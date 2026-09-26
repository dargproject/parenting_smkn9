<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $aktifId = DB::table('tahun_ajarans')->where('is_active', true)->value('id');

        if ($aktifId) {
            DB::table('jadwal_pelajarans')->whereNull('tahun_ajaran_id')->update(['tahun_ajaran_id' => $aktifId]);
        }
    }

    public function down(): void
    {
    }
};
