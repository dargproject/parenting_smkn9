<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Http\Requests\UpdateGuruRequest;
use App\Models\Guru;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        return view('admin.guru.index', ['gurus' => Guru::with('roles')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.guru.create', ['guru' => new Guru, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(StoreGuruRequest $request)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        $data['password'] = Hash::make('password');
        $guru = Guru::create($data);
        $guru->roles()->sync($roles);

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', ['guru' => $guru->load('roles'), 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(UpdateGuruRequest $request, Guru $guru)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        $guru->update($data);
        $guru->roles()->sync($roles);

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
