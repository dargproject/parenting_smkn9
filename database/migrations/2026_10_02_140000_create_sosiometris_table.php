<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sosiometris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->date('tanggal');
            $table->string('judul')->default('Asesmen Sosiometri');
            $table->text('instruksi')->nullable();
            $table->unsignedTinyInteger('jumlah_pilihan')->default(3);
            $table->timestamps();
        });

        Schema::create('sosiometri_respons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sosiometri_id')->constrained('sosiometris')->cascadeOnDelete();
            $table->foreignId('siswa_dipilih_id')->nullable()->constrained('siswas')->nullOnDelete();
            $table->string('nama_dipilih')->nullable();
            $table->unsignedTinyInteger('urutan');
            $table->string('pertanyaan', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosiometri_respons');
        Schema::dropIfExists('sosiometris');
    }
};
