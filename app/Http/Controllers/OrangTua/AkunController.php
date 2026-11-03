<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AkunController extends Controller
{
    /**
     * Dipanggil dari modal wajib isi profil yang muncul saat login pertama kali setelah akun
     * dibuatkan admin (nama & password masih default). Setelah berhasil submit sekali,
     * harus_lengkapi_profil jadi false dan modal tidak muncul lagi di login berikutnya.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        Auth::guard('orangtua')->user()->update([
            'nama' => $data['nama'],
            'password' => Hash::make($data['password']),
            'harus_lengkapi_profil' => false,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
