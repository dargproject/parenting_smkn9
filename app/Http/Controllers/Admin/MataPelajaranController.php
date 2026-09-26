<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMataPelajaranRequest;
use App\Models\Guru;
use App\Models\MataPelajaran;

class MataPelajaranController extends Controller
{
    public function index()
    {
        return view('admin.mata-pelajaran.index', ['mapels' => MataPelajaran::with('guru')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.mata-pelajaran.create', ['mapel' => new MataPelajaran, 'gurus' => Guru::orderBy('nama')->get()]);
    }

    public function store(StoreMataPelajaranRequest $request)
    {
        MataPelajaran::create($request->validated());

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('admin.mata-pelajaran.edit', ['mapel' => $mataPelajaran, 'gurus' => Guru::orderBy('nama')->get()]);
    }

    public function update(StoreMataPelajaranRequest $request, MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update($request->validated());
        JadwalPelajaran::where('mata_pelajaran_id', $mataPelajaran->id)->update(['guru_id' => $mataPelajaran->guru_id]);

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
