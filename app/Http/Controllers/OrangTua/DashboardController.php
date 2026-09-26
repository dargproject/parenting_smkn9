<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\RaporFinal;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(PenilaianService $penilaian)
    {
        $orangTua = Auth::guard('orangtua')->user();
        $siswa = $orangTua->siswa;
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();

        $raporFinal = $tahunAjaranAktif
            ? RaporFinal::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first()
            : null;

        if (! $raporFinal || $raporFinal->status !== 'final') {
            return view('ortu.dashboard', ['siswa' => $siswa, 'dirilis' => false, 'ringkasan' => collect()]);
        }

        $mapelList = \App\Models\KelasMataPelajaran::where('kelas_id', $siswa->kelas_id)
            ->with('mataPelajaran')
            ->get()
            ->pluck('mataPelajaran')
            ->filter()
            ->unique('id')
            ->values();

        $nilaiLmSiswa = NilaiLm::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->with('tujuanPembelajaran')->get();
        $nilaiSasSiswa = NilaiSas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $tahunAjaranAktif->id)->get();

        $ringkasan = $mapelList->map(function ($mapel) use ($nilaiLmSiswa, $nilaiSasSiswa, $penilaian) {
            $lmMapel = $nilaiLmSiswa->filter(fn ($n) => $n->tujuanPembelajaran && $n->tujuanPembelajaran->mata_pelajaran_id === $mapel->id);
            $sas = $nilaiSasSiswa->firstWhere('mata_pelajaran_id', $mapel->id);
            $rataLm = $penilaian->rataLm($lmMapel);
            $na = $penilaian->nilaiAkhir($rataLm, $sas?->nilai);

            return [
                'mapel' => $mapel,
                'na' => $na,
                'status' => $penilaian->statusMapel($lmMapel, $sas?->nilai !== null ? (float) $sas->nilai : null),
            ];
        });

        return view('ortu.dashboard', [
            'siswa' => $siswa,
            'dirilis' => true,
            'ringkasan' => $ringkasan,
            'perluPerhatian' => $ringkasan->filter(fn ($r) => $r['status'] === 'remedial'),
        ]);
    }

    public function show(MataPelajaran $mataPelajaran, PenilaianService $penilaian)
    {
        $orangTua = Auth::guard('orangtua')->user();
        $siswa = $orangTua->siswa;
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();

        $raporFinal = $tahunAjaranAktif
            ? RaporFinal::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first()
            : null;

        abort_unless($raporFinal && $raporFinal->status === 'final', 403, 'Rapor semester ini belum dirilis oleh wali kelas.');

        $nilaiLm = NilaiLm::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->whereHas('tujuanPembelajaran', fn ($q) => $q->where('mata_pelajaran_id', $mataPelajaran->id))
            ->with('tujuanPembelajaran')
            ->get()
            ->sortBy(fn ($n) => $n->tujuanPembelajaran->urutan);

        $sas = NilaiSas::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();
        $pklUkk = NilaiPklUkk::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->get()->keyBy('jenis');
        $catatanKompetensi = CatatanKompetensi::where(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mataPelajaran->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();
        $catatanWaliKelas = CatatanWaliKelas::where(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id])->first();

        $rataLm = $penilaian->rataLm($nilaiLm);
        $na = $penilaian->nilaiAkhir($rataLm, $sas?->nilai);

        return view('ortu.mapel-detail', [
            'siswa' => $siswa,
            'mapel' => $mataPelajaran,
            'nilaiLm' => $nilaiLm,
            'sas' => $sas,
            'pklUkk' => $pklUkk,
            'na' => $na,
            'status' => $penilaian->statusMapel($nilaiLm, $sas?->nilai !== null ? (float) $sas->nilai : null),
            'tpRemedial' => $penilaian->tpRemedial($nilaiLm),
            'catatanKompetensi' => $catatanKompetensi,
            'catatanWaliKelas' => $catatanWaliKelas,
        ]);
    }
}
