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
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['is_wali_kelas', 'is_guru_wali', 'is_guru_mapel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->boolean('is_wali_kelas')->default(false);
            $table->boolean('is_guru_wali')->default(false);
            $table->boolean('is_guru_mapel')->default(false);
        });
    }
};
