<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengunduran_diris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('nama_ortu_wali');
            $table->text('alamat_ortu_wali');
            $table->text('alasan_pengunduran');
            $table->date('tanggal_pengunduran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengunduran_diris');
    }
};
