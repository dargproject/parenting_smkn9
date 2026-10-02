<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_keluarga_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('telp_ortu')->nullable();
            $table->string('alamat_ayah')->nullable();
            $table->string('alamat_ibu')->nullable();
            $table->string('nomor_wa_ayah')->nullable();
            $table->string('nomor_wa_ibu')->nullable();
            $table->string('status_rumah')->nullable();
            $table->string('lokasi_rumah')->nullable();
            $table->string('dinding_rumah')->nullable();
            $table->string('lantai_rumah')->nullable();
            $table->integer('jml_kamar')->nullable();
            $table->boolean('punya_kamar_sendiri')->default(false);
            $table->integer('jml_tv')->nullable();
            $table->integer('kendaraan_mobil')->nullable();
            $table->integer('kendaraan_motor')->nullable();
            $table->string('biaya_sekolah_dari')->nullable();
            $table->string('kendaraan_ke_sekolah')->nullable();
            $table->string('media_sosial')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_keluarga_siswas');
    }
};
