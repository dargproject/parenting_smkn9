<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tujuan_pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->string('kode', 20)->nullable();
            $table->string('deskripsi', 500);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['mata_pelajaran_id', 'tahun_ajaran_id']);
        });

        Schema::create('nilai_lms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tujuan_pembelajaran_id')->constrained('tujuan_pembelajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai');
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'tujuan_pembelajaran_id']);
            $table->index(['siswa_id', 'tahun_ajaran_id']);
        });

        Schema::create('nilai_sas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id']);
        });

        Schema::create('nilai_pkl_ukks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->enum('jenis', ['pkl', 'ukk']);
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'jenis']);
        });

        Schema::create('catatan_kompetensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->text('catatan');
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id']);
        });

        Schema::create('catatan_wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->text('catatan_karakter')->nullable();
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('tanpa_keterangan')->default(0);
            $table->text('catatan_ekskul')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        Schema::create('rapor_finals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamp('dirilis_at')->nullable();
            $table->foreignId('dirilis_oleh')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        Schema::create('orang_tuas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('nama');
            $table->string('username', 50)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tuas');
        Schema::dropIfExists('rapor_finals');
        Schema::dropIfExists('catatan_wali_kelas');
        Schema::dropIfExists('catatan_kompetensis');
        Schema::dropIfExists('nilai_pkl_ukks');
        Schema::dropIfExists('nilai_sas');
        Schema::dropIfExists('nilai_lms');
        Schema::dropIfExists('tujuan_pembelajarans');
    }
};
