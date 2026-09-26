<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Models\Guru;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        return view('admin.kelas.index', ['kelases' => Kelas::with(['waliKelas', 'guruWali'])->latest()->paginate(15)]);
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
