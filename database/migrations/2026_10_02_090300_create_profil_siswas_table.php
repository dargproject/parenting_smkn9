<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('siswas')->cascadeOnDelete();
            $table->text('alamat')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->integer('anak_ke')->nullable();
            $table->integer('jml_saudara')->nullable();
            $table->string('asal_smp')->nullable();
            $table->string('hobi')->nullable();
            $table->string('bakat')->nullable();
            $table->enum('rencana_lulus', ['Bekerja', 'Kuliah', 'Menikah'])->nullable();
            $table->text('detail_rencana_lulus')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_siswas');
    }
};
