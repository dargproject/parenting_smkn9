<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $aturan = [
        'kelas' => ['nama_kelas'],
        'mata_pelajarans' => ['nama_mapel'],
        'master_pelanggarans' => ['pasal_id', 'nama_pelanggaran'],
        'kategori_pengumumans' => ['nama'],
        'tujuan_pembelajarans' => ['mata_pelajaran_id', 'tahun_ajaran_id', 'deskripsi'],
        'presensis' => ['siswa_id', 'jadwal_pelajaran_id', 'tanggal'],
        'guru_role' => ['guru_id', 'role_id'],
        'orang_tuas' => ['siswa_id'],
    ];

    public function up(): void
    {
        // Data ganda tidak dihapus otomatis: hentikan dan minta dirapikan lebih dulu agar tidak ada data hilang diam-diam.
        foreach ($this->aturan as $tabel => $kolom) {
            $ganda = DB::table($tabel)->select($kolom)->groupBy($kolom)->havingRaw('COUNT(*) > 1')->count();
            if ($ganda > 0) {
                throw new RuntimeException("Tabel {$tabel} punya {$ganda} kelompok data ganda pada kolom (".implode(', ', $kolom).'). Rapikan datanya terlebih dahulu lalu jalankan migrate lagi.');
            }
        }

        foreach ($this->aturan as $tabel => $kolom) {
            Schema::table($tabel, fn (Blueprint $t) => $t->unique($kolom, $tabel.'_'.implode('_', $kolom).'_unique'));
        }
    }

    public function down(): void
    {
        foreach ($this->aturan as $tabel => $kolom) {
            Schema::table($tabel, fn (Blueprint $t) => $t->dropUnique($tabel.'_'.implode('_', $kolom).'_unique'));
        }
    }
};