<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\PanggilanOrtu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanggilanOrtuBkController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'ruang' => 'nullable|string|max:255',
            'alasan' => 'required|string',
        ]);

        if (PanggilanOrtu::where('siswa_id', $data['siswa_id'])->whereDate('tanggal', $data['tanggal'])->where('waktu', $data['waktu'])->exists()) {
            return back()->withInput()->with('error', 'Panggilan orang tua untuk siswa ini pada tanggal dan jam tersebut sudah dijadwalkan.');
        }

        PanggilanOrtu::create($data + [
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => Auth::id(),
        ]);

        return back()->with('success', 'Panggilan orang tua berhasil dijadwalkan.');
    }
}
