<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\KasusBk;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $kasusBks = KasusBk::with(['siswa.kelas'])->where('konselor_id', Auth::id())->get();

        $perStatus = $kasusBks->groupBy('status')->map->count();
        $perPrioritas = $kasusBks->groupBy('prioritas')->map->count();
        $perTingkat = $kasusBks->groupBy(fn ($k) => $k->siswa?->kelas?->tingkat ?? '-')->map(fn ($g) => $g->pluck('siswa_id')->unique()->count());

        $kasusTerbaru = $kasusBks->sortByDesc('created_at')->take(5)->values();

        return view('guru.bk.dashboard', [
            'totalKasus' => $kasusBks->count(),
            'perStatus' => $perStatus,
            'perPrioritas' => $perPrioritas,
            'perTingkat' => $perTingkat,
            'kasusTerbaru' => $kasusTerbaru,
        ]);
    }
}
