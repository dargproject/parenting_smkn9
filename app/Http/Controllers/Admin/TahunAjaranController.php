<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTahunAjaranRequest;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        return view('admin.tahun-ajaran.index', [
            'tahunAjarans' => TahunAjaran::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create', ['tahunAjaran' => new TahunAjaran]);
    }

    public function store(StoreTahunAjaranRequest $request)
    {
        $tahunAjaran = TahunAjaran::create($request->validated());
        if ($request->boolean('is_active')) {
            $this->activate($tahunAjaran);
        }

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(StoreTahunAjaranRequest $request, TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->update($request->validated());
        if ($request->boolean('is_active')) {
            $this->activate($tahunAjaran);
        }

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tahun ajaran aktif tidak dapat dihapus.');
        }

        $tahunAjaran->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(TahunAjaran $tahunAjaran)
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->update(['is_active' => false]);
            $tahunAjaran->update(['is_active' => true]);
        });

        return back()->with('success', 'Tahun ajaran aktif berhasil diubah.');
    }
}
