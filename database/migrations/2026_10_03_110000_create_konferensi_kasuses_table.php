<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konferensi_kasuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->constrained('kasus_bks')->cascadeOnDelete();
            $table->date('tanggal_konferensi');
            $table->string('tempat_pertemuan')->nullable();
            $table->timestamps();
        });

        Schema::create('konferensi_kasus_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konferensi_kasus_id')->constrained('konferensi_kasuses')->cascadeOnDelete();
            $table->string('nama_peserta');
            $table->string('peran_peserta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konferensi_kasus_pesertas');
        Schema::dropIfExists('konferensi_kasuses');
    }
};
