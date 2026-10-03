<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimbingan_kelompoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->unique()->constrained('kasus_bks')->cascadeOnDelete();
            $table->date('tanggal_layanan');
            $table->timestamps();
        });

        Schema::create('bimbingan_kelompok_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bimbingan_kelompok_id')->constrained('bimbingan_kelompoks')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['bimbingan_kelompok_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan_kelompok_siswas');
        Schema::dropIfExists('bimbingan_kelompoks');
    }
};
