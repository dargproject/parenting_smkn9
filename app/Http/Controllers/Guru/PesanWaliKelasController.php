<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PesanWaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesanWaliKelasController extends Controller
{
    public function store(Request $request)
    {
        $guru = Auth::user();
        $siswaWaliIds = Kelas::where('wali_kelas_id', $guru->id)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'pesan' => 'required|string|max:2000',
        ]);

        abort_unless($siswaWaliIds->contains((int) $data['siswa_id']), 403, 'Siswa ini bukan bagian dari kelas perwalian Anda.');

        PesanWaliKelas::create(['siswa_id' => $data['siswa_id'], 'guru_id' => $guru->id, 'pesan' => $data['pesan']]);

        return redirect()->route('guru.portal')->with('success', 'Pesan berhasil dikirim ke orang tua.');
    }
}
