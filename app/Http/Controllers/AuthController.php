<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // PWA selalu buka ke /login (start_url). Kalau cookie "remember me" masih
        // valid, jangan tampilkan form lagi -- langsung lanjut ke portal.
        if (Auth::check()) {
            $user = Auth::user();
            $roles = $user->roles->pluck('name')->toArray();

            Session::put('roles', $roles);
            Session::put('role', $roles[0] ?? '');
            Session::put('guru_id', $user->id);
            Session::put('guru_nama', $user->nama);

            return $this->redirectByRole($roles);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        // Bersihkan session sebelumnya
        Session::flush();

        if (Auth::attempt(['nip' => $request->nip, 'password' => $request->password], true)) {
            $user = Auth::user();

            // Muat relasi roles
            $roles = $user->roles->pluck('name')->toArray();

            Session::put('roles', $roles);
            Session::put('role', $roles[0] ?? ''); // Role utama
            Session::put('guru_id', $user->id);
            Session::put('guru_nama', $user->nama);

            return $this->redirectByRole($roles);
        }

        return redirect()->route('login')->with('error', 'NIP/Username atau Password salah (Invalid Credentials).');
    }

    private function redirectByRole(array $roles)
    {
        // Jika memiliki role kepsek, arahkan ke dashboard kepsek
        if (in_array('kepsek', $roles)) {
            return redirect()->route('kepsek.dashboard');
        } elseif (in_array('admin', $roles)) {
            return redirect()->route('admin.dashboard');
        } elseif (in_array('siswa', $roles)) {
            return redirect()->route('siswa.portal');
        }

        return redirect()->route('guru.portal');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
