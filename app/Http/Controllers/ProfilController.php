<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function update(Request $request)
    {
        $guru = Auth::user();

        $data = $request->validateWithBag('profil', [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:gurus,nip,'.$guru->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $guru->update($data);
        Session::put('guru_nama', $guru->nama);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $guru = Auth::user();

        $data = $request->validateWithBag('password', [
            'password_lama' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $guru->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
