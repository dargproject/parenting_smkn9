<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('roles')->where('name', 'tatib')->exists()) {
            DB::table('roles')->insert(['name' => 'tatib', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'tatib')->delete();
    }
};