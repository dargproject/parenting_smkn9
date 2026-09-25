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
        return view('admin.dashboard', [
            'totalGuru' => Guru::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'tahunAjaranAktif' => TahunAjaran::where('is_active', true)->first(),
            'pelanggaranTerbaru' => Pelanggaran::with('siswa')->latest('tanggal')->limit(5)->get(),
            'kasusBkTerbaru' => KasusBk::with('siswa')->latest()->limit(5)->get(),
        ]);
    }
}
