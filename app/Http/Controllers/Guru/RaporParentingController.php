<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CatatanWaliKelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\NilaiEkstrakurikuler;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\Pelanggaran;
use App\Models\Peminatan;
use App\Models\PenguranganPoin;
use App\Models\Presensi;
use App\Models\PrestasiNonAkademik;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use App\Services\SanksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class RaporParentingController extends Controller
{
    /**
     * Rapor Parenting bisa dicetak oleh wali_kelas (data kedisiplinan & karakter) maupun
     * guru_wali (data akademik) untuk siswa binaan masing-masing -- jadi otorisasinya cek
     * kedua kemungkinan, bukan cuma satu kolom.
     */
    public function cetak(Siswa $siswa, PenilaianService $penilaian, SanksiService $sanksi)
    {
        $pdf = Pdf::loadView('pdf.rapor-parenting', $this->data($siswa, $penilaian, $sanksi, forPdf: true))->setPaper('a4');

        return $pdf->stream('Rapor-Parenting-'.str_replace(' ', '-', $siswa->nama).'.pdf');
    }

    /**
     * Preview HTML dari isi rapor sebelum dicetak ke PDF -- memakai blade yang sama persis
     * supaya guru bisa cek data dengan cepat tanpa harus generate PDF setiap kali.
     */
    public function preview(Siswa $siswa, PenilaianService $penilaian, SanksiService $sanksi)
    {
        return view('pdf.rapor-parenting', $this->data($siswa, $penilaian, $sanksi, forPdf: false));
    }

    private function data(Siswa $siswa, PenilaianService $penilaian, SanksiService $sanksi, bool $forPdf): array
    {
        $guru = Auth::user();
        $kelas = $siswa->kelas;

        abort_unless(
            $kelas && ($kelas->wali_kelas_id === $guru->id || $kelas->guru_wali_id === $guru->id),
            403,
            'Anda bukan wali kelas atau guru wali dari siswa ini.'
        );

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $tahunAjaranAktifId = $tahunAjaranAktif?->id;

        // Akademik: bukan nilai angka, tapi daftar mapel yang nilainya kurang (status remedial/
        // "Belum Tuntas" -- label & warna merah yang sama dipakai di halaman Dashboard Orang Tua),
        // supaya rapor fokus menyorot mapel yang perlu perhatian orang tua, bukan daftar lengkap
        // semua mapel. Daftar mapel sumbernya gabungan dari struktur kurikulum kelas
        // (KelasMataPelajaran) DAN mapel yang sudah ada nilainya (NilaiLm/NilaiSas) -- tidak cukup
        // pakai salah satu saja, karena di data produksi tidak semua kelas punya KelasMataPelajaran
        // lengkap. Tidak difilter berdasarkan kategori -- "Kejuruan" bukan berarti mapel itu cuma
        // dinilai lewat PKL/UKK, banyak mapel kategori Kejuruan tetap punya nilai LM/SAS normal.
        $akademikPerMapel = collect();
        if ($tahunAjaranAktifId) {
            $nilaiLmSiswa = NilaiLm::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->with('tujuanPembelajaran')->get();
            $nilaiSasSiswa = NilaiSas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->get();

            $mapelIdsKurikulum = KelasMataPelajaran::where('kelas_id', $kelas->id)->pluck('mata_pelajaran_id');
            $mapelIdsDinilai = $nilaiLmSiswa->pluck('tujuanPembelajaran.mata_pelajaran_id')->filter()
                ->merge($nilaiSasSiswa->pluck('mata_pelajaran_id'));
            $mapelIds = $mapelIdsKurikulum->merge($mapelIdsDinilai)->unique();

            $mapelList = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();

            $akademikPerMapel = $mapelList->map(function ($mapel) use ($nilaiLmSiswa, $nilaiSasSiswa, $penilaian) {
                $lmMapel = $nilaiLmSiswa->filter(fn ($n) => $n->tujuanPembelajaran && $n->tujuanPembelajaran->mata_pelajaran_id === $mapel->id);
                $sas = $nilaiSasSiswa->firstWhere('mata_pelajaran_id', $mapel->id);

                // Materi (deskripsi Tujuan Pembelajaran) yang nilainya di bawah KKTP -- supaya
                // rapor menunjukkan materi spesifik yang perlu ditingkatkan, bukan cuma status.
                $materiKurang = $penilaian->tpRemedial($lmMapel)
                    ->pluck('tujuanPembelajaran.deskripsi')->filter()->unique()->values()->all();

                return [
                    'nama' => $mapel->nama_mapel,
                    'status' => $penilaian->statusMapel($lmMapel, $sas?->nilai !== null ? (float) $sas->nilai : null),
                    'materi' => $materiKurang,
                ];
            })->filter(fn ($a) => $a['status'] === 'remedial')->values();
        }

        $pklUkk = $tahunAjaranAktifId
            ? NilaiPklUkk::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->whereNotNull('nilai')->get()
            : collect();
        $rataPraktik = $pklUkk->avg('nilai');
        $statusPraktik = $rataPraktik !== null ? $penilaian->statusNilai($rataPraktik) : null;

        // Kedisiplinan & kehadiran -- rincian H/S/I/A, bukan cuma persentase hadir, supaya orang
        // tua tahu persis berapa kali anaknya tidak masuk tanpa keterangan (Alpa).
        $presensi = $tahunAjaranAktifId
            ? Presensi::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->with('jadwalPelajaran.mataPelajaran')->get()
            : collect();
        $totalPresensi = $presensi->count();
        $rekapKehadiran = [
            'H' => $presensi->where('status', 'H')->count(),
            'S' => $presensi->where('status', 'S')->count(),
            'I' => $presensi->where('status', 'I')->count(),
            'A' => $presensi->where('status', 'A')->count(),
        ];
        $persenHadir = $totalPresensi > 0 ? round($rekapKehadiran['H'] / $totalPresensi * 100, 1) : null;

        // Rincian per mapel -- hanya mapel yang punya ketidakhadiran (S/I/A), supaya rapor
        // menyorot mapel yang sering ditinggalkan, bukan daftar lengkap semua mapel.
        $ketidakhadiranPerMapel = $presensi->filter(fn ($p) => $p->status !== 'H' && $p->jadwalPelajaran)
            ->groupBy(fn ($p) => $p->jadwalPelajaran->mataPelajaran->nama_mapel ?? 'Tanpa Mapel')
            ->map(fn ($items, $nama) => [
                'nama' => $nama,
                'sakit' => $items->where('status', 'S')->count(),
                'izin' => $items->where('status', 'I')->count(),
                'alpa' => $items->where('status', 'A')->count(),
            ])
            ->sortByDesc('alpa')
            ->values();

        $pelanggaranList = $tahunAjaranAktifId
            ? Pelanggaran::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->orderBy('tanggal')->get()
            : collect();
        $poinPelanggaran = (int) $pelanggaranList->sum('poin');
        $poinDikurangi = $tahunAjaranAktifId ? (int) PenguranganPoin::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->sum('poin') : 0;
        $poinBersih = max(0, $poinPelanggaran - $poinDikurangi);
        $statusSanksi = $sanksi->statusSemester($poinBersih);

        // Non-akademik: nilai ekskul (format huruf asli A/B/C, tidak perlu konversi).
        $nilaiEkskul = $tahunAjaranAktifId
            ? NilaiEkstrakurikuler::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->with('ekstrakurikuler')->get()
            : collect();

        // Catatan karakter wali kelas -- satu kolom teks bebas, dipakai untuk Soft Skills/
        // Komunikasi/Kreativitas & Inisiatif sekaligus (digabung, bukan diulang 3x).
        $catatanKarakter = $tahunAjaranAktifId
            ? CatatanWaliKelas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktifId)->value('catatan_karakter')
            : null;

        // Bakat & minat dari Tes Peminatan BK -- cuma yang ditandai tampilkan_ke_ortu oleh guru BK,
        // persis aturan privasi yang sama dipakai di Dashboard Orang Tua (tidak terikat tahun ajaran
        // tertentu karena tesnya biasanya cuma sekali, bukan per semester).
        $peminatan = Peminatan::where('siswa_id', $siswa->id)->where('tampilkan_ke_ortu', true)->latest('tanggal')->first();

        // Prestasi non-akademik -- seluruh riwayat, sama seperti Dashboard Orang Tua (tidak
        // dibatasi tahun ajaran karena prestasi tidak selalu dicatat per semester).
        $prestasiNonAkademik = PrestasiNonAkademik::where('siswa_id', $siswa->id)->latest('tanggal')->get();

        // Untuk PDF (DomPDF) pakai path filesystem langsung; untuk preview di browser pakai URL
        // publik -- path filesystem Windows tidak bisa dipakai sebagai src gambar di browser.
        $logoRelatif = setting('logo_sekolah');
        $logoPath = $logoRelatif
            ? ($forPdf ? storage_path('app/public/'.$logoRelatif) : asset('storage/'.$logoRelatif))
            : null;

        return [
            'siswa' => $siswa,
            'kelas' => $kelas,
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'namaWali' => $siswa->orangTua->nama ?? null,
            'logoPath' => $logoPath,
            'akademikPerMapel' => $akademikPerMapel,
            'rataPraktik' => $rataPraktik,
            'statusPraktik' => $statusPraktik,
            'totalPresensi' => $totalPresensi,
            'persenHadir' => $persenHadir,
            'rekapKehadiran' => $rekapKehadiran,
            'ketidakhadiranPerMapel' => $ketidakhadiranPerMapel,
            'poinBersih' => $poinBersih,
            'statusSanksi' => $statusSanksi,
            'pelanggaranList' => $pelanggaranList,
            'nilaiEkskul' => $nilaiEkskul,
            'catatanKarakter' => $catatanKarakter,
            'peminatan' => $peminatan,
            'prestasiNonAkademik' => $prestasiNonAkademik,
        ];
    }
}
