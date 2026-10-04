<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PanggilanOrtu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanggilanOrtuController extends Controller
{
    public function update(Request $request, PanggilanOrtu $panggilanOrtu)
    {
        abort_unless($panggilanOrtu->pemanggil_id === Auth::id(), 403, 'Anda tidak memiliki akses untuk mengubah panggilan ini.');

        $data = $request->validate([
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'ruang' => 'nullable|string|max:255',
            'alasan' => 'required|string',
        ]);

        $jadwalBerubah = $data['tanggal'] !== Carbon::parse($panggilanOrtu->tanggal)->toDateString()
            || $data['waktu'] !== Carbon::parse($panggilanOrtu->waktu)->format('H:i');

        $statusDikembalikan = $jadwalBerubah && str_contains($panggilanOrtu->status, 'Hadir');
        if ($statusDikembalikan) {
            $data['status'] = 'Menunggu Konfirmasi';
        }

        $panggilanOrtu->update($data);

        $pesan = 'Panggilan orang tua berhasil diperbarui.';
        if ($statusDikembalikan) {
            $pesan .= ' Status dikembalikan ke "Menunggu Konfirmasi" karena jadwal diubah.';
        }

        return back()->with('success', $pesan);
    }

    public function updateStatus(Request $request, PanggilanOrtu $panggilanOrtu)
    {
        $data = $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Hadir / Mediasi Selesai',
        ]);

        $panggilanOrtu->update($data);

        return back()->with('success', 'Status panggilan orang tua berhasil diperbarui.');
    }

    public function destroy(PanggilanOrtu $panggilanOrtu)
    {
        abort_unless($panggilanOrtu->pemanggil_id === Auth::id(), 403, 'Anda tidak memiliki akses untuk menghapus panggilan ini.');

        $panggilanOrtu->delete();

        return back()->with('success', 'Panggilan orang tua berhasil dihapus.');
    }
}
