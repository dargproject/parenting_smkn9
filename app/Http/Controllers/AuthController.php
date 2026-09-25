<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Guru;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string'
        ]);

        // Bersihkan session sebelumnya
        Session::flush();

        if (\Illuminate\Support\Facades\Auth::attempt(['nip' => $request->nip, 'password' => $request->password])) {
            $user = \Illuminate\Support\Facades\Auth::user();

            // Muat relasi roles
            $roles = $user->roles->pluck('name')->toArray();

            Session::put('roles', $roles);
            Session::put('role', $roles[0] ?? ''); // Role utama
            Session::put('guru_id', $user->id);
            Session::put('guru_nama', $user->nama);

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

        return redirect()->route('login')->with('error', 'NIP/Username atau Password salah (Invalid Credentials).');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
