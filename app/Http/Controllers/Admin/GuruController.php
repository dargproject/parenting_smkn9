<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Http\Requests\UpdateGuruRequest;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $gurus = Guru::with('roles')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->q.'%')->orWhere('nip', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $request->role)))
            ->latest()->paginate(15);

        return view('admin.guru.index', ['gurus' => $gurus, 'roleOptions' => Role::orderBy('name')->pluck('name')->mapWithKeys(fn ($n) => [$n => str_replace('_', ' ', $n)])]);
    }

    public function create()
    {
        return view('admin.guru.create', ['guru' => new Guru, 'roles' => Role::orderBy('name')->get(), 'kelasOptions' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get()]);
    }

    public function store(StoreGuruRequest $request)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        $kelasBk = $data['kelas_bk'] ?? [];
        unset($data['roles'], $data['kelas_bk']);
        $data['password'] = Hash::make('password');
        $guru = Guru::create($data);
        $guru->roles()->sync($roles);
        $guru->kelasBk()->sync($kelasBk);

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', [
            'guru' => $guru->load(['roles', 'kelasBk']),
            'roles' => Role::orderBy('name')->get(),
            'kelasOptions' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function update(UpdateGuruRequest $request, Guru $guru)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        $kelasBk = $data['kelas_bk'] ?? [];
        unset($data['roles'], $data['kelas_bk']);
        $guru->update($data);
        $guru->roles()->sync($roles);
        $guru->kelasBk()->sync($kelasBk);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->roles()->detach();
        $guru->delete();

        return back()->with('success', 'Data guru berhasil dihapus.');
    }

    public function resetPassword(Guru $guru)
    {
        $guru->update(['password' => Hash::make('password')]);

        return back()->with('success', 'Password direset ke password default.');
    }
}
