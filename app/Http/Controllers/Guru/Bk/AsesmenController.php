<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AsesmenController extends Controller
{
    public function index(Request $request)
    {
        $tingkat = $request->get('tingkat');
        $siswas = collect();

        if (in_array($tingkat, ['X', 'XI', 'XII'], true)) {
            $siswas = Siswa::with('kelas')
                ->whereHas('kelas', fn ($q) => $q->where('tingkat', $tingkat))
                ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->kelas_id))
                ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
                ->when($request->filled('search'), fn ($q) => $q->where('nama', 'like', '%'.$request->search.'%'))
                ->orderBy('nama')
                ->get();
        }

        return view('guru.bk.asesmen.index', [
            'tingkat' => $tingkat,
            'siswas' => $siswas,
            'kelasOptions' => $tingkat ? Kelas::where('tingkat', $tingkat)->orderBy('nama_kelas')->pluck('nama_kelas', 'id') : collect(),
            'jurusanOptions' => $tingkat ? Kelas::where('tingkat', $tingkat)->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan', 'jurusan') : collect(),
        ]);
    }
}
