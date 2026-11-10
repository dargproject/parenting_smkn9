<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penempatan_pkls', function (Blueprint $table) {
            $table->string('predikat_etika')->nullable()->after('catatan');
            $table->text('catatan_etika')->nullable()->after('predikat_etika');
            $table->string('predikat_kemandirian')->nullable()->after('catatan_etika');
            $table->text('catatan_kemandirian')->nullable()->after('predikat_kemandirian');
            $table->string('predikat_administrasi')->nullable()->after('catatan_kemandirian');
            $table->text('catatan_administrasi')->nullable()->after('predikat_administrasi');
        });
    }

    public function down(): void
    {
        Schema::table('penempatan_pkls', function (Blueprint $table) {
            $table->dropColumn([
                'predikat_etika',
                'catatan_etika',
                'predikat_kemandirian',
                'catatan_kemandirian',
                'predikat_administrasi',
                'catatan_administrasi',
            ]);
        });
    }
};
