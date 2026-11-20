<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PilihAnakController extends Controller
{
    /**
     * Ganti anak yang sedang dipantau -- disimpan di sesi, dipakai DashboardController &
     * KeluargaController lewat trait MemilihSiswaAktif. Cuma anak yang tautannya sudah
     * disetujui ke akun ini yang boleh dipilih.
     */
    public function pilih(Request $request)
    {
        $data = $request->validate(['siswa_id' => 'required|integer']);

        $orangTua = Auth::guard('orangtua')->user();
        $valid = $orangTua->siswaAktif()->where('siswas.id', $data['siswa_id'])->exists();

        abort_unless($valid, 403, 'Anak tersebut tidak tertaut ke akun Anda.');

        session(['ortu_siswa_id' => (int) $data['siswa_id']]);

        return back();
    }
}
