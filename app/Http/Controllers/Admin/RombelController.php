<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelController extends Controller
{
    public function index()
    {
        return view('admin.rombel.index', ['kelas' => Kelas::with('siswas')->orderBy('tingkat')->get(), 'siswas' => Siswa::with('kelas')->orderBy('nama')->get(), 'tahunAjarans' => TahunAjaran::orderByDesc('is_active')->get()]);
    }

    public function assign(Request $request)
    {
        $data = $request->validate(['kelas_id' => 'required|exists:kelas,id', 'siswa_ids' => 'required|array', 'siswa_ids.*' => 'exists:siswas,id']);
        Siswa::whereIn('id', $data['siswa_ids'])->update(['kelas_id' => $data['kelas_id']]);

        return back()->with('success', 'Siswa berhasil diplot ke rombel.');
    }

    public function promote(Request $request)
    {
        $data = $request->validate(['from_kelas_id' => 'required|exists:kelas,id', 'to_kelas_id' => 'required|exists:kelas,id|different:from_kelas_id']);
        DB::transaction(fn () => Siswa::where('kelas_id', $data['from_kelas_id'])->update(['kelas_id' => $data['to_kelas_id']]));

        return back()->with('success', 'Kenaikan kelas berhasil diproses.');
    }
}
