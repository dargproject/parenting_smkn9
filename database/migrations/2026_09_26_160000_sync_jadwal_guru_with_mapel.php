<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE jadwal_pelajarans SET guru_id = (SELECT guru_id FROM mata_pelajarans WHERE mata_pelajarans.id = jadwal_pelajarans.mata_pelajaran_id) WHERE EXISTS (SELECT 1 FROM mata_pelajarans WHERE mata_pelajarans.id = jadwal_pelajarans.mata_pelajaran_id)');
    }

    public function down(): void
    {
    }
};
