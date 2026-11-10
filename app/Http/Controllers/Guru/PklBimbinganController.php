<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PenempatanPkl;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PklBimbinganController extends Controller
{
    public function index()
    {
        return view('guru.pkl.index', [
            'items' => PenempatanPkl::where('guru_pembimbing_id', Auth::id())
                ->with(['siswa.kelas', 'tahunAjaran'])
                ->latest('id')
                ->get(),
            'predikatOptions' => PenempatanPkl::PREDIKAT_OPTIONS,
            'progresOptions' => PenempatanPkl::PROGRES_OPTIONS,
            'templateCatatan' => PenempatanPkl::TEMPLATE_CATATAN,
        ]);
    }

    /**
     * Guru pembimbing yang mengisi sendiri periode PKL siswa bimbingannya -- setiap pembimbing
     * biasanya punya periode berbeda, tapi semua siswa bimbingan pembimbing yang sama biasanya
     * berangkat dalam periode yang sama, jadi cukup satu form untuk mengatur semuanya sekaligus.
     */
    public function updatePeriode(Request $request)
    {
        $data = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $tahunAjaranAktifId = TahunAjaran::where('is_active', true)->value('id');

        PenempatanPkl::where('guru_pembimbing_id', Auth::id())
            ->when($tahunAjaranAktifId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktifId))
            ->update($data);

        return back()->with('success', 'Periode PKL berhasil disimpan untuk semua siswa bimbingan.');
    }

    public function update(Request $request, PenempatanPkl $penempatanPkl)
    {
        abort_unless($penempatanPkl->guru_pembimbing_id === Auth::id(), 403, 'Anda bukan pembimbing PKL siswa ini.');

        $predikat = implode(',', PenempatanPkl::PREDIKAT_OPTIONS);
        $progres = implode(',', PenempatanPkl::PROGRES_OPTIONS);

        $data = $request->validate([
            'predikat_etika' => ['nullable', "in:{$predikat}"],
            'catatan_etika' => ['nullable', 'string', 'max:1000'],
            'predikat_kemandirian' => ['nullable', "in:{$predikat}"],
            'catatan_kemandirian' => ['nullable', 'string', 'max:1000'],
            'predikat_administrasi' => ['nullable', "in:{$predikat}"],
            'catatan_administrasi' => ['nullable', 'string', 'max:1000'],
            'predikat_progres' => ['nullable', "in:{$progres}"],
            'catatan_progres' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $penempatanPkl->update($data);

        return back()->with('success', 'Catatan perkembangan PKL berhasil disimpan.');
    }
}
