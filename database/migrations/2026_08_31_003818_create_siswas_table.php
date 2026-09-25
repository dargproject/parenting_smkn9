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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nama');
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('password');
            $table->text('catatan_wali')->nullable();
            $table->text('catatan_akademik')->nullable();
            $table->integer('counseling_stress')->default(0);
            $table->string('counseling_career')->nullable();
            $table->text('counseling_note')->nullable();
            $table->integer('total_alpa')->default(0);
            $table->integer('total_violation_points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
