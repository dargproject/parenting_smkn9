<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kelas_id', 'mata_pelajaran_id']);
        });

        // Isi awal dari jadwal yang sudah ada: kelas + mapel yang sudah dijadwalkan dianggap bagian struktur kurikulum kelas tsb.
        $now = now();
        $rows = DB::table('jadwal_pelajarans')
            ->join('mata_pelajarans', 'mata_pelajarans.id', '=', 'jadwal_pelajarans.mata_pelajaran_id')
            ->select('jadwal_pelajarans.kelas_id', 'jadwal_pelajarans.mata_pelajaran_id', DB::raw('COALESCE(jadwal_pelajarans.guru_id, mata_pelajarans.guru_id) as guru_id'))
            ->get()
            ->unique(fn ($r) => $r->kelas_id.'-'.$r->mata_pelajaran_id)
            ->map(fn ($r) => ['kelas_id' => $r->kelas_id, 'mata_pelajaran_id' => $r->mata_pelajaran_id, 'guru_id' => $r->guru_id, 'created_at' => $now, 'updated_at' => $now])
            ->values()
            ->all();

        if ($rows) {
            DB::table('kelas_mata_pelajaran')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_mata_pelajaran');
    }
};
