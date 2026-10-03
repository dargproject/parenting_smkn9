<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaya_belajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->date('tanggal');
            $table->json('jawaban')->nullable();
            $table->unsignedTinyInteger('visual')->default(0);
            $table->unsignedTinyInteger('auditori')->default(0);
            $table->unsignedTinyInteger('kinestetik')->default(0);
            $table->string('hasil')->nullable();
            $table->text('catatan')->nullable();
            $table->text('faktor_penghambat')->nullable();
            $table->text('faktor_pendukung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaya_belajars');
    }
};
