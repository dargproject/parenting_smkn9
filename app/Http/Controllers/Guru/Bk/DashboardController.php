<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\BimbinganIndividu;
use App\Models\BimbinganKelompok;
use App\Models\KasusBk;
use App\Models\KonferensiKasus;
use App\Models\KunjunganRumah;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $konselorId = Auth::id();
        $kasusBks = KasusBk::with(['siswa.kelas'])->where('konselor_id', $konselorId)->get();

        $perStatus = $kasusBks->groupBy('status')->map->count();
        $perPrioritas = $kasusBks->groupBy('prioritas')->map->count();

        $countSiswaByTingkat = fn (string $tingkat) => $kasusBks
            ->filter(fn ($k) => ($k->siswa?->kelas?->tingkat) === $tingkat)
            ->pluck('siswa_id')->unique()->count();

        $kasusTerbaru = $kasusBks->sortByDesc('created_at')->take(5)->values();

        $bulanIni = now()->startOfMonth();
        $layananBulanIni = BimbinganIndividu::whereHas('kasusBk', fn ($q) => $q->where('konselor_id', $konselorId))->where('tanggal_layanan', '>=', $bulanIni)->count()
            + BimbinganKelompok::whereHas('kasusBk', fn ($q) => $q->where('konselor_id', $konselorId))->where('tanggal_layanan', '>=', $bulanIni)->count()
            + KunjunganRumah::whereHas('kasusBk', fn ($q) => $q->where('konselor_id', $konselorId))->where('tanggal_kunjungan', '>=', $bulanIni)->count()
            + KonferensiKasus::whereHas('kasusBk', fn ($q) => $q->where('konselor_id', $konselorId))->where('tanggal_konferensi', '>=', $bulanIni)->count();

        return view('guru.bk.dashboard', [
            'totalKasus' => $kasusBks->count(),
            'perStatus' => $perStatus,
            'perPrioritas' => $perPrioritas,
            'countKelasX' => $countSiswaByTingkat('X'),
            'countKelasXI' => $countSiswaByTingkat('XI'),
            'countKelasXII' => $countSiswaByTingkat('XII'),
            'layananBulanIni' => $layananBulanIni,
            'kasusTerbaru' => $kasusTerbaru,
        ]);
    }
}
