<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEkstrakurikulerRequest;
use App\Models\Ekstrakurikuler;
use App\Models\Guru;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        return view('admin.ekstrakurikuler.index', [
            'items' => Ekstrakurikuler::with('pembina')->orderBy('nama_ekskul')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.ekstrakurikuler.create', [
            'item' => new Ekstrakurikuler(['is_active' => true]),
            'pembinaOptions' => $this->pembinaOptions(),
        ]);
    }

    public function store(StoreEkstrakurikulerRequest $request)
    {
        Ekstrakurikuler::create($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('admin.ekstrakurikuler.edit', [
            'item' => $ekstrakurikuler,
            'pembinaOptions' => $this->pembinaOptions(),
        ]);
    }

    public function update(StoreEkstrakurikulerRequest $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->delete();

        return back()->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    private function pembinaOptions()
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'pembina_ekskul'))
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->prepend('— Belum ditentukan —', '');
    }
}
