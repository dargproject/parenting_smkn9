<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindah dari OrangTua 1 siswa (kolom siswa_id di orang_tuas) ke banyak siswa per akun
     * (tabel pivot ini) -- supaya satu orang tua dengan anak kembar/beberapa anak di sekolah
     * ini bisa pakai satu No. HP & password untuk memantau semuanya, bukan harus bikin akun
     * terpisah per anak. is_active di pivot ini menggantikan makna "persetujuan per anak";
     * is_active di orang_tuas sendiri jadi murni "akun ini sudah pernah disetujui, boleh login".
     *
     * Kolom siswa_id di orang_tuas dibangun ulang lewat tabel baru (bukan dropColumn langsung)
     * karena SQLite menolak men-drop kolom yang masih jadi sisi foreign key -- dropColumn biasa
     * gagal dengan "unknown column siswa_id in foreign key definition".
     */
    public function up(): void
    {
        Schema::create('orang_tua_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_tua_id')->constrained('orang_tuas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete()->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Data lama: setiap orang_tuas pasti punya tepat satu siswa_id (unik), pindahkan persis
        // ke satu baris pivot, is_active ikut nilai yang sudah ada supaya perilaku tidak berubah.
        DB::table('orang_tuas')->select('id', 'siswa_id', 'is_active')->get()->each(function ($ortu) {
            DB::table('orang_tua_siswa')->insert([
                'orang_tua_id' => $ortu->id,
                'siswa_id' => $ortu->siswa_id,
                'is_active' => $ortu->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $orangTuaLama = DB::table('orang_tuas')->orderBy('id')->get();

        Schema::create('orang_tuas_baru', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('harus_lengkapi_profil')->default(false);
            $table->string('username', 50)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        foreach ($orangTuaLama as $ortu) {
            DB::table('orang_tuas_baru')->insert([
                'id' => $ortu->id,
                'nama' => $ortu->nama,
                'harus_lengkapi_profil' => $ortu->harus_lengkapi_profil,
                'username' => $ortu->username,
                'email' => $ortu->email,
                'password' => $ortu->password,
                'is_active' => $ortu->is_active,
                'remember_token' => $ortu->remember_token,
                'created_at' => $ortu->created_at,
                'updated_at' => $ortu->updated_at,
            ]);
        }

        Schema::drop('orang_tuas');
        Schema::rename('orang_tuas_baru', 'orang_tuas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orang_tuas', function (Blueprint $table) {
            $table->foreignId('siswa_id')->nullable()->after('id')->constrained('siswas')->cascadeOnDelete();
        });

        DB::table('orang_tua_siswa')->select('orang_tua_id', 'siswa_id', 'is_active')->orderBy('id')->get()
            ->unique('orang_tua_id')
            ->each(function ($link) {
                DB::table('orang_tuas')->where('id', $link->orang_tua_id)->update([
                    'siswa_id' => $link->siswa_id,
                    'is_active' => $link->is_active,
                ]);
            });

        Schema::table('orang_tuas', function (Blueprint $table) {
            $table->unique('siswa_id');
        });

        Schema::dropIfExists('orang_tua_siswa');
    }
};
