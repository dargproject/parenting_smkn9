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
    public function index() { return view('admin.siswa.index', ['siswas'=>Siswa::with('kelas')->latest()->paginate(15)]); }
    public function create() { return view('admin.siswa.create', ['siswa'=>new Siswa, 'kelas'=>Kelas::orderBy('nama_kelas')->get()]); }
    public function store(StoreSiswaRequest $request) { $data=$request->validated(); $data['password']=Hash::make('password'); Siswa::create($data); return redirect()->route('admin.siswa.index')->with('success','Siswa berhasil ditambahkan.'); }
    public function edit(Siswa $siswa) { return view('admin.siswa.edit', ['siswa'=>$siswa, 'kelas'=>Kelas::orderBy('nama_kelas')->get()]); }
    public function update(UpdateSiswaRequest $request, Siswa $siswa) { $siswa->update($request->validated()); return redirect()->route('admin.siswa.index')->with('success','Data siswa berhasil diperbarui.'); }
    public function destroy(Siswa $siswa) { $siswa->delete(); return back()->with('success','Data siswa berhasil dihapus.'); }
    public function resetPassword(Siswa $siswa) { $siswa->update(['password'=>Hash::make('password')]); return back()->with('success','Password direset ke password default.'); }
}
