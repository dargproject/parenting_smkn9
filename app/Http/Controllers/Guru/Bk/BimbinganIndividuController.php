<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBimbinganIndividuRequest;
use App\Models\BimbinganIndividu;
use App\Models\KasusBk;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\Bk\LampiranBkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BimbinganIndividuController extends Controller
{
    public function index(Request $request)
    {
        $records = BimbinganIndividu::with(['kasusBk.siswa.kelas'])
            ->whereHas('kasusBk', fn ($q) => $q->where('konselor_id', Auth::id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->whereHas('kasusBk', fn ($qk) => $qk->where('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%")));
            })
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('kasusBk.siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('kasusBk.siswa.kelas', fn ($qs) => $qs->where('jurusan', $request->jurusan)))
            ->when($request->filled('jenis_kelamin'), fn ($q) => $q->whereHas('kasusBk.siswa', fn ($qs) => $qs->where('jenis_kelamin', $request->jenis_kelamin)))
            ->latest('tanggal_layanan')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.individu.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreBimbinganIndividuRequest $request, LampiranBkService $lampiranService)
    {
        $data = $request->validated();
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        $record = DB::transaction(function () use ($data, $tahunAjaranId, $lampiranService, $request) {
            $kasusBk = KasusBk::create([
                'siswa_id' => $data['siswa_id'],
                'tahun_ajaran_id' => $tahunAjaranId,
                'judul' => $data['judul'],
                'kategori' => 'Konseling Individu',
                'deskripsi' => $data['deskripsi'],
                'status' => 'antrean',
                'prioritas' => 'sedang',
                'tanggal_mulai' => $data['tanggal_layanan'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'konselor_id' => Auth::id(),
            ]);

            $record = BimbinganIndividu::create([
                'kasus_bk_id' => $kasusBk->id,
                'tanggal_layanan' => $data['tanggal_layanan'],
            ]);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'individu');
            }

            return $record;
        });

        return redirect()->route('guru.bk.individu.show', $record)->with('success', 'Layanan konseling individu berhasil ditambahkan.');
    }

    public function show(BimbinganIndividu $individu)
    {
        abort_unless($individu->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $individu->load(['kasusBk.siswa.kelas', 'kasusBk.lampiran', 'kasusBk.konselor']);

        return view('guru.bk.individu.show', [
            'individu' => $individu,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
        ]);
    }

    public function update(StoreBimbinganIndividuRequest $request, BimbinganIndividu $individu, LampiranBkService $lampiranService)
    {
        abort_unless($individu->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $data = $request->validated();

        DB::transaction(function () use ($data, $individu, $lampiranService, $request) {
            $individu->kasusBk->update([
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'tanggal_mulai' => $data['tanggal_layanan'],
            ]);

            $individu->update(['tanggal_layanan' => $data['tanggal_layanan']]);

            $idBolehDihapus = $individu->kasusBk->lampiran->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($individu->kasus_bk_id, $request->file('lampiran'), 'individu');
            }
        });

        return redirect()->route('guru.bk.individu.show', $individu)->with('success', 'Layanan konseling individu berhasil diperbarui.');
    }

    public function destroy(BimbinganIndividu $individu)
    {
        abort_unless($individu->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        // Kasus BK yang dibuat otomatis untuk layanan ini sengaja tidak ikut dihapus (konsisten dengan
        // aplikasi BK sumber) -- konselor masih bisa menghapusnya sendiri lewat halaman Kasus BK bila perlu.
        $individu->delete();

        return redirect()->route('guru.bk.individu.index')->with('success', 'Layanan konseling individu berhasil dihapus.');
    }
}
