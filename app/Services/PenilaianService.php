<?php

namespace App\Services;

use App\Models\KelasMataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiSas;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Support\Collection;

class PenilaianService
{
    public function kktpThreshold(): int
    {
        return (int) setting('kktp_threshold', 75);
    }

    public function kktpMargin(): int
    {
        return (int) setting('kktp_margin', 10);
    }

    /**
     * Deskripsi capaian kompetensi otomatis dari nilai per Tujuan Pembelajaran.
     * $items: daftar ['nama' => deskripsi TP, 'nilai' => angka]. Mengembalikan null bila belum ada nilai.
     */
    public function deskripsiCapaian(iterable $items): ?string
    {
        $items = collect($items)->filter(fn ($i) => isset($i['nilai']) && $i['nilai'] !== null && trim((string) ($i['nama'] ?? '')) !== '');
        if ($items->isEmpty()) {
            return null;
        }

        $kktp = $this->kktpThreshold();
        $batasCukup = $kktp - $this->kktpMargin();
        $urut = $items->sortByDesc('nilai')->values();

        $menguasai = $urut->filter(fn ($i) => $i['nilai'] >= $kktp)->values();
        $cukup = $urut->filter(fn ($i) => $i['nilai'] < $kktp && $i['nilai'] >= $batasCukup)->values();
        $perlu = $urut->filter(fn ($i) => $i['nilai'] < $batasCukup)->sortBy('nilai')->values();

        $daftar = function ($kelompok) {
            $nama = $kelompok->take(3)->map(fn ($i) => $i['nama'])->all();
            $sisa = $kelompok->count() - count($nama);

            return implode(', ', $nama).($sisa > 0 ? " dan {$sisa} lainnya" : '');
        };

        if ($menguasai->count() === $items->count()) {
            return $items->count() === 1
                ? "Menguasai capaian {$menguasai[0]['nama']} dengan baik."
                : "Menguasai seluruh capaian dengan baik, terutama {$menguasai[0]['nama']}.";
        }

        if ($perlu->count() === $items->count()) {
            return 'Perlu bimbingan intensif pada seluruh capaian: '.$daftar($perlu).'.';
        }

        $bagian = [];
        if ($menguasai->isNotEmpty()) {
            $bagian[] = 'menguasai '.$daftar($menguasai);
        }
        if ($cukup->isNotEmpty()) {
            $bagian[] = 'cukup menguasai '.$daftar($cukup);
        }
        if ($perlu->isNotEmpty()) {
            $bagian[] = 'perlu bimbingan pada '.$daftar($perlu);
        }

        return ucfirst(implode(', ', $bagian)).'.';
    }

    public function rataLm(Collection $nilaiLms): ?float
    {
        return $nilaiLms->isEmpty() ? null : round($nilaiLms->avg('nilai_efektif'), 1);
    }

    /**
     * Nilai akhir (Sumatif) diambil dari tabel nilai_sas, yang otomatis disinkronkan
     * dari rata-rata nilai formatif (LM) setiap kali guru mapel menyimpan nilai LM --
     * tidak ada input SAS manual terpisah. Fallback ke rata-rata LM langsung bila baris
     * nilai_sas belum pernah tersinkron (mis. belum pernah menyimpan nilai LM sama sekali).
     */
    public function nilaiAkhir(?float $rataLm, ?float $sas): ?float
    {
        if ($sas !== null) {
            return (float) $sas;
        }

        if ($rataLm !== null) {
            return round($rataLm, 1);
        }

        return null;
    }

    public function statusNilai(?float $nilai): string
    {
        if ($nilai === null) {
            return 'belum ada nilai';
        }

        return $nilai >= $this->kktpThreshold() ? 'tuntas' : 'remedial';
    }

    /**
     * Menentukan status ketuntasan suatu mata pelajaran bagi siswa.
     * Jika ada satu saja nilai (LM maupun SAS) yang belum tuntas (< KKTP),
     * maka status dianggap 'remedial' (Belum Tuntas).
     */
    public function statusMapel(Collection $nilaiLms, ?float $sas = null): string
    {
        $hasLm = $nilaiLms->isNotEmpty();
        $hasSas = $sas !== null;

        if (! $hasLm && ! $hasSas) {
            return 'belum ada nilai';
        }

        $kktp = $this->kktpThreshold();
        $hasRemedialLm = $nilaiLms->contains(function ($item) use ($kktp) {
            $val = is_object($item) ? ($item->nilai_efektif ?? $item->nilai ?? null) : ($item['nilai'] ?? $item);

            return $val !== null && (float) $val < $kktp;
        });
        $hasRemedialSas = $hasSas && (float) $sas < $kktp;

        if ($hasRemedialLm || $hasRemedialSas) {
            return 'remedial';
        }

        return 'tuntas';
    }

    /**
     * Sumber tunggal rekap kehadiran HARIAN dari data presensi mentah (per mapel).
     * Satu hari diberi SATU status: Hadir/Sakit/Izin/Alpa Penuh bila semua jadwal hari itu
     * berstatus sama, atau "sebagian" (campuran) bila hasilnya beda-beda dalam satu hari.
     */
    public function rekapHarian(Collection $presensiSiswa): array
    {
        $perHari = $presensiSiswa->groupBy('tanggal');
        $jumlah = ['penuh' => 0, 'sakit' => 0, 'izin' => 0, 'sebagian' => 0, 'alpa' => 0];

        foreach ($perHari as $records) {
            $status = $records->pluck('status')->unique();
            if ($status->count() > 1) {
                $jumlah['sebagian']++;

                continue;
            }

            match ($status->first()) {
                'H' => $jumlah['penuh']++,
                'S' => $jumlah['sakit']++,
                'I' => $jumlah['izin']++,
                'A' => $jumlah['alpa']++,
            };
        }

        return $jumlah + ['total_hari' => $perHari->count()];
    }

    public function tpRemedial(Collection $nilaiLms): Collection
    {
        return $nilaiLms->filter(fn ($nl) => $nl->nilai_efektif < $this->kktpThreshold())->values();
    }

    public function siswaLengkap(Siswa $siswa, int $tahunAjaranId): bool
    {
        $mapelIds = KelasMataPelajaran::where('kelas_id', $siswa->kelas_id)->pluck('mata_pelajaran_id');

        if ($mapelIds->isEmpty()) {
            return false;
        }

        $mapelDenganSas = NilaiSas::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai')
            ->whereIn('mata_pelajaran_id', $mapelIds)
            ->pluck('mata_pelajaran_id');

        return $mapelIds->diff($mapelDenganSas)->isEmpty();
    }

    /**
     * Sumber tunggal nilai akhir per siswa per mapel (dari nilai_lms + nilai_sas).
     * Mengembalikan koleksi objek {siswa_id, mata_pelajaran_id, nilai_akhir}.
     */
    public function nilaiAkhirRows(?int $tahunAjaranId = null): Collection
    {
        $tpMapel = TujuanPembelajaran::pluck('mata_pelajaran_id', 'id');

        $rataLm = NilaiLm::query()
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->get(['siswa_id', 'tujuan_pembelajaran_id', 'nilai', 'nilai_remedial'])
            ->groupBy(fn ($n) => $n->siswa_id.'-'.($tpMapel[$n->tujuan_pembelajaran_id] ?? 0))
            ->map(fn ($g) => $this->rataLm($g));

        $sas = NilaiSas::query()
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->whereNotNull('nilai')
            ->get(['siswa_id', 'mata_pelajaran_id', 'nilai'])
            ->keyBy(fn ($n) => $n->siswa_id.'-'.$n->mata_pelajaran_id);

        return $rataLm->keys()->merge($sas->keys())->unique()->map(function ($key) use ($rataLm, $sas) {
            [$siswaId, $mapelId] = array_map('intval', explode('-', $key));
            $na = $this->nilaiAkhir($rataLm[$key] ?? null, isset($sas[$key]) ? (float) $sas[$key]->nilai : null)
                ?? (isset($sas[$key]) ? (float) $sas[$key]->nilai : null);

            return (object) ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $mapelId, 'nilai_akhir' => $na];
        })->filter(fn ($r) => $r->nilai_akhir !== null && $r->mata_pelajaran_id > 0)->values();
    }
}
