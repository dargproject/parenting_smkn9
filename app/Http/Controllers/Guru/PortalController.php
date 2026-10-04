<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AsesmenBk;
use App\Models\CatatanAkademikSiswa;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\KasusBk;
use App\Models\KategoriKasus;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\LogPerubahanNilai;
use App\Models\MasterPelanggaran;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\PanggilanOrtu;
use App\Models\Pelanggaran;
use App\Models\PesanWaliKelas;
use App\Models\Presensi;
use App\Models\PrestasiNonAkademik;
use App\Models\RaporFinal;
use App\Models\RujukanBk;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PortalController extends Controller
{
    private const NAMA_HARI = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    public function index(PenilaianService $penilaian)
    {
        $guruId = Session::get('guru_id');
        if (! $guruId) {
            return redirect()->route('login');
        }

        $guru = Guru::with('roles')->find($guruId);
        if ($guru) {
            // Perubahan role oleh admin/kesiswaan langsung berlaku tanpa harus login ulang.
            Session::put('roles', $guru->roles->pluck('name')->all());
        }
        if (! $guru) {
            return redirect()->route('login');
        }

        // Ambil data master untuk ditampilkan di portal
        $siswas = Siswa::with('kelas')->get();
        $catatanAkademikMap = CatatanAkademikSiswa::where('tahun_ajaran_id', TahunAjaran::where('is_active', true)->value('id'))->get()->keyBy('siswa_id');
        $asesmenBkMap = AsesmenBk::where('tahun_ajaran_id', TahunAjaran::where('is_active', true)->value('id'))->get()->keyBy('siswa_id');
        // Data BK bersifat rahasia: tiap guru BK hanya melihat kasus miliknya sendiri.
        $kasusBks = KasusBk::with(['siswa', 'konselor'])->where('konselor_id', $guru->id)->get();
        $kategoriKasusList = KategoriKasus::orderBy('nama_kategori')->get();
        $pelanggarans = Pelanggaran::with(['siswa', 'pelapor'])->orderBy('tanggal', 'desc')->get();
        $panggilanOrtus = PanggilanOrtu::with(['siswa', 'pemanggil'])->orderBy('tanggal', 'desc')->get();
        $jadwalPelajarans = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru'])
            ->when(TahunAjaran::where('is_active', true)->value('id'), fn ($q, $ta) => $q->where('tahun_ajaran_id', $ta))
            ->get();
        $mataPelajarans = MataPelajaran::all();
        $kelasList = Kelas::all();

        // Data untuk menu Waka Kesiswaan
        $masterPelanggarans = MasterPelanggaran::with(['pasal', 'jenisPelanggaran'])->orderBy('pasal_id')->orderBy('nama_pelanggaran')->get();
        $masterPelanggaranAktif = $masterPelanggarans->where('is_active', true)->values();

        $rekapPoin = Siswa::with('kelas')
            ->withSum('pelanggarans as total_poin', 'poin')
            ->orderByDesc('total_poin')
            ->get();

        $absensiBermasalah = Siswa::with('kelas')
            ->withCount(['presensis as alpa_count' => fn ($q) => $q->where('status', 'A')])
            ->get()
            ->filter(fn ($s) => $s->alpa_count > 0)
            ->sortByDesc('alpa_count')
            ->values();

        // Data untuk Guru Wali: hanya kelas binaan guru yang login
        $kelasBinaan = $kelasList->where('guru_wali_id', $guru->id)->values();
        $siswaBinaan = $siswas->whereIn('kelas_id', $kelasBinaan->pluck('id'))->values();
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $tahunAjaranAktifId = $tahunAjaranAktif?->id;

        // Nilai akhir seluruh siswa (sumber tunggal: nilai_lms + nilai_sas), dipakai legger & dashboard guru wali.
        $nilaiAkhirRows = $penilaian->nilaiAkhirRows($tahunAjaranAktifId);
        $nilaiAkhirMap = $nilaiAkhirRows->keyBy(fn ($r) => $r->siswa_id.'-'.$r->mata_pelajaran_id);

        // Log audit perubahan nilai se-sekolah, dipakai panel audit di Validasi Legger (waka_kurikulum).
        $logPerubahanNilaiSemua = $tahunAjaranAktif
            ? LogPerubahanNilai::whereHas('nilaiLm', fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktifId))
                ->with(['nilaiLm.siswa.kelas', 'nilaiLm.tujuanPembelajaran.mataPelajaran', 'guru'])
                ->latest()
                ->limit(100)
                ->get()
            : collect();
        $nilaiBinaan = $nilaiAkhirRows->whereIn('siswa_id', $siswaBinaan->pluck('id'));
        $rerataBinaan = $nilaiBinaan->isNotEmpty() ? round($nilaiBinaan->avg('nilai_akhir'), 1) : null;
        $peringkatBinaan = $siswaBinaan
            ->map(fn ($s) => (object) ['nama' => $s->nama, 'rata' => $nilaiBinaan->where('siswa_id', $s->id)->avg('nilai_akhir')])
            ->filter(fn ($s) => $s->rata !== null)
            ->sortByDesc('rata')
            ->take(3)
            ->values();

        // Data untuk Guru Mapel: mapel yang diampu, TP semester berjalan, dan nilai yang sudah diinput
        $kelasMapelBinaan = KelasMataPelajaran::with(['kelas', 'mataPelajaran'])->where('guru_id', $guru->id)->get()->filter(fn ($km) => $km->kelas && $km->mataPelajaran)->values();
        $mapelBinaan = $mataPelajarans->whereIn('id', $kelasMapelBinaan->pluck('mata_pelajaran_id'))->values();
        $kelasMapelSemua = KelasMataPelajaran::with(['kelas', 'mataPelajaran', 'guru'])->get();
        $tujuanPembelajarans = $tahunAjaranAktif
            ? TujuanPembelajaran::whereIn('mata_pelajaran_id', $mapelBinaan->pluck('id'))
                ->where('tahun_ajaran_id', $tahunAjaranAktifId)
                ->orderBy('urutan')
                ->get()
            : collect();

        $nilaiLmBinaan = NilaiLm::whereIn('tujuan_pembelajaran_id', $tujuanPembelajarans->pluck('id'))
            ->with('logs.guru')
            ->get()
            ->keyBy(fn ($n) => $n->siswa_id.'-'.$n->tujuan_pembelajaran_id);
        $nilaiSasBinaan = $tahunAjaranAktif
            ? NilaiSas::whereIn('mata_pelajaran_id', $mapelBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()
                ->keyBy(fn ($n) => $n->siswa_id.'-'.$n->mata_pelajaran_id)
            : collect();
        $catatanKompetensiBinaan = $tahunAjaranAktif
            ? CatatanKompetensi::whereIn('mata_pelajaran_id', $mapelBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()
                ->keyBy(fn ($n) => $n->siswa_id.'-'.$n->mata_pelajaran_id)
            : collect();
        $nilaiPklUkkBinaan = $tahunAjaranAktif
            ? NilaiPklUkk::whereIn('mata_pelajaran_id', $mapelBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()
                ->keyBy(fn ($n) => $n->siswa_id.'-'.$n->mata_pelajaran_id.'-'.$n->jenis)
            : collect();

        // Data untuk Guru Wali: matrix nilai akhir, progres input, catatan, dan status rilis rapor
        $allNilaiLmKelasBinaan = $tahunAjaranAktif
            ? NilaiLm::whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->with('tujuanPembelajaran')->get()
            : collect();
        $allNilaiSasKelasBinaan = $tahunAjaranAktif
            ? NilaiSas::whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()
            : collect();

        $matrixPerKelas = $kelasBinaan->map(function ($kelas) use ($siswaBinaan, $kelasMapelSemua, $allNilaiLmKelasBinaan, $allNilaiSasKelasBinaan, $penilaian) {
            $siswaKelas = $siswaBinaan->where('kelas_id', $kelas->id)->values();
            $mapelKelas = $kelasMapelSemua
                ->where('kelas_id', $kelas->id)
                ->pluck('mataPelajaran')
                ->filter()
                ->unique('id')
                ->values();

            $mapelProgres = $mapelKelas->map(function ($mapel) use ($siswaKelas, $allNilaiSasKelasBinaan) {
                $siswaIds = $siswaKelas->pluck('id');
                $submitted = $allNilaiSasKelasBinaan
                    ->where('mata_pelajaran_id', $mapel->id)
                    ->whereIn('siswa_id', $siswaIds)
                    ->whereNotNull('nilai')
                    ->count();

                return ['mapel' => $mapel, 'submitted' => $submitted, 'total' => $siswaKelas->count()];
            });

            $rows = $siswaKelas->map(function ($siswa) use ($mapelKelas, $allNilaiLmKelasBinaan, $allNilaiSasKelasBinaan, $penilaian) {
                $nilaiPerMapel = $mapelKelas->mapWithKeys(function ($mapel) use ($siswa, $allNilaiLmKelasBinaan, $allNilaiSasKelasBinaan, $penilaian) {
                    $nilaiLmSiswaMapel = $allNilaiLmKelasBinaan
                        ->where('siswa_id', $siswa->id)
                        ->filter(fn ($n) => $n->tujuanPembelajaran && $n->tujuanPembelajaran->mata_pelajaran_id === $mapel->id);
                    $sas = $allNilaiSasKelasBinaan
                        ->where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->first();

                    $rataLm = $penilaian->rataLm($nilaiLmSiswaMapel);
                    $na = $penilaian->nilaiAkhir($rataLm, $sas?->nilai);

                    return [$mapel->id => ['na' => $na, 'status' => $penilaian->statusNilai($na)]];
                });

                return ['siswa' => $siswa, 'nilai' => $nilaiPerMapel];
            });

            return ['kelas' => $kelas, 'mapel' => $mapelProgres, 'rows' => $rows];
        });

        $kelasWaliList = $kelasList->where('wali_kelas_id', $guru->id)->sortBy('nama_kelas', SORT_NATURAL)->values();
        $siswaWali = $siswas->whereIn('kelas_id', $kelasWaliList->pluck('id'))->values();
        $catatanWaliKelasWali = $tahunAjaranAktif
            ? CatatanWaliKelas::whereIn('siswa_id', $siswaWali->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()->keyBy('siswa_id')
            : collect();
        $pesanWaliKelasWali = PesanWaliKelas::whereIn('siswa_id', $siswaWali->pluck('id'))->latest()->get()->groupBy('siswa_id');
        $prestasiNonAkademikWali = PrestasiNonAkademik::whereIn('siswa_id', $siswaWali->pluck('id'))->latest('tanggal')->get()->groupBy('siswa_id');
        $rujukanBkSaya = RujukanBk::with('siswa')->where('dirujuk_oleh', $guru->id)->latest()->limit(10)->get();
        $logPerubahanNilaiBinaan = $tahunAjaranAktif
            ? LogPerubahanNilai::whereHas('nilaiLm', fn ($q) => $q->whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId))
                ->with(['nilaiLm.siswa.kelas', 'nilaiLm.tujuanPembelajaran.mataPelajaran', 'guru'])
                ->latest()
                ->get()
            : collect();

        $raporFinalBinaan = $tahunAjaranAktif
            ? RaporFinal::whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()->keyBy('siswa_id')
            : collect();
        $siswaBinaanLengkap = $tahunAjaranAktif
            ? $siswaBinaan->mapWithKeys(fn ($s) => [$s->id => $penilaian->siswaLengkap($s, $tahunAjaranAktifId)])
            : collect();

        // Data untuk Wali Kelas: rekap kehadiran (Dashboard Kelas & Rekap Presensi Mapel), dari presensi asli.
        $jadwalKelasWali = $jadwalPelajarans->whereIn('kelas_id', $kelasWaliList->pluck('id'))->values();
        $presensiWali = Presensi::whereIn('jadwal_pelajaran_id', $jadwalKelasWali->pluck('id'))->get();
        $rekapPresensiWali = $siswaWali->mapWithKeys(function ($s) use ($presensiWali) {
            $milik = $presensiWali->where('siswa_id', $s->id);
            $jumlah = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
            foreach ($milik as $p) {
                $jumlah[$p->status]++;
            }
            $total = array_sum($jumlah);

            return [$s->id => ['jumlah' => $jumlah, 'total' => $total, 'persen' => $total > 0 ? round($jumlah['H'] / $total * 100, 1) : null]];
        });
        // Rekap harian: satu status per hari (bukan per mapel), agar siswa yang alpa di satu jadwal
        // tapi hadir di jadwal lain hari itu tidak "tertutupi" atau salah terbaca sebagai alpa penuh.
        $rekapHarianWali = $siswaWali->mapWithKeys(fn ($s) => [$s->id => $penilaian->rekapHarian($presensiWali->where('siswa_id', $s->id))]);

        // Peringatan alpa mingguan: siswa dengan hari Alpa Penuh >= ambang, dalam minggu kalender berjalan (Senin-Sabtu).
        $ambangAlpaMingguan = (int) setting('alpa_mingguan_threshold', 3);
        $awalMingguIni = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $akhirMingguIni = now()->startOfWeek(Carbon::MONDAY)->addDays(5)->toDateString();
        $siswaPeringatanMingguan = $siswaWali->map(function ($s) use ($presensiWali, $penilaian, $awalMingguIni, $akhirMingguIni) {
            $milikMingguIni = $presensiWali->where('siswa_id', $s->id)->whereBetween('tanggal', [$awalMingguIni, $akhirMingguIni]);

            return (object) ['siswa' => $s, 'alpa' => $penilaian->rekapHarian($milikMingguIni)['alpa']];
        })->filter(fn ($r) => $r->alpa >= $ambangAlpaMingguan)->sortByDesc('alpa')->values();

        $tanggalPresensiTerbaruWali = $presensiWali->max('tanggal');
        $tanggalPresensiDipilih = request()->query('presensi_tanggal') ?: ($tanggalPresensiTerbaruWali ?: now()->toDateString());
        $namaHariPresensiDipilih = self::NAMA_HARI[Carbon::parse($tanggalPresensiDipilih)->dayOfWeek];
        $jadwalHariPresensiDipilih = $jadwalKelasWali->where('hari', $namaHariPresensiDipilih)->values();
        $presensiHariDipilihWali = $presensiWali->where('tanggal', $tanggalPresensiDipilih);

        // Data untuk menu Jurnal & Presensi (guru mapel): jadwal hari ini + kalender riwayat pengisian sebulan.
        $namaHariIni = self::NAMA_HARI[now()->dayOfWeek];
        $jadwalGuruMapel = $jadwalPelajarans->where('guru_id', $guru->id)->values();
        $jadwalHariIni = $jadwalGuruMapel->where('hari', $namaHariIni)->values();
        $jurnalHariIniIds = JurnalMengajar::where('guru_id', $guru->id)->whereDate('tanggal', now())->pluck('jadwal_pelajaran_id');

        $bulanJurnal = request()->query('jurnal_bulan')
            ? Carbon::createFromFormat('Y-m', request()->query('jurnal_bulan'))->startOfMonth()
            : now()->startOfMonth();
        $kalenderJurnal = $this->buildKalenderJurnal($jadwalGuruMapel, $guru->id, $bulanJurnal);
        $namaBulanTerpilih = $bulanJurnal->locale('id')->translatedFormat('F Y');
        $bulanSebelumnya = $bulanJurnal->copy()->subMonth()->format('Y-m');
        $bulanBerikutnya = $bulanJurnal->copy()->addMonth()->format('Y-m');
        $offsetAwalKalender = $bulanJurnal->copy()->startOfMonth()->dayOfWeekIso - 1;

        return view('guru.portal', compact(
            'guru', 'siswas', 'kasusBks', 'kategoriKasusList', 'pelanggarans', 'panggilanOrtus',
            'jadwalPelajarans', 'mataPelajarans', 'kelasList', 'nilaiAkhirMap', 'logPerubahanNilaiSemua', 'kelasMapelBinaan', 'kelasMapelSemua', 'catatanAkademikMap', 'asesmenBkMap',
            'masterPelanggarans', 'masterPelanggaranAktif', 'rekapPoin', 'absensiBermasalah',
            'kelasBinaan', 'siswaBinaan', 'rerataBinaan', 'peringkatBinaan',
            'tahunAjaranAktif', 'mapelBinaan', 'tujuanPembelajarans',
            'nilaiLmBinaan', 'nilaiSasBinaan', 'catatanKompetensiBinaan', 'nilaiPklUkkBinaan',
            'matrixPerKelas', 'raporFinalBinaan', 'logPerubahanNilaiBinaan', 'siswaWali', 'catatanWaliKelasWali', 'pesanWaliKelasWali', 'prestasiNonAkademikWali', 'siswaBinaanLengkap', 'rujukanBkSaya',
            'kelasWaliList', 'rekapPresensiWali', 'rekapHarianWali', 'tanggalPresensiTerbaruWali', 'tanggalPresensiDipilih', 'jadwalHariPresensiDipilih', 'presensiHariDipilihWali', 'presensiWali',
            'siswaPeringatanMingguan', 'ambangAlpaMingguan',
            'jadwalGuruMapel', 'jadwalHariIni', 'jurnalHariIniIds', 'namaHariIni',
            'kalenderJurnal', 'namaBulanTerpilih', 'bulanSebelumnya', 'bulanBerikutnya', 'offsetAwalKalender'
        ));
    }

    /**
     * Kalender sebulan: untuk tiap tanggal yang punya jadwal mengajar guru ini, cek jurnal mana yang
     * sudah/belum diisi. Tanggal tanpa jadwal (mis. hari Minggu) diberi ada_jadwal=false.
     */
    private function buildKalenderJurnal($jadwalGuru, int $guruId, Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        $jurnalSebulan = JurnalMengajar::where('guru_id', $guruId)
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->get()
            ->groupBy(fn ($j) => Carbon::parse($j->tanggal)->toDateString());

        $kalender = [];
        for ($tgl = $awal->copy(); $tgl->lte($akhir); $tgl->addDay()) {
            $namaHari = self::NAMA_HARI[$tgl->dayOfWeek];
            $jadwalHariItu = $jadwalGuru->where('hari', $namaHari)->values();
            $tanggalStr = $tgl->toDateString();

            if ($jadwalHariItu->isEmpty()) {
                $kalender[$tanggalStr] = ['ada_jadwal' => false];

                continue;
            }

            $jurnalHariItu = $jurnalSebulan->get($tanggalStr, collect());
            $terisiIds = $jurnalHariItu->pluck('jadwal_pelajaran_id');

            $detail = $jadwalHariItu->map(function ($j) use ($jurnalHariItu) {
                $terisi = $jurnalHariItu->firstWhere('jadwal_pelajaran_id', $j->id);

                return [
                    'id' => $j->id,
                    'label' => ($j->kelas->nama_kelas ?? '-').' — '.($j->mataPelajaran->nama_mapel ?? '-').' ('.substr($j->jam_mulai, 0, 5).'-'.substr($j->jam_selesai, 0, 5).')',
                    'terisi' => (bool) $terisi,
                    'materi' => $terisi?->materi,
                ];
            })->values();

            $kalender[$tanggalStr] = [
                'ada_jadwal' => true,
                'lewat' => $tgl->lte(today()),
                'total' => $jadwalHariItu->count(),
                'terisi' => $terisiIds->unique()->count(),
                'detail' => $detail->all(),
                'kurang' => $detail->reject('terisi')->pluck('label')->values()->all(),
            ];
        }

        return $kalender;
    }

    public function getSiswaByJadwal(Request $request, $jadwalId)
    {
        $jadwal = JadwalPelajaran::find($jadwalId);
        if (! $jadwal) {
            return response()->json(['siswa' => []]);
        }

        abort_unless($jadwal->guru_id === Auth::id(), 403);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get(['id', 'nama', 'nis']);

        $tanggal = $request->query('tanggal');
        $materi = null;
        $presensi = [];

        if ($tanggal) {
            $materi = JurnalMengajar::where('jadwal_pelajaran_id', $jadwal->id)->where('tanggal', $tanggal)->value('materi');
            $presensi = Presensi::where('jadwal_pelajaran_id', $jadwal->id)->where('tanggal', $tanggal)
                ->get()->keyBy('siswa_id')->map(fn ($p) => ['status' => $p->status, 'keterangan' => $p->keterangan]);
        }

        return response()->json(['siswa' => $siswas, 'materi' => $materi, 'presensi' => $presensi]);
    }

    public function storePelanggaran(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'master_pelanggaran_id' => 'required|exists:master_pelanggarans,id',
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        if (Pelanggaran::where('siswa_id', $data['siswa_id'])->where('master_pelanggaran_id', $data['master_pelanggaran_id'])->whereDate('tanggal', $data['tanggal'])->exists()) {
            return back()->withInput()->with('error', 'Pelanggaran yang sama untuk siswa ini pada tanggal tersebut sudah tercatat.');
        }

        $master = MasterPelanggaran::with('jenisPelanggaran')->findOrFail($data['master_pelanggaran_id']);

        Pelanggaran::create([
            'siswa_id' => $data['siswa_id'],
            'master_pelanggaran_id' => $master->id,
            'tanggal' => $data['tanggal'],
            'kategori' => $master->jenisPelanggaran->nama,
            'judul' => $master->nama_pelanggaran,
            'deskripsi' => $data['keterangan'],
            'poin' => $master->jenisPelanggaran->poin,
            'pelapor_id' => Auth::id(),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Pelanggaran berhasil dicatat.');
    }

    public function storePanggilanOrtu(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'ruang' => 'nullable|string|max:255',
            'alasan' => 'required|string',
        ]);

        if (PanggilanOrtu::where('siswa_id', $data['siswa_id'])->whereDate('tanggal', $data['tanggal'])->where('waktu', $data['waktu'])->exists()) {
            return back()->withInput()->with('error', 'Panggilan orang tua untuk siswa ini pada tanggal dan jam tersebut sudah dijadwalkan.');
        }

        PanggilanOrtu::create($data + [
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => Auth::id(),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Panggilan orang tua berhasil dijadwalkan.');
    }
}
