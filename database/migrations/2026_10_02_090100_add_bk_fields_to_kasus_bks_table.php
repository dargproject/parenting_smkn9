<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasus_bks', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('siswa_id')->constrained('tahun_ajarans')->nullOnDelete();
            $table->foreignId('kategori_id')->nullable()->after('kategori')->constrained('kategori_kasus')->nullOnDelete();
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('rendah')->after('status');
            $table->date('tanggal_mulai')->nullable()->after('prioritas');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->text('tindak_lanjut')->nullable()->after('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('kasus_bks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tahun_ajaran_id');
            $table->dropConstrainedForeignId('kategori_id');
            $table->dropColumn(['prioritas', 'tanggal_mulai', 'tanggal_selesai', 'tindak_lanjut']);
        });
    }
};
