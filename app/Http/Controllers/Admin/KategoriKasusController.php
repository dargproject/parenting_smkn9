<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriKasusRequest;
use App\Models\KategoriKasus;

class KategoriKasusController extends Controller
{
    public function index()
    {
        return view('admin.kategori-kasus.index', ['items' => KategoriKasus::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.kategori-kasus.create', ['item' => new KategoriKasus]);
    }

    public function store(StoreKategoriKasusRequest $request)
    {
        KategoriKasus::create($request->validated());

        return redirect()->route('admin.kategori-kasus.index')->with('success', 'Kategori kasus berhasil ditambahkan.');
    }

    public function edit(KategoriKasus $kategoriKasus)
    {
        return view('admin.kategori-kasus.edit', ['item' => $kategoriKasus]);
    }

    public function update(StoreKategoriKasusRequest $request, KategoriKasus $kategoriKasus)
    {
        $kategoriKasus->update($request->validated());

        return redirect()->route('admin.kategori-kasus.index')->with('success', 'Kategori kasus berhasil diperbarui.');
    }

    public function destroy(KategoriKasus $kategoriKasus)
    {
        $kategoriKasus->delete();

        return back()->with('success', 'Kategori kasus berhasil dihapus.');
    }
}
