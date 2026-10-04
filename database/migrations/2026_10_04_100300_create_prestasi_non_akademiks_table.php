<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasi_non_akademiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->string('nama_prestasi');
            $table->string('tingkat');
            $table->string('peringkat')->nullable();
            $table->date('tanggal');
            $table->string('penyelenggara')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi_non_akademiks');
    }
};
