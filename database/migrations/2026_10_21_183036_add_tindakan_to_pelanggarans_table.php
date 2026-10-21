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
        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('tanggal')->constrained('tahun_ajarans')->nullOnDelete();
            $table->text('tindakan')->nullable()->after('foto');
            $table->string('foto_tindakan')->nullable()->after('tindakan');
            $table->foreignId('ditindak_oleh')->nullable()->after('foto_tindakan')->constrained('gurus')->nullOnDelete();
        });

        // Backfill data lama: tabel tahun_ajarans tidak punya rentang tanggal untuk dicocokkan
        // dengan kolom "tanggal" di pelanggarans, jadi anggap semua data lama milik tahun ajaran
        // yang aktif sekarang (asumsi wajar untuk sistem yang baru berjalan).
        $tahunAjaranAktifId = DB::table('tahun_ajarans')->where('is_active', true)->value('id');
        if ($tahunAjaranAktifId) {
            DB::table('pelanggarans')->whereNull('tahun_ajaran_id')->update(['tahun_ajaran_id' => $tahunAjaranAktifId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropForeign(['ditindak_oleh']);
            $table->dropColumn(['tahun_ajaran_id', 'tindakan', 'foto_tindakan', 'ditindak_oleh']);
        });
    }
};
