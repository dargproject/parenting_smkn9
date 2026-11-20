<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pemilih anak (akun ortu bisa tertaut ke beberapa anak) dibagikan ke layout ortu secara
        // global, supaya setiap halaman ortu (dashboard, nilai mapel, keluarga, akun) bisa
        // menampilkannya di header tanpa tiap controller harus mengirim datanya sendiri-sendiri.
        View::composer('layouts.ortu', function ($view) {
            $orangTua = Auth::guard('orangtua')->user();

            if (! $orangTua) {
                return;
            }

            $anak = $orangTua->siswaAktif()->get();
            $dipilihId = session('ortu_siswa_id');
            $siswaDipilih = ($dipilihId ? $anak->firstWhere('id', $dipilihId) : null) ?? $anak->first();

            $view->with(['daftarAnakOrtu' => $anak, 'siswaDipilihOrtu' => $siswaDipilih]);
        });
    }
}
