<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\KelasMataPelajaran;
use App\Models\TahunAjaran;
use App\Services\JadwalPelajaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalMengajarController extends Controller
{
    public function __construct(private JadwalPelajaranService $jadwalService) {}

    private function pastikanMapelMiliknya(int $kelasId, int $mapelId): void
    {
        $milik = KelasMataPelajaran::where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('guru_id', Auth::id())
            ->exists();

        abort_unless($milik, 403, 'Mata pelajaran ini belum ditetapkan untuk Anda di kelas tersebut oleh Waka Kurikulum.');
    }

    public function store(Request $request)
    {
        $data = $this->jadwalService->validasi($request);
        $this->pastikanMapelMiliknya((int) $data['kelas_id'], (int) $data['mata_pelajaran_id']);

        if ($pesan = $this->jadwalService->cariBentrok($data, Auth::id())) {
            return back()->withInput()->with('error', $pesan);
        }

        JadwalPelajaran::create($data + [
            'guru_id' => Auth::id(),
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal mengajar berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        abort_unless($jadwalPelajaran->guru_id === Auth::id(), 403, 'Anda tidak memiliki akses ke jadwal ini.');

        $data = $this->jadwalService->validasi($request);
        $this->pastikanMapelMiliknya((int) $data['kelas_id'], (int) $data['mata_pelajaran_id']);

        if ($pesan = $this->jadwalService->cariBentrok($data, Auth::id(), $jadwalPelajaran->id)) {
            return back()->withInput()->with('error', $pesan);
        }

        $jadwalPelajaran->update($data);

        return redirect()->route('guru.portal')->with('success', 'Jadwal mengajar berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        abort_unless($jadwalPelajaran->guru_id === Auth::id(), 403, 'Anda tidak memiliki akses ke jadwal ini.');

        $jadwalPelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Jadwal mengajar berhasil dihapus.');
    }
}
