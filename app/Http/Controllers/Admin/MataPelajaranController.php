<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMataPelajaranRequest;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $mapels = MataPelajaran::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama_mapel', 'like', '%'.$request->q.'%')->orWhere('kode_mapel', 'like', '%'.$request->q.'%')))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('kelompok'), fn ($q) => $q->where('kelompok', $request->kelompok))
            ->latest()->paginate(15);

        return view('admin.mata-pelajaran.index', ['mapels' => $mapels]);
    }

    public function create()
    {
        return view('admin.mata-pelajaran.create', ['mapel' => new MataPelajaran]);
    }

    public function store(StoreMataPelajaranRequest $request)
    {
        $data = $request->validated();
        $data['wajib_dinilai'] = $request->boolean('wajib_dinilai');

        MataPelajaran::create($data);

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('admin.mata-pelajaran.edit', ['mapel' => $mataPelajaran]);
    }

    public function update(StoreMataPelajaranRequest $request, MataPelajaran $mataPelajaran)
    {
        $data = $request->validated();
        $data['wajib_dinilai'] = $request->boolean('wajib_dinilai');

        $mataPelajaran->update($data);

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
