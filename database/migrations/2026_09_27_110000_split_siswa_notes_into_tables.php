<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $kolomLama = [
        'catatan_wali', 'catatan_akademik', 'counseling_stress', 'counseling_career',
        'counseling_note', 'total_alpa', 'total_violation_points',
    ];

    public function up(): void
    {
        Schema::create('catatan_akademik_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        Schema::create('asesmen_bks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('konselor_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->unsignedTinyInteger('tingkat_stres')->nullable();
            $table->string('minat_karir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        // Pindahkan data lama ke tahun ajaran aktif (atau yang terbaru bila belum ada yang aktif).
        $tahunAjaranId = DB::table('tahun_ajarans')->where('is_active', true)->value('id')
            ?? DB::table('tahun_ajarans')->orderByDesc('id')->value('id');

        if ($tahunAjaranId) {
            $now = now();
            foreach (DB::table('siswas')->get(['id', 'catatan_akademik', 'counseling_stress', 'counseling_career', 'counseling_note']) as $s) {
                if (filled($s->catatan_akademik)) {
                    DB::table('catatan_akademik_siswas')->insert(['siswa_id' => $s->id, 'tahun_ajaran_id' => $tahunAjaranId, 'catatan' => $s->catatan_akademik, 'created_at' => $now, 'updated_at' => $now]);
                }

                if ($s->counseling_stress !== null || filled($s->counseling_career) || filled($s->counseling_note)) {
                    DB::table('asesmen_bks')->insert(['siswa_id' => $s->id, 'tahun_ajaran_id' => $tahunAjaranId, 'tingkat_stres' => $s->counseling_stress, 'minat_karir' => $s->counseling_career, 'catatan' => $s->counseling_note, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn($this->kolomLama);
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->text('catatan_wali')->nullable();
            $table->text('catatan_akademik')->nullable();
            $table->unsignedTinyInteger('counseling_stress')->nullable();
            $table->string('counseling_career')->nullable();
            $table->text('counseling_note')->nullable();
            $table->unsignedInteger('total_alpa')->default(0);
            $table->unsignedInteger('total_violation_points')->default(0);
        });

        Schema::dropIfExists('asesmen_bks');
        Schema::dropIfExists('catatan_akademik_siswas');
    }
};