<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['kunjungan_rumahs', 'konferensi_kasuses', 'gaya_belajars', 'peminatans'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('tampilkan_ke_ortu')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['kunjungan_rumahs', 'konferensi_kasuses', 'gaya_belajars', 'peminatans'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('tampilkan_ke_ortu');
            });
        }
    }
};
