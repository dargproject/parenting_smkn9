<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\GayaBelajar;
use App\Models\KonferensiKasus;
use App\Models\KunjunganRumah;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\PanggilanOrtu;
use App\Models\Peminatan;
use App\Models\PesanWaliKelas;
use App\Models\PrestasiNonAkademik;
use App\Models\RaporFinal;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(PenilaianService $penilaian)
    {
        $orangTua = Auth::guard('orangtua')->user();
        $siswa = $orangTua->siswa;
        [$riwayat, $tahunAjaranAktif] = $this->pilihTahunAjaran($siswa);
        $tahunAjaranAktifSistem = TahunAjaran::where('is_active', true)->first();

        $pesanWaliKelas = PesanWaliKelas::where('siswa_id', $siswa->id)->with('guru')->latest()->get();
        PesanWaliKelas::where('siswa_id', $siswa->id)->whereNull('dibaca_at')->update(['dibaca_at' => now()]);

        $infoBk = $this->infoBk($siswa);
        $prestasiNonAkademik = PrestasiNonAkademik::where('siswa_id', $siswa->id)->latest('tanggal')->get();

        $raporFinal = $tahunAjaranAktif
            ? RaporFinal::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first()
            : null;

        if (! $raporFinal || $raporFinal->status !== 'final') {
            return view('ortu.dashboard', ['siswa' => $siswa, 'dirilis' => false, 'ringkasan' => collect(), 'riwayat' => $riwayat, 'tahunAjaranTerpilih' => $tahunAjaranAktif, 'tahunAjaranAktifSistem' => $tahunAjaranAktifSistem, 'pesanWaliKelas' => $pesanWaliKelas, 'prestasiNonAkademik' => $prestasiNonAkademik] + $infoBk);
        }

        // Mapel diambil dari nilai yang tersimpan pada semester tsb, bukan kelas siswa saat ini (siswa bisa sudah naik kelas).
        $mapelIds = NilaiSas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->pluck('mata_pelajaran_id')
            ->merge(NilaiLm::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->with('tujuanPembelajaran')->get()->pluck('tujuanPembelajaran.mata_pelajaran_id'))
            ->filter()->unique();
        $mapelList = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();

        $nilaiLmSiswa = NilaiLm::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->with('tujuanPembelajaran')->get();
        $nilaiSasSiswa = NilaiSas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->get();

        $ringkasan = $mapelList->map(function ($mapel) use ($nilaiLmSiswa, $nilaiSasSiswa, $penilaian) {
            $lmMapel = $nilaiLmSiswa->filter(fn ($n) => $n->tujuanPembelajaran && $n->tujuanPembelajaran->mata_pelajaran_id === $mapel->id);
            $sas = $nilaiSasSiswa->firstWhere('mata_pelajaran_id', $mapel->id);
            $rataLm = $penilaian->rataLm($lmMapel);
            $na = $penilaian->nilaiAkhir($rataLm, $sas?->nilai);

            return [
                'mapel' => $mapel,
                'na' => $na,
                'status' => $penilaian->statusMapel($lmMapel, $sas?->nilai !== null ? (float) $sas->nilai : null),
            ];
        });

        return view('ortu.dashboard', [
            'siswa' => $siswa,
            'dirilis' => true,
            'ringkasan' => $ringkasan,
            'riwayat' => $riwayat,
            'tahunAjaranTerpilih' => $tahunAjaranAktif,
            'tahunAjaranAktifSistem' => $tahunAjaranAktifSistem,
            'perluPerhatian' => $ringkasan->filter(fn ($r) => $r['status'] === 'remedial'),
            'pesanWaliKelas' => $pesanWaliKelas,
            'prestasiNonAkademik' => $prestasiNonAkademik,
        ] + $infoBk);
    }

    /**
     * Data BK yang boleh dilihat orang tua: hanya yang ditandai tampilkan_ke_ortu oleh guru BK,
     * plus panggilan ortu (selalu tampil, sudah pasti ditujukan untuk ortu) dan nama konselor BK
     * kelas siswa (dari alokasi guru_bk_kelas, bukan dari kasus_bk mana pun -- kasus BK tetap rahasia).
     *
     * Kunjungan Rumah & Konferensi otomatis hilang dari dashboard ortu begitu kasus_bk induknya
     * berstatus 'selesai' (datanya tetap utuh di sisi guru BK). Panggilan ortu hilang begitu
     * statusnya 'Hadir / Mediasi Selesai'. GayaBelajar/Peminatan tidak punya kasus_bk, tidak terdampak.
     */
    private function infoBk($siswa): array
    {
        return [
            'konselorBk' => $siswa->kelas?->guruBks ?? collect(),
            'panggilanOrtus' => PanggilanOrtu::where('siswa_id', $siswa->id)->where('status', '!=', 'Hadir / Mediasi Selesai')->orderByDesc('tanggal')->get(),
            'bkKunjunganRumah' => KunjunganRumah::whereHas('kasusBk', fn ($q) => $q->where('siswa_id', $siswa->id)->where('status', '!=', 'selesai'))->where('tampilkan_ke_ortu', true)->latest('tanggal_kunjungan')->get(),
            'bkKonferensi' => KonferensiKasus::whereHas('kasusBk', fn ($q) => $q->where('siswa_id', $siswa->id)->where('status', '!=', 'selesai'))->where('tampilkan_ke_ortu', true)->latest('tanggal_konferensi')->get(),
            'bkGayaBelajar' => GayaBelajar::where('siswa_id', $siswa->id)->where('tampilkan_ke_ortu', true)->latest('tanggal')->first(),
            'bkPeminatan' => Peminatan::where('siswa_id', $siswa->id)->where('tampilkan_ke_ortu', true)->latest('tanggal')->first(),
        ];
    }

    public function show(MataPelajaran $mataPelajaran, PenilaianService $penilaian)
    {
        $orangTua = Auth::guard('orangtua')->user();
        $siswa = $orangTua->siswa;
        [, $tahunAjaranAktif] = $this->pilihTahunAjaran($siswa);

        $raporFinal = $tahunAjaranAktif
            ? RaporFinal::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first()
            : null;

        abort_unless($raporFinal && $raporFinal->status === 'final', 403, 'Rapor semester ini belum dirilis oleh wali kelas.');

        $nilaiLm = NilaiLm::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->whereHas('tujuanPembelajaran', fn ($q) => $q->where('mata_pelajaran_id', $mataPelajaran->id))
            ->with('tujuanPembelajaran')
            ->get()
            ->sortBy(fn ($n) => $n->tujuanPembelajaran->urutan);

        $sas = NilaiSas::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();
        $pklUkk = NilaiPklUkk::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->get()->keyBy('jenis');
        $catatanKompetensi = CatatanKompetensi::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();
        $catatanWaliKelas = CatatanWaliKelas::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();

        $rataLm = $penilaian->rataLm($nilaiLm);
        $na = $penilaian->nilaiAkhir($rataLm, $sas?->nilai);
        $capaian = $penilaian->deskripsiCapaian($nilaiLm->map(fn ($n) => ['nama' => $n->tujuanPembelajaran->deskripsi ?? '', 'nilai' => $n->nilai_efektif]));

        return view('ortu.mapel-detail', [
            'capaian' => $capaian,
            'siswa' => $siswa,
            'mapel' => $mataPelajaran,
            'nilaiLm' => $nilaiLm,
            'sas' => $sas,
            'pklUkk' => $pklUkk,
            'na' => $na,
            'status' => $penilaian->statusMapel($nilaiLm, $sas?->nilai !== null ? (float) $sas->nilai : null),
            'tpRemedial' => $penilaian->tpRemedial($nilaiLm),
            'catatanKompetensi' => $catatanKompetensi,
            'catatanWaliKelas' => $catatanWaliKelas,
            'tahunAjaranTerpilih' => $tahunAjaranAktif,
        ]);
    }

    /**
     * Mengembalikan [daftar semester yang rapornya sudah dirilis, semester yang dipilih (default: aktif)].
     *
     * Permintaan eksplisit via ?ta= dicari dulu di riwayat (rapor dirilis), lalu di SEMUA tahun ajaran
     * (mis. semester berjalan yang belum ada rapornya) -- supaya tidak diam-diam dialihkan ke semester
     * aktif begitu saja ketika semester yang diminta memang ada tapi belum punya rapor final.
     */
    private function pilihTahunAjaran($siswa): array
    {
        $riwayat = TahunAjaran::whereIn('id', RaporFinal::where('siswa_id', $siswa->id)->where('status', 'final')->pluck('tahun_ajaran_id'))
            ->orderByDesc('id')->get();

        $taDiminta = request('ta');
        $dipilih = $taDiminta ? ($riwayat->firstWhere('id', (int) $taDiminta) ?? TahunAjaran::find($taDiminta)) : null;
        $dipilih ??= TahunAjaran::where('is_active', true)->first();

        return [$riwayat, $dipilih];
    }
}
