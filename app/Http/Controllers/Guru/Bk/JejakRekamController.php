<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\AsesmenBk;
use App\Models\KasusBk;
use App\Models\Pelanggaran;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\PenilaianService;

class JejakRekamController extends Controller
{
    public function show(Siswa $siswa, PenilaianService $penilaian)
    {
        $siswa->load('kelas');

        $poinPelanggaran = (int) Pelanggaran::where('siswa_id', $siswa->id)->sum('poin');

        $presensi = Presensi::where('siswa_id', $siswa->id)->get();
        $rekap = $penilaian->rekapHarian($presensi);
        $persenKehadiran = $rekap['total_hari'] > 0 ? round($rekap['penuh'] / $rekap['total_hari'] * 100, 1) : null;

        $asesmenTerakhir = AsesmenBk::where('siswa_id', $siswa->id)->latest('updated_at')->first();

        $kasusBks = KasusBk::where('siswa_id', $siswa->id)->latest('tanggal_mulai')->get();
        $pelanggarans = Pelanggaran::where('siswa_id', $siswa->id)->latest('tanggal')->get();

        $timeline = $kasusBks->map(fn ($k) => [
            'tanggal' => optional($k->tanggal_mulai)->toDateString() ?? $k->created_at->toDateString(),
            'jenis' => 'Kasus BK',
            'judul' => $k->judul,
            'keterangan' => $k->deskripsi,
            'status' => $k->status,
        ])->merge($pelanggarans->map(fn ($p) => [
            'tanggal' => $p->tanggal,
            'jenis' => 'Pelanggaran',
            'judul' => $p->judul,
            'keterangan' => $p->deskripsi.($p->poin ? " ({$p->poin} poin)" : ''),
            'status' => null,
        ]))->sortByDesc('tanggal')->values();

        return response()->json([
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
            ],
            'poin_pelanggaran' => $poinPelanggaran,
            'persen_kehadiran' => $persenKehadiran,
            'catatan_terakhir' => $asesmenTerakhir?->catatan,
            'jumlah_kasus' => $kasusBks->count(),
            'timeline' => $timeline,
        ]);
    }
}
