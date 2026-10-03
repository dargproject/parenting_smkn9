<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alih_tangan_kasuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->constrained('kasus_bks')->cascadeOnDelete();
            $table->foreignId('konselor_asal_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('konselor_tujuan_id')->constrained('gurus')->cascadeOnDelete();
            $table->date('tanggal_alih');
            $table->text('alasan_alih')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alih_tangan_kasuses');
    }
};
