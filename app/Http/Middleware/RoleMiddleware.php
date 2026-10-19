<?php

namespace App\Http\Middleware;

use App\Models\Guru;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Session kosong tapi cookie "remember me" sudah login-kan ulang guru-nya (mis. setelah
        // sesi lama kedaluwarsa di PWA) -- bangun lagi data session role dari user yang aktif,
        // supaya tidak dipaksa login manual selama cookie remember masih berlaku.
        if (! Session::has('guru_id') && ! Session::has('role') && Auth::guard('web')->check()) {
            $guru = Auth::guard('web')->user()->load('roles');
            $roleNames = $guru->roles->pluck('name')->toArray();

            Session::put('roles', $roleNames);
            Session::put('role', $roleNames[0] ?? '');
            Session::put('guru_id', $guru->id);
            Session::put('guru_nama', $guru->nama);
        }

        // Periksa apakah ada guru_id di session
        if (! Session::has('guru_id') && ! Session::has('role')) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (Session::has('guru_id')) {
            $guru = Guru::with('roles')->find(Session::get('guru_id'));
            if ($guru) {
                foreach ($roles as $role) {
                    if ($guru->roles->contains('name', $role)) {
                        return $next($request);
                    }
                }
            }
        } else {
            // Fallback untuk role non-guru (seperti siswa, ortu, kepsek) yang disimulasikan
            $userRole = Session::get('role');
            if (in_array($userRole, $roles)) {
                return $next($request);
            }
        }

        // Jika role tidak sesuai, redirect ke login (atau bisa juga ke halaman error 403)
        return redirect()->route('login')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
