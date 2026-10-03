<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengunduranDiriRequest;
use App\Models\Kelas;
use App\Models\PengunduranDiri;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PengunduranDiriController extends Controller
{
    public function index(Request $request)
    {
        $records = PengunduranDiri::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qs) => $qs->where('jurusan', $request->jurusan)))
            ->latest('tanggal_pengunduran')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.pengunduran-diri.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StorePengunduranDiriRequest $request)
    {
        $record = PengunduranDiri::create($request->validated());

        return redirect()->route('guru.bk.pengunduran-diri.show', $record)->with('success', 'Data pengunduran diri berhasil ditambahkan.');
    }

    public function show(PengunduranDiri $pengunduranDiri)
    {
        $pengunduranDiri->load(['siswa.kelas', 'siswa.profilSiswa', 'siswa.dataKeluarga']);

        return view('guru.bk.pengunduran-diri.show', ['pengunduranDiri' => $pengunduranDiri]);
    }

    public function update(StorePengunduranDiriRequest $request, PengunduranDiri $pengunduranDiri)
    {
        $pengunduranDiri->update($request->validated());

        return redirect()->route('guru.bk.pengunduran-diri.show', $pengunduranDiri)->with('success', 'Data pengunduran diri berhasil diperbarui.');
    }

    public function destroy(PengunduranDiri $pengunduranDiri)
    {
        $pengunduranDiri->delete();

        return redirect()->route('guru.bk.pengunduran-diri.index')->with('success', 'Data pengunduran diri berhasil dihapus.');
    }
}
