<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriPengumumanRequest;
use App\Models\KategoriPengumuman;

class KategoriPengumumanController extends Controller
{
    public function index()
    {
        return view('admin.kategori-pengumuman.index', ['items' => KategoriPengumuman::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.kategori-pengumuman.create', ['item' => new KategoriPengumuman]);
    }

    public function store(StoreKategoriPengumumanRequest $request)
    {
        KategoriPengumuman::create($request->validated());

        return redirect()->route('admin.kategori-pengumuman.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(KategoriPengumuman $kategoriPengumuman)
    {
        return view('admin.kategori-pengumuman.edit', ['item' => $kategoriPengumuman]);
    }

    public function update(StoreKategoriPengumumanRequest $request, KategoriPengumuman $kategoriPengumuman)
    {
        $kategoriPengumuman->update($request->validated());

        return redirect()->route('admin.kategori-pengumuman.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriPengumuman $kategoriPengumuman)
    {
        $kategoriPengumuman->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
