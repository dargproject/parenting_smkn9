<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKunjunganRumahRequest;
use App\Models\KasusBk;
use App\Models\Kelas;
use App\Models\KunjunganRumah;
use App\Models\PanggilanOrtu;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\Bk\LampiranBkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KunjunganRumahController extends Controller
{
    public function index(Request $request)
    {
        $records = KunjunganRumah::with(['kasusBk.siswa.kelas'])
            ->whereHas('kasusBk', fn ($q) => $q->where('konselor_id', Auth::id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->whereHas('kasusBk', fn ($qk) => $qk->where('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('kasusBk.siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('kasusBk.siswa.kelas', fn ($qs) => $qs->where('jurusan', $request->jurusan)))
            ->latest('tanggal_kunjungan')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.kunjungan-rumah.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreKunjunganRumahRequest $request, LampiranBkService $lampiranService)
    {
        $data = $request->validated();
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        $record = DB::transaction(function () use ($data, $tahunAjaranId, $lampiranService, $request) {
            $kasusBk = KasusBk::create([
                'siswa_id' => $data['siswa_id'],
                'tahun_ajaran_id' => $tahunAjaranId,
                'judul' => $data['judul'],
                'kategori' => 'Kunjungan Rumah',
                'deskripsi' => $data['deskripsi'],
                'status' => 'antrean',
                'prioritas' => 'sedang',
                'tanggal_mulai' => $data['tanggal_kunjungan'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'konselor_id' => Auth::id(),
            ]);

            $record = KunjunganRumah::create([
                'kasus_bk_id' => $kasusBk->id,
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status' => $data['status'],
                'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
            ]);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'kunjungan-rumah');
            }

            return $record;
        });

        return redirect()->route('guru.bk.kunjungan-rumah.show', $record)->with('success', 'Kunjungan rumah berhasil ditambahkan.');
    }

    public function show(KunjunganRumah $kunjunganRumah)
    {
        abort_unless($kunjunganRumah->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $kunjunganRumah->load(['kasusBk.siswa.kelas', 'kasusBk.siswa.profilSiswa', 'kasusBk.siswa.dataKeluarga', 'kasusBk.lampiran', 'kasusBk.konselor']);

        return view('guru.bk.kunjungan-rumah.show', [
            'kunjunganRumah' => $kunjunganRumah,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'panggilanOrtus' => PanggilanOrtu::where('siswa_id', $kunjunganRumah->kasusBk->siswa_id)->orderByDesc('tanggal')->get(),
        ]);
    }

    public function update(StoreKunjunganRumahRequest $request, KunjunganRumah $kunjunganRumah, LampiranBkService $lampiranService)
    {
        abort_unless($kunjunganRumah->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $data = $request->validated();

        DB::transaction(function () use ($data, $kunjunganRumah, $lampiranService, $request) {
            $kunjunganRumah->kasusBk->update([
                'siswa_id' => $data['siswa_id'],
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'tanggal_mulai' => $data['tanggal_kunjungan'],
            ]);

            $kunjunganRumah->update([
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status' => $data['status'],
                'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
            ]);

            $idBolehDihapus = $kunjunganRumah->kasusBk->lampiran->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kunjunganRumah->kasus_bk_id, $request->file('lampiran'), 'kunjungan-rumah');
            }
        });

        return redirect()->route('guru.bk.kunjungan-rumah.show', $kunjunganRumah)->with('success', 'Kunjungan rumah berhasil diperbarui.');
    }

    public function destroy(KunjunganRumah $kunjunganRumah)
    {
        abort_unless($kunjunganRumah->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke layanan ini.');

        $kunjunganRumah->delete();

        return redirect()->route('guru.bk.kunjungan-rumah.index')->with('success', 'Kunjungan rumah berhasil dihapus.');
    }
}
