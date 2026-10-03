<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengunduranDiriRequest;
use App\Models\Kelas;
use App\Models\PengunduranDiri;
use App\Models\Siswa;
use App\Services\Bk\LampiranPengunduranDiriService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store(StorePengunduranDiriRequest $request, LampiranPengunduranDiriService $lampiranService)
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($data, $lampiranService, $request) {
            $record = PengunduranDiri::create([
                'siswa_id' => $data['siswa_id'],
                'nama_ortu_wali' => $data['nama_ortu_wali'],
                'alamat_ortu_wali' => $data['alamat_ortu_wali'],
                'alasan_pengunduran' => $data['alasan_pengunduran'],
                'tanggal_pengunduran' => $data['tanggal_pengunduran'],
            ]);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($record->id, $request->file('lampiran'));
            }

            return $record;
        });

        return redirect()->route('guru.bk.pengunduran-diri.show', $record)->with('success', 'Data pengunduran diri berhasil ditambahkan.');
    }

    public function show(PengunduranDiri $pengunduranDiri)
    {
        $pengunduranDiri->load(['siswa.kelas', 'siswa.profilSiswa', 'siswa.dataKeluarga', 'lampirans']);

        return view('guru.bk.pengunduran-diri.show', ['pengunduranDiri' => $pengunduranDiri]);
    }

    public function update(StorePengunduranDiriRequest $request, PengunduranDiri $pengunduranDiri, LampiranPengunduranDiriService $lampiranService)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $pengunduranDiri, $lampiranService, $request) {
            $pengunduranDiri->update([
                'siswa_id' => $data['siswa_id'],
                'nama_ortu_wali' => $data['nama_ortu_wali'],
                'alamat_ortu_wali' => $data['alamat_ortu_wali'],
                'alasan_pengunduran' => $data['alasan_pengunduran'],
                'tanggal_pengunduran' => $data['tanggal_pengunduran'],
            ]);

            $idBolehDihapus = $pengunduranDiri->lampirans->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($pengunduranDiri->id, $request->file('lampiran'));
            }
        });

        return redirect()->route('guru.bk.pengunduran-diri.show', $pengunduranDiri)->with('success', 'Data pengunduran diri berhasil diperbarui.');
    }

    public function destroy(PengunduranDiri $pengunduranDiri, LampiranPengunduranDiriService $lampiranService)
    {
        foreach ($pengunduranDiri->lampirans as $lampiran) {
            $lampiranService->deleteLampiran($lampiran->id);
        }
        $pengunduranDiri->delete();

        return redirect()->route('guru.bk.pengunduran-diri.index')->with('success', 'Data pengunduran diri berhasil dihapus.');
    }
}
