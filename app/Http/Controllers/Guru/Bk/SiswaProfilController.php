<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDataKeluargaSiswaRequest;
use App\Http\Requests\StoreProfilSiswaRequest;
use App\Models\Akpd;
use App\Models\BimbinganIndividu;
use App\Models\BimbinganKelompok;
use App\Models\DataKeluargaSiswa;
use App\Models\Dcm;
use App\Models\GayaBelajar;
use App\Models\KasusBk;
use App\Models\KonferensiKasus;
use App\Models\KunjunganRumah;
use App\Models\Peminatan;
use App\Models\ProfilSiswa;
use App\Models\Siswa;
use App\Models\Sosiometri;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class SiswaProfilController extends Controller
{
    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas', 'profilSiswa', 'dataKeluarga']);

        // Kasus BK & layanan bertahap tetap rahasia per-konselor: hanya kasus milik guru BK yang login
        // yang ditampilkan di sini, berbeda dari instrumen asesmen di bawah yang datanya bersama.
        $kasusBks = KasusBk::where('siswa_id', $siswa->id)->where('konselor_id', Auth::id())->get();
        $kasusBkIds = $kasusBks->pluck('id');

        $layanan = collect()
            ->merge(BimbinganIndividu::whereIn('kasus_bk_id', $kasusBkIds)->get()->map(fn ($r) => ['jenis' => 'Konseling Individu', 'tanggal' => $r->tanggal_layanan, 'route' => route('guru.bk.individu.show', $r)]))
            ->merge(BimbinganKelompok::whereIn('kasus_bk_id', $kasusBkIds)->get()->map(fn ($r) => ['jenis' => 'Konseling Kelompok', 'tanggal' => $r->tanggal_layanan, 'route' => route('guru.bk.kelompok.show', $r)]))
            ->merge(KunjunganRumah::whereIn('kasus_bk_id', $kasusBkIds)->get()->map(fn ($r) => ['jenis' => 'Kunjungan Rumah', 'tanggal' => $r->tanggal_kunjungan, 'route' => route('guru.bk.kunjungan-rumah.show', $r)]))
            ->merge(KonferensiKasus::whereIn('kasus_bk_id', $kasusBkIds)->get()->map(fn ($r) => ['jenis' => 'Konferensi Kasus', 'tanggal' => $r->tanggal_konferensi, 'route' => route('guru.bk.konferensi.show', $r)]))
            ->sortByDesc('tanggal')
            ->values();

        $asesmen = collect()
            ->merge(Akpd::where('siswa_id', $siswa->id)->get()->map(fn ($r) => ['jenis' => 'AKPD', 'tanggal' => $r->tanggal, 'route' => route('guru.bk.asesmen.akpd.show', $r)]))
            ->merge(Dcm::where('siswa_id', $siswa->id)->get()->map(fn ($r) => ['jenis' => 'DCM', 'tanggal' => $r->tanggal, 'route' => route('guru.bk.asesmen.dcm.show', $r)]))
            ->merge(GayaBelajar::where('siswa_id', $siswa->id)->get()->map(fn ($r) => ['jenis' => 'Gaya Belajar', 'tanggal' => $r->tanggal, 'route' => route('guru.bk.asesmen.gaya-belajar.show', $r)]))
            ->merge(Sosiometri::where('siswa_id', $siswa->id)->get()->map(fn ($r) => ['jenis' => 'Sosiometri', 'tanggal' => $r->tanggal, 'route' => route('guru.bk.asesmen.sosiometri.show', $r)]))
            ->merge(Peminatan::where('siswa_id', $siswa->id)->get()->map(fn ($r) => ['jenis' => 'Tes Bakat Minat', 'tanggal' => $r->tanggal, 'route' => route('guru.bk.asesmen.peminatan.show', $r)]))
            ->sortByDesc('tanggal')
            ->values();

        return view('guru.bk.siswa.show', [
            'siswa' => $siswa,
            'kasusBks' => $kasusBks->sortByDesc('tanggal_mulai')->values(),
            'layanan' => $layanan,
            'asesmen' => $asesmen,
        ]);
    }

    public function updateProfil(StoreProfilSiswaRequest $request, Siswa $siswa)
    {
        ProfilSiswa::updateOrCreate(['siswa_id' => $siswa->id], $request->validated());

        return redirect()->route('guru.bk.siswa.show', $siswa)->with('success', 'Profil siswa berhasil disimpan.');
    }

    public function updateKeluarga(StoreDataKeluargaSiswaRequest $request, Siswa $siswa)
    {
        DataKeluargaSiswa::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $request->validated() + [
                'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
                'punya_kamar_sendiri' => $request->boolean('punya_kamar_sendiri'),
            ]
        );

        return redirect()->route('guru.bk.siswa.show', $siswa)->with('success', 'Data keluarga siswa berhasil disimpan.');
    }
}
