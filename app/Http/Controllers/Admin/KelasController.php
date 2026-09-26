<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Models\Guru;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $kelases = Kelas::with(['waliKelas', 'guruWali'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama_kelas', 'like', '%'.$request->q.'%')->orWhere('jurusan', 'like', '%'.$request->q.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->tingkat))
            ->when($request->filled('jurusan'), fn ($q) => $q->where('jurusan', $request->jurusan))
            ->orderBy('tingkat')->orderBy('jurusan')->orderBy('nama_kelas')->paginate(15);

        return view('admin.kelas.index', ['kelases' => $kelases, 'jurusanOptions' => Kelas::orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan')]);
    }

    public function create()
    {
        return view('admin.kelas.create', ['kelas' => new Kelas, 'gurus' => Guru::with('roles')->orderBy('nama')->get()]);
    }

    public function store(StoreKelasRequest $request)
    {
        Kelas::create($request->validated());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.edit', ['kelas' => $kelas, 'gurus' => Guru::with('roles')->orderBy('nama')->get()]);
    }

    public function update(StoreKelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
