<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlihTanganKasusRequest;
use App\Models\AlihTanganKasus;
use App\Models\Guru;
use App\Models\KasusBk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlihTanganKasusController extends Controller
{
    public function index(Request $request)
    {
        $records = AlihTanganKasus::with(['kasusBk.siswa.kelas', 'konselorAsal', 'konselorTujuan'])
            ->where(fn ($q) => $q->where('konselor_asal_id', Auth::id())->orWhere('konselor_tujuan_id', Auth::id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;
                $q->whereHas('kasusBk', fn ($qk) => $qk->where('judul', 'like', "%{$keyword}%")
                    ->orWhereHas('siswa', fn ($qs) => $qs->where('nama', 'like', "%{$keyword}%")));
            })
            ->latest('tanggal_alih')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.alih-tangan.index', [
            'records' => $records,
            'kasusOptions' => $this->kasusOptions(),
            'guruBkOptions' => $this->guruBkOptions(),
        ]);
    }

    public function store(StoreAlihTanganKasusRequest $request)
    {
        $data = $request->validated();
        $kasusBk = KasusBk::findOrFail($data['kasus_bk_id']);

        abort_unless($kasusBk->konselor_id === Auth::id(), 403, 'Kasus ini bukan milik Anda, tidak bisa dialihkan.');

        $record = DB::transaction(function () use ($data, $kasusBk) {
            $record = AlihTanganKasus::create([
                'kasus_bk_id' => $kasusBk->id,
                'konselor_asal_id' => Auth::id(),
                'konselor_tujuan_id' => $data['konselor_tujuan_id'],
                'tanggal_alih' => $data['tanggal_alih'],
                'alasan_alih' => $data['alasan_alih'] ?? null,
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
            ]);

            $kasusBk->update(['konselor_id' => $data['konselor_tujuan_id']]);

            return $record;
        });

        return redirect()->route('guru.bk.alih-tangan.show', $record)->with('success', 'Alih tangan kasus berhasil disimpan. Kasus kini ditangani oleh guru BK penerima.');
    }

    public function show(AlihTanganKasus $alihTangan)
    {
        $this->authorizeAkses($alihTangan);

        $alihTangan->load(['kasusBk.siswa.kelas', 'kasusBk.konselor', 'konselorAsal', 'konselorTujuan']);

        return view('guru.bk.alih-tangan.show', [
            'alihTangan' => $alihTangan,
            'guruBkOptions' => Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))
                ->where('id', '!=', $alihTangan->konselor_asal_id)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function update(StoreAlihTanganKasusRequest $request, AlihTanganKasus $alihTangan)
    {
        $this->authorizeAkses($alihTangan);

        $data = $request->validated();

        DB::transaction(function () use ($data, $alihTangan) {
            $tujuanBerubah = (int) $data['konselor_tujuan_id'] !== $alihTangan->konselor_tujuan_id;

            $alihTangan->update([
                'tanggal_alih' => $data['tanggal_alih'],
                'konselor_tujuan_id' => $data['konselor_tujuan_id'],
                'alasan_alih' => $data['alasan_alih'] ?? null,
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
            ]);

            if ($tujuanBerubah) {
                $alihTangan->kasusBk->update(['konselor_id' => $data['konselor_tujuan_id']]);
            }
        });

        return redirect()->route('guru.bk.alih-tangan.show', $alihTangan)->with('success', 'Alih tangan kasus berhasil diperbarui.');
    }

    public function destroy(AlihTanganKasus $alihTangan)
    {
        $this->authorizeAkses($alihTangan);

        // Menghapus catatan ini TIDAK mengembalikan kasus ke konselor asal -- hanya menghapus log
        // alih tangannya, konsisten dengan aplikasi BK sumber. Konselor saat ini tetap sebagai pemegang kasus.
        $alihTangan->delete();

        return redirect()->route('guru.bk.alih-tangan.index')->with('success', 'Catatan alih tangan kasus berhasil dihapus.');
    }

    private function authorizeAkses(AlihTanganKasus $alihTangan): void
    {
        abort_unless(
            in_array(Auth::id(), [$alihTangan->konselor_asal_id, $alihTangan->konselor_tujuan_id], true),
            403,
            'Anda tidak memiliki akses ke catatan alih tangan ini.'
        );
    }

    private function kasusOptions()
    {
        return KasusBk::with('siswa.kelas')
            ->where('konselor_id', Auth::id())
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    private function guruBkOptions()
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->where('id', '!=', Auth::id())
            ->orderBy('nama')
            ->get();
    }
}
