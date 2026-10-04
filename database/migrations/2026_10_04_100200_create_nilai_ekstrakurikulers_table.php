<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_ekstrakurikulers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->enum('nilai', ['A', 'B', 'C']);
            $table->text('catatan')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'ekstrakurikuler_id', 'tahun_ajaran_id'], 'nilai_ekskul_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ekstrakurikulers');
    }
};
