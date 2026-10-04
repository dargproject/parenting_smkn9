<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerSiswa;
use App\Models\NilaiEkstrakurikuler;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiEkstrakurikulerController extends Controller
{
    public function index()
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();

        $ekstrakurikulers = Ekstrakurikuler::where('guru_id', Auth::id())
            ->orderBy('nama_ekskul')
            ->get()
            ->map(function ($ekskul) use ($tahunAjaranAktif) {
                $roster = $tahunAjaranAktif
                    ? EkstrakurikulerSiswa::with('siswa.kelas')
                        ->where('ekstrakurikuler_id', $ekskul->id)
                        ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                        ->get()
                    : collect();

                $nilaiMap = $tahunAjaranAktif
                    ? NilaiEkstrakurikuler::where('ekstrakurikuler_id', $ekskul->id)
                        ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                        ->get()
                        ->keyBy('siswa_id')
                    : collect();

                $ekskul->roster = $roster;
                $ekskul->nilaiMap = $nilaiMap;

                return $ekskul;
            });

        return view('guru.ekskul.index', [
            'ekstrakurikulers' => $ekstrakurikulers,
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'siswas' => Siswa::with('kelas')->where('status_aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function storeSiswa(Request $request)
    {
        $data = $request->validate([
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $ekskul = Ekstrakurikuler::where('guru_id', Auth::id())->findOrFail($data['ekstrakurikuler_id']);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->firstOrFail();

        $sudahAda = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)
            ->where('siswa_id', $data['siswa_id'])
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Siswa ini sudah terdaftar di ekstrakurikuler ini.');
        }

        EkstrakurikulerSiswa::create([
            'ekstrakurikuler_id' => $ekskul->id,
            'siswa_id' => $data['siswa_id'],
            'tahun_ajaran_id' => $tahunAjaranAktif->id,
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan ke ekstrakurikuler.');
    }

    public function destroySiswa(EkstrakurikulerSiswa $ekstrakurikulerSiswa)
    {
        abort_unless($ekstrakurikulerSiswa->ekstrakurikuler->guru_id === Auth::id(), 403, 'Anda tidak memiliki akses ke ekstrakurikuler ini.');

        $ekstrakurikulerSiswa->delete();

        return back()->with('success', 'Siswa berhasil dikeluarkan dari ekstrakurikuler.');
    }

    public function storeNilai(Request $request)
    {
        $data = $request->validate([
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
            'siswa_id' => 'required|exists:siswas,id',
            'nilai' => 'required|in:A,B,C',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $ekskul = Ekstrakurikuler::where('guru_id', Auth::id())->findOrFail($data['ekstrakurikuler_id']);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->firstOrFail();

        NilaiEkstrakurikuler::updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'ekstrakurikuler_id' => $ekskul->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id],
            ['nilai' => $data['nilai'], 'catatan' => $data['catatan'] ?? null, 'guru_id' => Auth::id()]
        );

        return back()->with('success', 'Nilai ekstrakurikuler berhasil disimpan.');
    }
}
