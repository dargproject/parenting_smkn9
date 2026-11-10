<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penempatan_pkls', function (Blueprint $table) {
            $table->string('predikat_progres')->nullable()->after('catatan_administrasi');
            $table->text('catatan_progres')->nullable()->after('predikat_progres');
        });
    }

    public function down(): void
    {
        Schema::table('penempatan_pkls', function (Blueprint $table) {
            $table->dropColumn(['predikat_progres', 'catatan_progres']);
        });
    }
};
