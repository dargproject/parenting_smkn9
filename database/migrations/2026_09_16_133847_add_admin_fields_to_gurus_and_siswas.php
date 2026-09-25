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
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nisn')->nullable()->unique();
            $table->string('nipd')->nullable()->unique();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_hp_ortu')->nullable();
            $table->boolean('status_aktif')->default(true);
        });

        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->string('kode_mapel')->nullable()->unique();
            $table->string('kelompok', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'phone', 'is_active']);
        });
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique(['nisn']);
            $table->dropUnique(['nipd']);
            $table->dropColumn(['nisn', 'nipd', 'jenis_kelamin', 'tanggal_lahir', 'no_hp_ortu', 'status_aktif']);
        });
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropUnique(['kode_mapel']);
            $table->dropColumn(['kode_mapel', 'kelompok']);
        });
    }
};
