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
        Schema::create('kasus_bks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('judul');
            $table->string('kategori'); // Mendesak, Normal, Rujukan Wali
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['antrean', 'proses', 'selesai'])->default('antrean');
            $table->foreignId('konselor_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasus_bks');
    }
};
