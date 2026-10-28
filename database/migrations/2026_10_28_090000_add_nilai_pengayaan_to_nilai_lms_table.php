<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_lms', function (Blueprint $table) {
            $table->unsignedTinyInteger('nilai_pengayaan')->nullable()->after('sudah_pengayaan');
        });
    }

    public function down(): void
    {
        Schema::table('nilai_lms', function (Blueprint $table) {
            $table->dropColumn('nilai_pengayaan');
        });
    }
};
