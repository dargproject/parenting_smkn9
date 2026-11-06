<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ekstrakurikuler sebelumnya cuma punya 1 pembina (kolom guru_id tunggal) -- diganti jadi
     * relasi banyak-ke-banyak supaya satu ekskul bisa dibina beberapa guru sekaligus, semuanya
     * dengan akses yang sama (kelola peserta & nilai). Data guru_id yang sudah ada dipindah ke
     * tabel pivot dulu sebelum kolomnya dihapus, supaya tidak ada data pembina yang hilang.
     */
    public function up(): void
    {
        Schema::create('ekstrakurikuler_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'guru_id']);
        });

        $now = now();
        $rows = DB::table('ekstrakurikulers')->whereNotNull('guru_id')->get(['id', 'guru_id']);
        foreach ($rows as $row) {
            DB::table('ekstrakurikuler_guru')->insert([
                'ekstrakurikuler_id' => $row->id,
                'guru_id' => $row->guru_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guru_id');
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->foreignId('guru_id')->nullable()->after('nama_ekskul')->constrained('gurus')->nullOnDelete();
        });

        // Rollback hanya bisa menyimpan 1 pembina per ekskul (yang pertama) -- keterbatasan
        // bawaan karena skema lama memang cuma mendukung 1 pembina.
        $pembinaPertama = DB::table('ekstrakurikuler_guru')
            ->select('ekstrakurikuler_id', DB::raw('MIN(guru_id) as guru_id'))
            ->groupBy('ekstrakurikuler_id')
            ->get();

        foreach ($pembinaPertama as $row) {
            DB::table('ekstrakurikulers')->where('id', $row->ekstrakurikuler_id)->update(['guru_id' => $row->guru_id]);
        }

        Schema::dropIfExists('ekstrakurikuler_guru');
    }
};
