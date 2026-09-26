<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $siswas = Siswa::with('kelas')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->q.'%')->orWhere('nis', 'like', '%'.$request->q.'%')->orWhere('nisn', 'like', '%'.$request->q.'%')->orWhere('nipd', 'like', '%'.$request->q.'%')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status_aktif', $request->status === 'aktif'))
            ->latest()->paginate(15);

        return view('admin.siswa.index', ['siswas' => $siswas, 'kelasOptions' => \App\Models\Kelas::orderBy('tingkat')->orderBy('nama_kelas')->pluck('nama_kelas', 'id')]);
    }

    public function create()
    {
        return view('admin.siswa.create', ['siswa' => new Siswa, 'kelas' => Kelas::orderBy('nama_kelas')->get()]);
    }

    public function store(StoreSiswaRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make('password');
        Siswa::create($data);

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.edit', ['siswa' => $siswa, 'kelas' => Kelas::orderBy('nama_kelas')->get()]);
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $siswa->update($request->validated());

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function resetPassword(Siswa $siswa)
    {
        $siswa->update(['password' => Hash::make('password')]);

        return back()->with('success', 'Password direset ke password default.');
    }
}
