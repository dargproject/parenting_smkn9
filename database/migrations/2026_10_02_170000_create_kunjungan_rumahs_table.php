<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_rumahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->unique()->constrained('kasus_bks')->cascadeOnDelete();
            $table->date('tanggal_kunjungan');
            $table->enum('status', ['diproses', 'ditunda', 'dibatalkan'])->default('diproses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_rumahs');
    }
};
