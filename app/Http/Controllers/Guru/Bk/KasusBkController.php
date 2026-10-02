<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKasusBkRequest;
use App\Models\KasusBk;
use App\Models\KategoriKasus;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\Bk\LampiranBkService;
use App\Services\Bk\WordExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class KasusBkController extends Controller
{
    public function index(Request $request)
    {
        $kasusBks = KasusBk::with(['siswa.kelas', 'kategoriKasus'])
            ->where('konselor_id', Auth::id())
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->where(fn ($w) => $w->where('judul', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('prioritas'), fn ($q) => $q->where('prioritas', $request->prioritas))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qs) => $qs->where('jurusan', $request->jurusan)))
            ->when($request->filled('jenis_kelamin'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('jenis_kelamin', $request->jenis_kelamin)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.kasus.index', [
            'kasusBks' => $kasusBks,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kategoriKasusList' => KategoriKasus::orderBy('nama_kategori')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreKasusBkRequest $request, LampiranBkService $lampiranService)
    {
        $data = $request->validated();
        $kategori = ! empty($data['kategori_id']) ? KategoriKasus::find($data['kategori_id']) : null;
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        $kasusBk = DB::transaction(function () use ($data, $kategori, $tahunAjaranId, $lampiranService, $request) {
            $kasusBk = KasusBk::create([
                'siswa_id' => $data['siswa_id'],
                'tahun_ajaran_id' => $tahunAjaranId,
                'judul' => $data['judul'],
                'kategori' => $kategori?->nama_kategori ?? 'Normal',
                'kategori_id' => $data['kategori_id'] ?? null,
                'deskripsi' => $data['deskripsi'],
                'status' => 'antrean',
                'prioritas' => $data['prioritas'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'konselor_id' => Auth::id(),
            ]);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'kasus');
            }

            return $kasusBk;
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $kasusBk->id]);
        }

        return redirect()->route('guru.bk.kasus.show', $kasusBk)->with('success', 'Kasus BK berhasil ditambahkan.');
    }

    public function show(KasusBk $kasusBk)
    {
        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke kasus ini.');

        $kasusBk->load(['siswa.kelas', 'kategoriKasus', 'lampiran', 'konselor']);

        return view('guru.bk.kasus.show', [
            'kasusBk' => $kasusBk,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kategoriKasusList' => KategoriKasus::orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(StoreKasusBkRequest $request, KasusBk $kasusBk, LampiranBkService $lampiranService)
    {
        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke kasus ini.');

        $data = $request->validated();
        $kategori = ! empty($data['kategori_id']) ? KategoriKasus::find($data['kategori_id']) : null;

        DB::transaction(function () use ($data, $kategori, $kasusBk, $lampiranService, $request) {
            $kasusBk->update([
                'siswa_id' => $data['siswa_id'],
                'judul' => $data['judul'],
                'kategori' => $kategori?->nama_kategori ?? $kasusBk->kategori,
                'kategori_id' => $kategori?->id,
                'deskripsi' => $data['deskripsi'],
                'prioritas' => $data['prioritas'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
            ]);

            $idBolehDihapus = $kasusBk->lampiran->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'kasus');
            }
        });

        return redirect()->route('guru.bk.kasus.show', $kasusBk)->with('success', 'Kasus BK berhasil diperbarui.');
    }

    public function destroy(KasusBk $kasusBk, LampiranBkService $lampiranService)
    {
        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke kasus ini.');

        foreach ($kasusBk->lampiran as $lampiran) {
            $lampiranService->deleteLampiran($lampiran->id);
        }
        $kasusBk->delete();

        return redirect()->route('guru.bk.kasus.index')->with('success', 'Kasus BK berhasil dihapus.');
    }

    public function updateStatus(Request $request, KasusBk $kasusBk)
    {
        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke kasus ini.');

        $data = $request->validate(['status' => 'required|in:antrean,proses,selesai']);

        $kasusBk->update([
            'status' => $data['status'],
            'tanggal_selesai' => $data['status'] === 'selesai' ? ($kasusBk->tanggal_selesai ?? now()->toDateString()) : $kasusBk->tanggal_selesai,
        ]);

        return back()->with('success', 'Status kasus berhasil diperbarui.');
    }

    public function export(KasusBk $kasusBk, string $template, WordExportService $wordExportService)
    {
        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke kasus ini.');

        $kasusBk->load(['siswa.kelas', 'siswa.profilSiswa', 'kategoriKasus', 'konselor', 'tahunAjaran']);

        try {
            $tempPath = $wordExportService->generateDocument($kasusBk, $template);
        } catch (InvalidArgumentException $e) {
            abort(404, $e->getMessage());
        }

        $filename = "kasus-bk-{$kasusBk->id}-{$template}.docx";

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
