<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AkunController extends Controller
{
    /**
     * Halaman permanen "Akun Saya" -- supaya nama & password tetap bisa diubah kapan saja,
     * tidak hanya lewat modal wajib isi profil yang cuma tampil sekali di login pertama.
     */
    public function edit()
    {
        return view('ortu.akun', ['orangTua' => Auth::guard('orangtua')->user()]);
    }

    /**
     * Dipanggil baik dari modal wajib isi profil (login pertama, nama & password masih default)
     * maupun dari halaman "Akun Saya". Password wajib diisi hanya selama harus_lengkapi_profil
     * masih true; setelah itu jadi opsional supaya bisa ganti nama saja tanpa ganti password.
     */
    public function update(Request $request)
    {
        $orangTua = Auth::guard('orangtua')->user();
        $passwordWajib = $orangTua->harus_lengkapi_profil;

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'password' => [$passwordWajib ? 'required' : 'nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $orangTua->nama = $data['nama'];
        if (! empty($data['password'])) {
            $orangTua->password = Hash::make($data['password']);
        }
        $orangTua->harus_lengkapi_profil = false;
        $orangTua->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
