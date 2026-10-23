<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disepakati: Tujuan Pembelajaran (N) sebelumnya dibagi antar kelas untuk mapel yang
        // sama, sekarang dibuat independen per kelas. Data N & nilai formatif lama di-reset
        // (disetujui user) -- nilai_lms ikut terhapus lewat cascadeOnDelete pada FK-nya.
        DB::table('tujuan_pembelajarans')->delete();

        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->dropUnique('tujuan_pembelajarans_mata_pelajaran_id_tahun_ajaran_id_deskripsi_unique');
        });

        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->foreignId('kelas_id')->after('mata_pelajaran_id')->constrained('kelas')->cascadeOnDelete();
            $table->unique(['kelas_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'deskripsi'], 'tujuan_pembelajarans_kelas_mapel_ta_deskripsi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->dropUnique('tujuan_pembelajarans_kelas_mapel_ta_deskripsi_unique');
            $table->dropConstrainedForeignId('kelas_id');
        });

        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->unique(['mata_pelajaran_id', 'tahun_ajaran_id', 'deskripsi'], 'tujuan_pembelajarans_mata_pelajaran_id_tahun_ajaran_id_deskripsi_unique');
        });
    }
};
