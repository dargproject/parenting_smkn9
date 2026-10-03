<?php

namespace App\Http\Controllers;

use App\Models\KasusBk;
use App\Models\KategoriPengumuman;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PanggilanOrtu;
use App\Models\Pelanggaran;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KepsekController extends Controller
{
    private ?Collection $nilaiRows = null;

    private function nilaiRows(): Collection
    {
        return $this->nilaiRows ??= app(PenilaianService::class)->nilaiAkhirRows(TahunAjaran::where('is_active', true)->value('id'));
    }

    private function kkm(): int
    {
        return app(PenilaianService::class)->kktpThreshold();
    }

    public function index(Request $request)
    {
        return view('kepsek.dashboard', [
            'stats' => $this->dashboardStats(),
            'trendKehadiran' => $this->trendKehadiran(),
            'distribusiPelanggaran' => $this->distribusiPelanggaran(),
            'jurusanList' => Kelas::query()->select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
            'jurusanFilter' => $request->query('jurusan', 'all'),
            'akademikPerKelas' => $this->akademikPerKelas($request->query('jurusan', 'all')),
            'kesiswaanSummary' => $this->kesiswaanSummary(),
            'trendPelanggaranMingguan' => $this->trendPelanggaranMingguan(),
            'leaderboardPelanggaran' => $this->leaderboardPelanggaran(),
            'laporanRecap' => $this->laporanRecap(),
            'kategoriPengumumanList' => KategoriPengumuman::orderBy('nama')->pluck('nama'),
            'pengumumanTerbaru' => Pengumuman::with('pembuat')->latest()->limit(5)->get(),
        ]);
    }

    public function broadcastPengumuman(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        if (Pengumuman::where($data)->exists()) {
            return back()->withInput()->with('error', 'Pengumuman dengan judul, kategori, dan isi yang sama sudah pernah disiarkan.');
        }

        Pengumuman::create($data + ['pembuat_id' => Auth::id()]);

        return redirect()->route('kepsek.dashboard')->with('success', 'Pengumuman berhasil disiarkan.');
    }

    public function exportLaporan(Request $request, string $type)
    {
        $siswaMap = Siswa::with('kelas')->get()->keyBy('id');
        $mapelMap = MataPelajaran::pluck('nama_mapel', 'id');

        return match ($type) {
            'kehadiran' => $this->streamCsv('rekap-kehadiran', ['NIS', 'Nama Siswa', 'Kelas', 'Tanggal', 'Status', 'Keterangan'],
                Presensi::with(['siswa.kelas'])->orderBy('tanggal', 'desc')->get()->map(fn ($p) => [
                    $p->siswa->nis ?? '-',
                    $p->siswa->nama ?? '-',
                    $p->siswa->kelas->nama_kelas ?? '-',
                    $p->tanggal,
                    $p->status,
                    $p->keterangan ?? '-',
                ])),
            'legger' => $this->streamCsv('legger-nilai', ['NIS', 'Nama Siswa', 'Kelas', 'Mata Pelajaran', 'Nilai Akhir', 'Status'],
                $this->nilaiRows()->map(function ($n) use ($siswaMap, $mapelMap) {
                    $siswa = $siswaMap[$n->siswa_id] ?? null;

                    return [
                        $siswa->nis ?? '-',
                        $siswa->nama ?? '-',
                        $siswa->kelas->nama_kelas ?? '-',
                        $mapelMap[$n->mata_pelajaran_id] ?? '-',
                        $n->nilai_akhir,
                        $n->nilai_akhir >= $this->kkm() ? 'Tuntas' : 'Perlu Remedial',
                    ];
                })),
            'bk' => $this->streamCsv('kasus-bk', ['Nama Siswa', 'Kelas', 'Judul Kasus', 'Kategori', 'Status', 'Konselor'],
                KasusBk::with(['siswa.kelas', 'konselor'])->orderBy('created_at', 'desc')->get()->map(fn ($k) => [
                    $k->siswa->nama ?? '-',
                    $k->siswa->kelas->nama_kelas ?? '-',
                    $k->judul,
                    $k->kategori,
                    $k->status,
                    $k->konselor->nama ?? '-',
                ])),
            default => abort(404),
        };
    }

    private function streamCsv(string $filename, array $header, $rows)
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename.'-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function dashboardStats(): array
    {
        $totalSiswa = Siswa::where('status_aktif', true)->count();
        $totalRombel = Kelas::count();

        $tanggalPresensiTerakhir = Presensi::max('tanggal');
        $presensiHariIni = $tanggalPresensiTerakhir
            ? Presensi::whereDate('tanggal', $tanggalPresensiTerakhir)
            : Presensi::whereRaw('1 = 0');
        $totalPresensiHariIni = (clone $presensiHariIni)->count();
        $hadirHariIni = (clone $presensiHariIni)->where('status', 'H')->count();
        $persenKehadiran = $totalPresensiHariIni > 0 ? round($hadirHariIni / $totalPresensiHariIni * 100, 1) : null;

        $totalNilai = $this->nilaiRows()->count();
        $tuntasNilai = $this->nilaiRows()->where('nilai_akhir', '>=', $this->kkm())->count();
        $persenTuntas = $totalNilai > 0 ? round($tuntasNilai / $totalNilai * 100, 1) : null;

        $kasusAktif = KasusBk::where('status', '!=', 'selesai')->count();
        $kasusBerat = KasusBk::where('status', '!=', 'selesai')->where('kategori', 'Mendesak')->count();

        return [
            'total_siswa' => $totalSiswa,
            'total_rombel' => $totalRombel,
            'persen_kehadiran' => $persenKehadiran,
            'tanggal_kehadiran' => $tanggalPresensiTerakhir,
            'persen_tuntas' => $persenTuntas,
            'tuntas_count' => $tuntasNilai,
            'nilai_count' => $totalNilai,
            'kasus_aktif' => $kasusAktif,
            'kasus_berat' => $kasusBerat,
        ];
    }

    private function trendKehadiran()
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $days = collect(range(0, 6))->map(fn ($i) => $startOfWeek->copy()->addDays($i));

        $rekap = Presensi::selectRaw("tanggal, COUNT(*) as total, SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir")
            ->whereBetween('tanggal', [$startOfWeek->toDateString(), $startOfWeek->copy()->addDays(6)->toDateString()])
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        return $days->map(function ($day) use ($rekap) {
            $row = $rekap->get($day->toDateString());

            return [
                'tanggal' => $day->toDateString(),
                'hari' => $day->locale('id')->translatedFormat('l'),
                'persen' => $row && $row->total > 0 ? round($row->hadir / $row->total * 100, 1) : null,
            ];
        });
    }

    private function distribusiPelanggaran()
    {
        return Pelanggaran::selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();
    }

    private function akademikPerKelas(string $jurusan)
    {
        return Kelas::withCount('siswas')
            ->with('waliKelas')
            ->when($jurusan !== 'all', fn ($q) => $q->where('jurusan', $jurusan))
            ->orderBy('nama_kelas')
            ->get()
            ->map(function ($kelas) {
                $siswaIds = $kelas->siswas()->pluck('id');
                $nilaiKelas = $this->nilaiRows()->whereIn('siswa_id', $siswaIds);
                $total = $nilaiKelas->count();
                $tuntas = $nilaiKelas->where('nilai_akhir', '>=', $this->kkm())->count();

                return [
                    'kelas' => $kelas,
                    'rata_rata' => $total > 0 ? round($nilaiKelas->avg('nilai_akhir'), 1) : null,
                    'total_nilai' => $total,
                    'persen_tuntas' => $total > 0 ? round($tuntas / $total * 100, 1) : null,
                ];
            });
    }

    private function kesiswaanSummary(): array
    {
        return [
            'kasus_bk_aktif' => KasusBk::whereIn('status', ['antrean', 'proses'])->count(),
            'kasus_bk_mendesak' => KasusBk::whereIn('status', ['antrean', 'proses'])->where('kategori', 'Mendesak')->count(),
            'panggilan_ortu_pending' => PanggilanOrtu::where('status', 'Menunggu Konfirmasi')->count(),
            'pelanggaran_bulan_ini' => Pelanggaran::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
        ];
    }

    private function trendPelanggaranMingguan()
    {
        return Pelanggaran::selectRaw("strftime('%Y-%W', tanggal) as minggu, COUNT(*) as total")
            ->groupBy('minggu')
            ->orderBy('minggu')
            ->limit(8)
            ->get();
    }

    private function leaderboardPelanggaran()
    {
        return Pelanggaran::selectRaw('siswa_id, SUM(poin) as total_poin, COUNT(*) as jumlah_kasus')
            ->with('siswa.kelas')
            ->groupBy('siswa_id')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get();
    }

    private function laporanRecap(): array
    {
        $totalPresensi = Presensi::count();
        $rekapStatus = Presensi::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $totalKasusBk = KasusBk::count();
        $rekapKasusBk = KasusBk::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $rekapPrioritasKasusBk = KasusBk::where('status', '!=', 'selesai')->selectRaw('prioritas, COUNT(*) as total')->groupBy('prioritas')->pluck('total', 'prioritas');

        return [
            'total_presensi' => $totalPresensi,
            'rekap_status_presensi' => $rekapStatus,
            'total_kasus_bk' => $totalKasusBk,
            'rekap_status_kasus_bk' => $rekapKasusBk,
            'rekap_prioritas_kasus_bk' => $rekapPrioritasKasusBk,
        ];
    }
}
