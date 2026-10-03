<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKonferensiKasusRequest;
use App\Models\KasusBk;
use App\Models\KonferensiKasus;
use App\Models\KonferensiKasusPeserta;
use App\Models\PanggilanOrtu;
use App\Services\Bk\LampiranBkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonferensiKasusController extends Controller
{
    public function index(Request $request)
    {
        $records = KonferensiKasus::with(['kasusBk.siswa.kelas', 'pesertas'])
            ->whereHas('kasusBk', fn ($q) => $q->where('konselor_id', Auth::id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->whereHas('kasusBk', fn ($qk) => $qk->where('judul', 'like', "%{$keyword}%")
                    ->orWhereHas('siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%")));
            })
            ->latest('tanggal_konferensi')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.konferensi.index', [
            'records' => $records,
            'kasusOptions' => $this->kasusOptions(),
        ]);
    }

    public function store(StoreKonferensiKasusRequest $request, LampiranBkService $lampiranService)
    {
        $data = $request->validated();
        $kasusBk = KasusBk::findOrFail($data['kasus_bk_id']);

        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Kasus ini bukan milik Anda.');

        $record = DB::transaction(function () use ($data, $kasusBk, $lampiranService, $request) {
            $record = KonferensiKasus::create([
                'kasus_bk_id' => $kasusBk->id,
                'tanggal_konferensi' => $data['tanggal_konferensi'],
                'tempat_pertemuan' => $data['tempat_pertemuan'] ?? null,
                'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
            ]);

            $this->syncPesertas($record, $data['peserta']);

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($kasusBk->id, $request->file('lampiran'), 'konferensi');
            }

            return $record;
        });

        return redirect()->route('guru.bk.konferensi.show', $record)->with('success', 'Konferensi kasus berhasil ditambahkan.');
    }

    public function show(KonferensiKasus $konferensi)
    {
        abort_unless($konferensi->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke konferensi ini.');

        $konferensi->load(['kasusBk.siswa.kelas', 'kasusBk.lampiran', 'kasusBk.konselor', 'pesertas']);

        return view('guru.bk.konferensi.show', [
            'konferensi' => $konferensi,
            'panggilanOrtus' => PanggilanOrtu::where('siswa_id', $konferensi->kasusBk->siswa_id)->orderByDesc('tanggal')->get(),
        ]);
    }

    public function update(StoreKonferensiKasusRequest $request, KonferensiKasus $konferensi, LampiranBkService $lampiranService)
    {
        abort_unless($konferensi->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke konferensi ini.');

        $data = $request->validated();

        DB::transaction(function () use ($data, $konferensi, $lampiranService, $request) {
            $konferensi->update([
                'tanggal_konferensi' => $data['tanggal_konferensi'],
                'tempat_pertemuan' => $data['tempat_pertemuan'] ?? null,
                'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
            ]);

            $this->syncPesertas($konferensi, $data['peserta']);

            $idBolehDihapus = $konferensi->kasusBk->lampiran->pluck('id');
            foreach ($data['lampiran_dihapus'] ?? [] as $id) {
                if ($idBolehDihapus->contains($id)) {
                    $lampiranService->deleteLampiran($id);
                }
            }

            if ($request->hasFile('lampiran')) {
                $lampiranService->storeLampirans($konferensi->kasus_bk_id, $request->file('lampiran'), 'konferensi');
            }
        });

        return redirect()->route('guru.bk.konferensi.show', $konferensi)->with('success', 'Konferensi kasus berhasil diperbarui.');
    }

    public function destroy(KonferensiKasus $konferensi)
    {
        abort_unless($konferensi->kasusBk->konselor_id === Auth::id(), 403, 'Anda tidak memiliki akses ke konferensi ini.');

        $konferensi->delete();

        return redirect()->route('guru.bk.konferensi.index')->with('success', 'Konferensi kasus berhasil dihapus.');
    }

    private function syncPesertas(KonferensiKasus $konferensi, array $peserta): void
    {
        $konferensi->pesertas()->delete();

        KonferensiKasusPeserta::insert(collect($peserta)->map(fn ($p) => [
            'konferensi_kasus_id' => $konferensi->id,
            'nama_peserta' => $p['nama_peserta'],
            'peran_peserta' => $p['peran_peserta'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    private function kasusOptions()
    {
        return KasusBk::with('siswa.kelas')
            ->where('konselor_id', Auth::id())
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal_mulai')
            ->get();
    }
}
