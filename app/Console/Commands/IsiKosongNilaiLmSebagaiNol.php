<?php

namespace App\Console\Commands;

use App\Models\KelasMataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiSas;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill satu kali untuk data lama: N yang sudah mulai diisi (minimal satu siswa) tapi masih
 * kosong untuk siswa aktif lain di kelas yang sama diisi 0. N yang kosong untuk semua siswa
 * (belum pernah digarap sama sekali) tetap dibiarkan kosong -- aturan yang sama dengan yang
 * sekarang berlaku otomatis di PenilaianController::simpanNilaiLm() untuk input baru.
 */
class IsiKosongNilaiLmSebagaiNol extends Command
{
    protected $signature = 'nilai:isi-kosong-nol {--dry-run : Hanya tampilkan ringkasan tanpa menyimpan}';

    protected $description = 'Isi 0 untuk siswa yang nilai N-nya masih kosong, khusus N yang sudah ada minimal satu siswa dinilai';

    public function handle(PenilaianService $penilaian): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $rencana = [];
        foreach (TujuanPembelajaran::all() as $tp) {
            if (! NilaiLm::where('tujuan_pembelajaran_id', $tp->id)->exists()) {
                continue;
            }

            $siswaAktifIds = Siswa::where('kelas_id', $tp->kelas_id)->where('status_aktif', true)->pluck('id');
            $siswaSudahAda = NilaiLm::where('tujuan_pembelajaran_id', $tp->id)->pluck('siswa_id');
            $siswaKosong = $siswaAktifIds->diff($siswaSudahAda)->values();

            if ($siswaKosong->isEmpty()) {
                continue;
            }

            $rencana[] = ['tp' => $tp, 'siswa_kosong' => $siswaKosong];
        }

        if (empty($rencana)) {
            $this->info('Tidak ada yang perlu diisi -- semua N yang sudah mulai diisi sudah lengkap untuk siswa aktif.');

            return self::SUCCESS;
        }

        $totalBaris = collect($rencana)->sum(fn ($r) => $r['siswa_kosong']->count());
        $this->info(count($rencana)." kolom N akan diisi 0 untuk total {$totalBaris} data siswa yang kosong:");

        foreach ($rencana as $r) {
            $tp = $r['tp'];
            $this->line("- {$tp->mataPelajaran->nama_mapel} / {$tp->kelas->nama_kelas} / ".($tp->kode ?: 'N'.$tp->urutan).": {$r['siswa_kosong']->count()} siswa");
        }

        if ($dryRun) {
            $this->comment('Mode --dry-run: tidak ada perubahan yang disimpan.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Lanjutkan menyimpan nilai 0 ini?', true)) {
            $this->comment('Dibatalkan, tidak ada perubahan.');

            return self::SUCCESS;
        }

        $siswaTerpengaruh = [];

        DB::transaction(function () use ($rencana, &$siswaTerpengaruh) {
            foreach ($rencana as $r) {
                $tp = $r['tp'];
                $guruId = KelasMataPelajaran::where('kelas_id', $tp->kelas_id)
                    ->where('mata_pelajaran_id', $tp->mata_pelajaran_id)
                    ->value('guru_id');

                foreach ($r['siswa_kosong'] as $siswaId) {
                    NilaiLm::create([
                        'siswa_id' => $siswaId,
                        'tujuan_pembelajaran_id' => $tp->id,
                        'tahun_ajaran_id' => $tp->tahun_ajaran_id,
                        'nilai' => 0,
                        'guru_id' => $guruId,
                    ]);

                    $kunci = $tp->mata_pelajaran_id.'-'.$tp->kelas_id.'-'.$tp->tahun_ajaran_id;
                    $siswaTerpengaruh[$kunci][$siswaId] = $siswaId;
                }
            }
        });

        $naDiperbarui = 0;
        foreach ($siswaTerpengaruh as $kunci => $siswaIds) {
            [$mapelId, $kelasId, $tahunAjaranId] = explode('-', $kunci);
            $tpIds = TujuanPembelajaran::where('mata_pelajaran_id', $mapelId)->where('kelas_id', $kelasId)->where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');

            foreach ($siswaIds as $siswaId) {
                $nilaiLmSiswa = NilaiLm::where('siswa_id', $siswaId)->whereIn('tujuan_pembelajaran_id', $tpIds)->get();
                $rata = $penilaian->rataLm($nilaiLmSiswa);
                if ($rata === null) {
                    continue;
                }

                NilaiSas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $mapelId, 'tahun_ajaran_id' => $tahunAjaranId],
                    ['nilai' => (int) round($rata)]
                );
                $naDiperbarui++;
            }
        }

        $this->info("Selesai: {$totalBaris} nilai 0 ditambahkan, {$naDiperbarui} Nilai Akhir Sumatif diperbarui otomatis.");

        return self::SUCCESS;
    }
}
