<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_lms', function (Blueprint $table) {
            $table->unsignedTinyInteger('nilai_remedial')->nullable()->after('nilai');
            $table->boolean('sudah_pengayaan')->default(false)->after('nilai_remedial');
            $table->string('catatan_pengayaan', 500)->nullable()->after('sudah_pengayaan');
        });
    }

    public function down(): void
    {
        Schema::table('nilai_lms', function (Blueprint $table) {
            $table->dropColumn(['nilai_remedial', 'sudah_pengayaan', 'catatan_pengayaan']);
        });
    }
};
