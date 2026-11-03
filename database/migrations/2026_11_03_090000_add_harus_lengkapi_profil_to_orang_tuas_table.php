<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orang_tuas', function (Blueprint $table) {
            // Default false (bukan true) supaya akun orang tua yang sudah ada sebelum fitur ini
            // tidak mendadak dipaksa isi ulang profil -- hanya akun BARU yang dibuatkan admin
            // (nama/password default) yang ditandai true secara eksplisit saat dibuat.
            $table->boolean('harus_lengkapi_profil')->default(false)->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('orang_tuas', function (Blueprint $table) {
            $table->dropColumn('harus_lengkapi_profil');
        });
    }
};
