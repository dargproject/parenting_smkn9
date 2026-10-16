<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekapPresensiController extends Controller
{
    private const NAMA_HARI = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    public function index(Request $request, PenilaianService $penilaian)
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get()->sortBy('nama_kelas', SORT_NATURAL)->values();
        $kelasBinaanIds = Auth::user()->kelasBk->pluck('id');

        $kelasId = $request->query('kelas_id') ?: $kelasBinaanIds->first();
        $tanggal = $request->query('tanggal') ?: now()->toDateString();
        $tahunAjaranAktifId = TahunAjaran::where('is_active', true)->value('id');

        $kelasTerpilih = $kelasId ? $kelasList->firstWhere('id', (int) $kelasId) : null;
        $siswaKelas = collect();
        $rekapSemester = collect();
        $rekapHarian = collect();
        $jadwalHariDipilih = collect();
        $namaHariDipilih = self::NAMA_HARI[Carbon::parse($tanggal)->dayOfWeek];

        if ($kelasTerpilih) {
            $siswaKelas = Siswa::where('kelas_id', $kelasTerpilih->id)->where('status_aktif', true)->orderBy('nama')->get();
            $jadwalKelas = JadwalPelajaran::with('mataPelajaran')
                ->where('kelas_id', $kelasTerpilih->id)
                ->when($tahunAjaranAktifId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktifId))
                ->get();

            $presensiSemester = Presensi::whereIn('jadwal_pelajaran_id', $jadwalKelas->pluck('id'))->get();

            $rekapSemester = $siswaKelas->mapWithKeys(function ($s) use ($presensiSemester) {
                $milik = $presensiSemester->where('siswa_id', $s->id);
                $jumlah = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
                foreach ($milik as $p) {
                    $jumlah[$p->status]++;
                }
                $total = array_sum($jumlah);

                return [$s->id => ['jumlah' => $jumlah, 'total' => $total, 'persen' => $total > 0 ? round($jumlah['H'] / $total * 100, 1) : null]];
            });

            $rekapHarian = $siswaKelas->mapWithKeys(fn ($s) => [$s->id => $penilaian->rekapHarian($presensiSemester->where('siswa_id', $s->id))]);

            $jadwalHariDipilih = $jadwalKelas->where('hari', $namaHariDipilih)->sortBy('jam_mulai')->values();
            $presensiTanggalDipilih = $presensiSemester->where('tanggal', $tanggal);
        }

        return view('guru.bk.rekap-presensi.index', [
            'kelasList' => $kelasList,
            'kelasBinaanIds' => $kelasBinaanIds,
            'kelasTerpilih' => $kelasTerpilih,
            'tanggal' => $tanggal,
            'namaHariDipilih' => $namaHariDipilih,
            'siswaKelas' => $siswaKelas,
            'rekapSemester' => $rekapSemester,
            'rekapHarian' => $rekapHarian,
            'jadwalHariDipilih' => $jadwalHariDipilih,
            'presensiTanggalDipilih' => $presensiTanggalDipilih ?? collect(),
        ]);
    }
}
