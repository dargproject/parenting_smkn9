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
        Schema::create('pasals', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('jenis_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->unsignedInteger('poin');
            $table->timestamps();
        });

        Schema::create('master_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasal_id')->constrained('pasals')->cascadeOnDelete();
            $table->foreignId('jenis_id')->constrained('jenis_pelanggarans')->cascadeOnDelete();
            $table->string('nama_pelanggaran');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kategori_pengumumans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->foreignId('master_pelanggaran_id')->nullable()->after('siswa_id')->constrained('master_pelanggarans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_pelanggaran_id');
        });
        Schema::dropIfExists('kategori_pengumumans');
        Schema::dropIfExists('master_pelanggarans');
        Schema::dropIfExists('jenis_pelanggarans');
        Schema::dropIfExists('pasals');
    }
};
