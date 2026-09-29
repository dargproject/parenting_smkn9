<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_perubahan_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nilai_lm_id')->constrained('nilai_lms')->cascadeOnDelete();
            $table->string('kolom', 30);
            $table->unsignedTinyInteger('nilai_lama')->nullable();
            $table->unsignedTinyInteger('nilai_baru')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_perubahan_nilai');
    }
};
