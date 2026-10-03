<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBimbinganKelompokRequest;
use App\Models\BimbinganKelompok;
use App\Models\BimbinganKelompokSiswa;
use App\Models\KasusBk;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\Bk\LampiranBkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BimbinganKelompokController extends Controller
{
    public function index(Request $request)
    {
        $records = BimbinganKelompok::with(['kasusBk', 'pesertas.siswa.kelas'])
            ->whereHas('kasusBk', fn ($q) => $q->where('konselor_id', Auth::id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->where(function ($qw) use ($keyword) {
                    $qw->whereHas('kasusBk', fn ($qk) => $qk->where('deskripsi', 'like', "%{$keyword}%"))
                        ->orWhereHas('pesertas.siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%"));
                });
            })
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('pesertas.siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('pesertas.siswa.kelas', fn ($qs) => $qs->where('jurusan', $request->jurusan)))
            ->latest('tanggal_layanan')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.kelompok.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreBimbinganKelompokRequest $request, LampiranBkService $lampiranService)
    {
        $data = $request->validated();
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');
        $siswaIds = array_values(array_unique($data['siswa_ids']));

        $record = DB::transaction(function () use ($data, $siswaIds, $tahunAjaranId, $lampiranService, $request) {
            $kasusBk = KasusBk::create([
                'siswa_id' => $siswaIds[0],
                'tahun_ajaran_id' => $tahunAjaranId,
                'judul' => $data['judul'],
                'kategori' => 'Konseling Kelompok',
                'deskripsi' => $data['deskripsi'],
                'status' => 'antrean',
                'prioritas' => 'sedang',
                'tanggal_mulai' => $data['tanggal_layanan'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'konselor_id' => Auth::id(),
            ]);

            $record = BimbinganKelompok::create([
                'kasus_bk_id' => $kasusBk->id,
                'tanggal_layanan' => $data['tanggal_layanan'],
            ]);

            $this->syncPesertas($record, $siswaIds);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'kelompok');
            }

            return $record;
        });

        return redirect()->route('guru.bk.kelompok.show', $record)->with('success', 'Layanan konseling kelompok berhasil ditambahkan.');
    }

    public function show(BimbinganKelompok $kelompok)
    {
        abort_unless($kelompok->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $kelompok->load(['kasusBk.lampiran', 'kasusBk.konselor', 'pesertas.siswa.kelas']);

        return view('guru.bk.kelompok.show', [
            'kelompok' => $kelompok,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
        ]);
    }

    public function update(StoreBimbinganKelompokRequest $request, BimbinganKelompok $kelompok, LampiranBkService $lampiranService)
    {
        abort_unless($kelompok->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $data = $request->validated();
        $siswaIds = array_values(array_unique($data['siswa_ids']));

        DB::transaction(function () use ($data, $siswaIds, $kelompok, $lampiranService, $request) {
            $kelompok->kasusBk->update([
                'siswa_id' => $siswaIds[0],
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'tanggal_mulai' => $data['tanggal_layanan'],
            ]);

            $kelompok->update(['tanggal_layanan' => $data['tanggal_layanan']]);

            $this->syncPesertas($kelompok, $siswaIds);

            $idBolehDihapus = $kelompok->kasusBk->lampiran->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kelompok->kasus_bk_id, $request->file('lampiran'), 'kelompok');
            }
        });

        return redirect()->route('guru.bk.kelompok.show', $kelompok)->with('success', 'Layanan konseling kelompok berhasil diperbarui.');
    }

    public function destroy(BimbinganKelompok $kelompok)
    {
        abort_unless($kelompok->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        // Kasus BK yang dibuat otomatis untuk layanan ini sengaja tidak ikut dihapus, konsisten dengan modul lain.
        $kelompok->delete();

        return redirect()->route('guru.bk.kelompok.index')->with('success', 'Layanan konseling kelompok berhasil dihapus.');
    }

    private function syncPesertas(BimbinganKelompok $kelompok, array $siswaIds): void
    {
        $kelompok->pesertas()->delete();

        BimbinganKelompokSiswa::insert(collect($siswaIds)->map(fn ($siswaId) => [
            'bimbingan_kelompok_id' => $kelompok->id,
            'siswa_id' => $siswaId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }
}
