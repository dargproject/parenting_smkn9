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
            'items' => Ekstrakurikuler::with('pembinas')->orderBy('nama_ekskul')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.ekstrakurikuler.create', [
            'item' => new Ekstrakurikuler(['is_active' => true]),
            'pembinaOptions' => $this->pembinaOptions(),
            'selectedPembinaIds' => old('pembina_ids', []),
        ]);
    }

    public function store(StoreEkstrakurikulerRequest $request)
    {
        $data = $request->validated();

        $ekstrakurikuler = Ekstrakurikuler::create([
            'nama_ekskul' => $data['nama_ekskul'],
            'is_active' => $request->boolean('is_active'),
        ]);
        $ekstrakurikuler->pembinas()->sync($data['pembina_ids'] ?? []);

        return redirect()->route('admin.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('admin.ekstrakurikuler.edit', [
            'item' => $ekstrakurikuler,
            'pembinaOptions' => $this->pembinaOptions(),
            'selectedPembinaIds' => old('pembina_ids', $ekstrakurikuler->pembinas->pluck('id')->all()),
        ]);
    }

    public function update(StoreEkstrakurikulerRequest $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $request->validated();

        $ekstrakurikuler->update([
            'nama_ekskul' => $data['nama_ekskul'],
            'is_active' => $request->boolean('is_active'),
        ]);
        $ekstrakurikuler->pembinas()->sync($data['pembina_ids'] ?? []);

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
            ->pluck('nama', 'id');
    }
}
