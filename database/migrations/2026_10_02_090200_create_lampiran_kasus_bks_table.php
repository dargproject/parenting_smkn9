<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_kasus_bks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_bk_id')->constrained('kasus_bks')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('tipe_file')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_kasus_bks');
    }
};
