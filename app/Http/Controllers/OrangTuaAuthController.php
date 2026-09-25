<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrangTuaAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-ortu');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('orangtua')->attempt(['username' => $request->username, 'password' => $request->password, 'is_active' => true])) {
            $request->session()->regenerate();

            return redirect()->route('ortu.dashboard');
        }

        return redirect()->route('ortu.login')->with('error', 'Username atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::guard('orangtua')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ortu.login');
    }
}
