<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rujukan_bks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('dirujuk_oleh')->constrained('gurus')->cascadeOnDelete();
            $table->string('kategori');
            $table->text('alasan');
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->foreignId('kasus_bk_id')->nullable()->constrained('kasus_bks')->nullOnDelete();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('gurus')->nullOnDelete();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rujukan_bks');
    }
};
