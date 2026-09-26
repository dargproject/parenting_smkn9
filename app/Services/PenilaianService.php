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
            ->get(['siswa_id', 'tujuan_pembelajaran_id', 'nilai'])
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
