<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('panggilan_ortus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('ruang')->nullable();
            $table->text('alasan');
            $table->string('status')->default('Menunggu Konfirmasi'); // Menunggu Konfirmasi, Hadir / Mediasi Selesai
            $table->foreignId('pemanggil_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panggilan_ortus');
    }
};
