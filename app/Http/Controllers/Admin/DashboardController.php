<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik BK sengaja hanya agregat (jumlah per status/prioritas), tanpa nama siswa atau
        // judul kasus -- data BK bersifat rahasia dan tidak boleh terlihat oleh role selain guru BK.
        $kasusAktif = KasusBk::where('status', '!=', 'selesai');

        return view('admin.dashboard', [
            'totalGuru' => Guru::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'tahunAjaranAktif' => TahunAjaran::where('is_active', true)->first(),
            'pelanggaranTerbaru' => Pelanggaran::with('siswa')->latest('tanggal')->limit(5)->get(),
            'kasusBkAktif' => $kasusAktif->count(),
            'kasusBkPerPrioritas' => (clone $kasusAktif)->selectRaw('prioritas, COUNT(*) as total')->groupBy('prioritas')->pluck('total', 'prioritas'),
        ]);
    }
}
