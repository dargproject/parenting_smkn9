<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\KasusBk;
use App\Models\Kelas;
use App\Models\MasterPelanggaran;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\PanggilanOrtu;
use App\Models\Pelanggaran;
use App\Models\RaporFinal;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PortalController extends Controller
{
    public function index(PenilaianService $penilaian)
    {
        $guruId = Session::get('guru_id');
        if (! $guruId) {
            return redirect()->route('login');
        }

        $guru = Guru::with('roles')->find($guruId);
        if (! $guru) {
            return redirect()->route('login');
        }

        // Ambil data master untuk ditampilkan di portal
        $siswas = Siswa::with('kelas')->get();
        $kasusBks = KasusBk::with(['siswa', 'konselor'])->get();
        $pelanggarans = Pelanggaran::with(['siswa', 'pelapor'])->orderBy('tanggal', 'desc')->get();
        $panggilanOrtus = PanggilanOrtu::with(['siswa', 'pemanggil'])->orderBy('tanggal', 'desc')->get();
        $jadwalPelajarans = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru'])->get();
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
        $nilaiBinaan = $nilaiAkhirRows->whereIn('siswa_id', $siswaBinaan->pluck('id'));
        $rerataBinaan = $nilaiBinaan->isNotEmpty() ? round($nilaiBinaan->avg('nilai_akhir'), 1) : null;
        $peringkatBinaan = $siswaBinaan
            ->map(fn ($s) => (object) ['nama' => $s->nama, 'rata' => $nilaiBinaan->where('siswa_id', $s->id)->avg('nilai_akhir')])
            ->filter(fn ($s) => $s->rata !== null)
            ->sortByDesc('rata')
            ->take(3)
            ->values();

        // Data untuk Guru Mapel: mapel yang diampu, TP semester berjalan, dan nilai yang sudah diinput
        $mapelBinaan = $mataPelajarans->where('guru_id', $guru->id)->values();
        $tujuanPembelajarans = $tahunAjaranAktif
            ? TujuanPembelajaran::whereIn('mata_pelajaran_id', $mapelBinaan->pluck('id'))
                ->where('tahun_ajaran_id', $tahunAjaranAktifId)
                ->orderBy('urutan')
                ->get()
            : collect();

        $nilaiLmBinaan = NilaiLm::whereIn('tujuan_pembelajaran_id', $tujuanPembelajarans->pluck('id'))
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

        $matrixPerKelas = $kelasBinaan->map(function ($kelas) use ($siswaBinaan, $jadwalPelajarans, $allNilaiLmKelasBinaan, $allNilaiSasKelasBinaan, $penilaian) {
            $siswaKelas = $siswaBinaan->where('kelas_id', $kelas->id)->values();
            $mapelKelas = $jadwalPelajarans
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

        $catatanWaliKelasBinaan = $tahunAjaranAktif
            ? CatatanWaliKelas::whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()->keyBy('siswa_id')
            : collect();
        $raporFinalBinaan = $tahunAjaranAktif
            ? RaporFinal::whereIn('siswa_id', $siswaBinaan->pluck('id'))->where('tahun_ajaran_id', $tahunAjaranAktifId)->get()->keyBy('siswa_id')
            : collect();
        $siswaBinaanLengkap = $tahunAjaranAktif
            ? $siswaBinaan->mapWithKeys(fn ($s) => [$s->id => $penilaian->siswaLengkap($s, $tahunAjaranAktifId)])
            : collect();

        return view('guru.portal', compact(
            'guru', 'siswas', 'kasusBks', 'pelanggarans', 'panggilanOrtus',
            'jadwalPelajarans', 'mataPelajarans', 'kelasList', 'nilaiAkhirMap',
            'masterPelanggarans', 'masterPelanggaranAktif', 'rekapPoin', 'absensiBermasalah',
            'kelasBinaan', 'siswaBinaan', 'rerataBinaan', 'peringkatBinaan',
            'tahunAjaranAktif', 'mapelBinaan', 'tujuanPembelajarans',
            'nilaiLmBinaan', 'nilaiSasBinaan', 'catatanKompetensiBinaan', 'nilaiPklUkkBinaan',
            'matrixPerKelas', 'catatanWaliKelasBinaan', 'raporFinalBinaan', 'siswaBinaanLengkap'
        ));
    }

    public function getSiswaByJadwal($jadwalId)
    {
        $jadwal = JadwalPelajaran::find($jadwalId);
        if (! $jadwal) {
            return response()->json([]);
        }

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->get();

        return response()->json($siswas);
    }

    public function storePelanggaran(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'master_pelanggaran_id' => 'required|exists:master_pelanggarans,id',
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);

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

        PanggilanOrtu::create($data + [
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => Auth::id(),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Panggilan orang tua berhasil dijadwalkan.');
    }
}
