<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMasterPelanggaranRequest;
use App\Models\JenisPelanggaran;
use App\Models\MasterPelanggaran;
use App\Models\Pasal;

class MasterPelanggaranController extends Controller
{
    public function index()
    {
        return view('admin.master-pelanggaran.index', [
            'items' => MasterPelanggaran::with(['pasal', 'jenisPelanggaran'])->orderBy('pasal_id')->orderBy('nama_pelanggaran')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.master-pelanggaran.create', [
            'item' => new MasterPelanggaran(['is_active' => true]),
            'pasals' => Pasal::orderBy('kode')->get(),
            'jenisList' => JenisPelanggaran::orderBy('poin')->get(),
        ]);
    }

    public function store(StoreMasterPelanggaranRequest $request)
    {
        MasterPelanggaran::create($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.master-pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit(MasterPelanggaran $masterPelanggaran)
    {
        return view('admin.master-pelanggaran.edit', [
            'item' => $masterPelanggaran,
            'pasals' => Pasal::orderBy('kode')->get(),
            'jenisList' => JenisPelanggaran::orderBy('poin')->get(),
        ]);
    }

    public function update(StoreMasterPelanggaranRequest $request, MasterPelanggaran $masterPelanggaran)
    {
        $masterPelanggaran->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.master-pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy(MasterPelanggaran $masterPelanggaran)
    {
        $masterPelanggaran->delete();

        return back()->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }
}
