<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\NilaiSas;
use App\Models\Siswa;
use Illuminate\Support\Collection;

class PenilaianService
{
    public function bobotLm(): float
    {
        return ((float) setting('bobot_lm', 60)) / 100;
    }

    public function bobotSas(): float
    {
        return ((float) setting('bobot_sas', 40)) / 100;
    }

    public function kktpThreshold(): int
    {
        return (int) setting('kktp_threshold', 75);
    }

    public function rataLm(Collection $nilaiLms): ?float
    {
        return $nilaiLms->isEmpty() ? null : round($nilaiLms->avg('nilai'), 1);
    }

    /**
     * NA = bobot LM * rata LM + bobot SAS * SAS.
     * Jika SAS belum diisi, renormalisasi ke 100% LM agar siswa tidak dirugikan
     * oleh guru yang belum menetapkan kebijakan SAS untuk mapel tersebut.
     */
    public function nilaiAkhir(?float $rataLm, ?float $sas): ?float
    {
        if ($rataLm === null) {
            return null;
        }

        if ($sas === null) {
            return round($rataLm, 1);
        }

        return round($this->bobotLm() * $rataLm + $this->bobotSas() * $sas, 1);
    }

    public function statusNilai(?float $nilai): string
    {
        if ($nilai === null) {
            return 'belum ada nilai';
        }

        return $nilai >= $this->kktpThreshold() ? 'tuntas' : 'remedial';
    }

    public function tpRemedial(Collection $nilaiLms): Collection
    {
        return $nilaiLms->filter(fn ($nl) => $nl->nilai < $this->kktpThreshold())->values();
    }

    public function siswaLengkap(Siswa $siswa, int $tahunAjaranId): bool
    {
        // NB: jadwal_pelajarans.tahun_ajaran_id tidak selalu terisi di data lama, jadi
        // tidak difilter berdasarkan tahun ajaran seperti tabel nilai LM/SAS yang baru.
        $mapelIds = JadwalPelajaran::where('kelas_id', $siswa->kelas_id)
            ->distinct()
            ->pluck('mata_pelajaran_id');

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
}
