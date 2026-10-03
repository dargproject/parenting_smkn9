<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimbingan_individus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->unique()->constrained('kasus_bks')->cascadeOnDelete();
            $table->date('tanggal_layanan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan_individus');
    }
};
