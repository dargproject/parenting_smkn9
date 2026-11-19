<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PendaftaranController extends Controller
{
    /**
     * Pendaftaran akun ortu mandiri -- tidak ada data (No. HP/Tanggal Lahir) yang bisa
     * dicocokkan otomatis untuk verifikasi, jadi akun yang baru didaftarkan di sini mulai
     * sebagai is_active=false dan menunggu wali kelas menyetujuinya (lihat PortalController::setujuiAkunOrtu).
     */
    public function create()
    {
        return view('auth.daftar-ortu', ['kelasList' => Kelas::orderBy('nama_kelas', 'ASC')->get()]);
    }

    /**
     * Daftar siswa per kelas untuk dropdown pendaftaran -- siswa yang sudah punya akun ortu
     * (aktif maupun masih menunggu persetujuan) tidak ditampilkan lagi supaya tidak ada yang
     * mencoba mendaftar dobel untuk siswa yang sama.
     */
    public function siswaByKelas(Kelas $kelas)
    {
        $siswa = Siswa::where('kelas_id', $kelas->id)
            ->where('status_aktif', true)
            ->whereDoesntHave('orangTua')
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return response()->json(['siswa' => $siswa]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $siswa = Siswa::findOrFail($data['siswa_id']);

        if ($siswa->orangTua) {
            return back()->withInput()->with('error', 'Siswa ini sudah memiliki akun orang tua (aktif atau masih menunggu persetujuan wali kelas). Hubungi wali kelas atau admin bila ini keliru.');
        }

        $username = preg_replace('/\D/', '', $data['no_hp']);

        if ($username === '') {
            return back()->withInput()->with('error', 'No. HP tidak valid.');
        }

        if (OrangTua::where('username', $username)->exists()) {
            return back()->withInput()->with('error', "No. HP {$data['no_hp']} sudah terdaftar untuk siswa lain. Hubungi admin bila Anda memiliki lebih dari satu anak di sekolah ini.");
        }

        $siswa->update(['no_hp_ortu' => $username]);

        OrangTua::create([
            'siswa_id' => $siswa->id,
            'nama' => $data['nama'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'is_active' => false,
            'harus_lengkapi_profil' => false,
        ]);

        return redirect()->route('ortu.login')->with('success', 'Pendaftaran berhasil. Akun Anda akan bisa digunakan setelah disetujui oleh wali kelas '.$siswa->nama.'.');
    }
}
