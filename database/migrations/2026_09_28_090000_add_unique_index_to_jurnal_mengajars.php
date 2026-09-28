<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $ganda = DB::table('jurnal_mengajars')
            ->select('jadwal_pelajaran_id', 'tanggal')
            ->groupBy('jadwal_pelajaran_id', 'tanggal')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($ganda > 0) {
            throw new RuntimeException("Tabel jurnal_mengajars punya {$ganda} kelompok data ganda pada (jadwal_pelajaran_id, tanggal). Rapikan datanya terlebih dahulu lalu jalankan migrate lagi.");
        }

        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->unique(['jadwal_pelajaran_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropUnique(['jadwal_pelajaran_id', 'tanggal']);
        });
    }
};
