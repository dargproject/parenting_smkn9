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
     * dicocokkan otomatis untuk verifikasi, jadi tautan yang baru didaftarkan di sini mulai
     * sebagai is_active=false dan menunggu wali kelas menyetujuinya (lihat PortalController::setujuiAkunOrtu).
     */
    public function create()
    {
        return view('auth.daftar-ortu', ['kelasList' => Kelas::orderBy('nama_kelas', 'ASC')->get()]);
    }

    /**
     * Daftar siswa per kelas untuk dropdown pendaftaran -- siswa yang sudah punya tautan akun
     * ortu (aktif maupun masih menunggu persetujuan) tidak ditampilkan lagi supaya tidak ada
     * yang mencoba mendaftar dobel untuk siswa yang sama.
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

    /**
     * Kalau No. HP yang diisi sudah dipakai akun lain (anak kembar/kakak-adik, orang tua yang
     * sama), siswa ini ditautkan ke akun ITU (setelah verifikasi password akun tersebut), bukan
     * membuat akun baru -- supaya satu orang tua bisa pakai satu No. HP & password untuk
     * memantau semua anaknya di sekolah ini. Nama & password yang diisi di form tidak dipakai
     * pada kasus ini (akun lama tetap dengan nama/password-nya sendiri); password yang diisi
     * cuma dipakai untuk membuktikan ini memang pemilik akun itu, bukan orang lain yang
     * kebetulan tahu No. HP-nya.
     */
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

        $existing = OrangTua::where('username', $username)->first();

        if ($existing) {
            if (! Hash::check($data['password'], $existing->password)) {
                return back()->withInput()->with('error', "No. HP {$data['no_hp']} sudah terdaftar. Masukkan password akun tersebut untuk menautkan anak ini ke akun yang sama, atau hubungi wali kelas/admin bila Anda lupa password.");
            }

            $siswa->update(['no_hp_ortu' => $username]);
            $existing->links()->create(['siswa_id' => $siswa->id, 'is_active' => false]);

            return redirect()->route('ortu.login')->with('success', 'Anak ini berhasil ditautkan ke akun Anda yang sudah ada. Akun bisa dipakai untuk memantau '.$siswa->nama.' setelah disetujui oleh wali kelas '.$siswa->nama.'.');
        }

        $siswa->update(['no_hp_ortu' => $username]);

        $orangTua = OrangTua::create([
            'nama' => $data['nama'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'is_active' => false,
            'harus_lengkapi_profil' => false,
        ]);
        $orangTua->links()->create(['siswa_id' => $siswa->id, 'is_active' => false]);

        return redirect()->route('ortu.login')->with('success', 'Pendaftaran berhasil. Akun Anda akan bisa digunakan setelah disetujui oleh wali kelas '.$siswa->nama.'.');
    }
}
