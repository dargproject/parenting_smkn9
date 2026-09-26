<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\RaporFinal;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    private function tahunAjaranAktifId(): int
    {
        return TahunAjaran::where('is_active', true)->value('id')
            ?? abort(422, 'Belum ada tahun ajaran aktif.');
    }

    private function pastikanMapelMilikGuru(int $mataPelajaranId): MataPelajaran
    {
        $mapel = MataPelajaran::findOrFail($mataPelajaranId);
        abort_unless(
            \App\Models\KelasMataPelajaran::where('mata_pelajaran_id', $mapel->id)->where('guru_id', Auth::id())->exists(),
            403,
            'Anda bukan pengampu mata pelajaran ini.'
        );

        return $mapel;
    }

    public function storeTujuanPembelajaran(Request $request)
    {
        $tpUnik = fn (string $kolom) => \Illuminate\Validation\Rule::unique('tujuan_pembelajarans', $kolom)
            ->where('mata_pelajaran_id', $request->input('mata_pelajaran_id'))
            ->where('tahun_ajaran_id', $this->tahunAjaranAktifId());

        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kode' => ['nullable', 'string', 'max:20', $tpUnik('kode')],
            'deskripsi' => ['required', 'string', 'max:500', $tpUnik('deskripsi')],
            'urutan' => 'nullable|integer|min:0',
        ], [
            'kode.unique' => 'Kode TP tersebut sudah dipakai pada mapel ini.',
            'deskripsi.unique' => 'Tujuan pembelajaran dengan deskripsi yang sama sudah ada pada mapel ini.',
        ]);

        $this->pastikanMapelMilikGuru($data['mata_pelajaran_id']);

        TujuanPembelajaran::create($data + [
            'tahun_ajaran_id' => $this->tahunAjaranAktifId(),
            'urutan' => $data['urutan'] ?? 0,
        ]);

        return redirect()->route('guru.portal')->with('success', 'Tujuan pembelajaran berhasil ditambahkan.');
    }

    public function destroyTujuanPembelajaran(TujuanPembelajaran $tujuanPembelajaran)
    {
        $this->pastikanMapelMilikGuru($tujuanPembelajaran->mata_pelajaran_id);

        $tujuanPembelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Tujuan pembelajaran berhasil dihapus.');
    }

    public function storeNilaiLm(Request $request)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
            'nilai.*.*' => 'nullable|integer|min:0|max:100',
        ]);

        $this->pastikanMapelMilikGuru($data['mata_pelajaran_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        $tpValid = TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])->where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');
        $tpKiriman = collect($data['nilai'])->flatMap(fn ($perTp) => array_keys($perTp))->unique();
        if ($tpKiriman->diff($tpValid)->isNotEmpty()) {
            return back()->with('error', 'Sebagian Tujuan Pembelajaran bukan milik semester/tahun ajaran aktif. Muat ulang halaman lalu isi kembali.');
        }

        DB::transaction(function () use ($data, $tahunAjaranId) {
            foreach ($data['nilai'] as $siswaId => $perTp) {
                foreach ($perTp as $tujuanPembelajaranId => $nilai) {
                    if ($nilai === null || $nilai === '') {
                        continue;
                    }

                    NilaiLm::updateOrCreate(
                        ['siswa_id' => $siswaId, 'tujuan_pembelajaran_id' => $tujuanPembelajaranId],
                        ['nilai' => $nilai, 'tahun_ajaran_id' => $tahunAjaranId, 'guru_id' => Auth::id()]
                    );
                }
            }

            $tpIds = TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])->where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');
            foreach (array_keys($data['nilai']) as $siswaId) {
                $rata = NilaiLm::where('siswa_id', $siswaId)->whereIn('tujuan_pembelajaran_id', $tpIds)->avg('nilai');
                if ($rata === null) {
                    continue;
                }

                NilaiSas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $data['mata_pelajaran_id'], 'tahun_ajaran_id' => $tahunAjaranId],
                    ['nilai' => (int) round($rata), 'guru_id' => Auth::id()]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Nilai Sumatif Lingkup Materi berhasil disimpan; nilai SAS dihitung otomatis dari rata-rata.');
    }

    public function storeNilaiSas(Request $request)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai_sas' => 'required|array',
            'nilai_sas.*' => 'nullable|integer|min:0|max:100',
        ]);

        $this->pastikanMapelMilikGuru($data['mata_pelajaran_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        DB::transaction(function () use ($data, $tahunAjaranId) {
            foreach ($data['nilai_sas'] as $siswaId => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }

                NilaiSas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $data['mata_pelajaran_id'], 'tahun_ajaran_id' => $tahunAjaranId],
                    ['nilai' => $nilai, 'guru_id' => Auth::id()]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Nilai Sumatif Akhir Semester berhasil disimpan.');
    }

    public function storeCatatanKompetensi(Request $request)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'catatan' => 'required|array',
            'catatan.*' => 'nullable|string',
        ]);

        $this->pastikanMapelMilikGuru($data['mata_pelajaran_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        DB::transaction(function () use ($data, $tahunAjaranId) {
            foreach ($data['catatan'] as $siswaId => $catatan) {
                if (! trim((string) $catatan)) {
                    continue;
                }

                CatatanKompetensi::updateOrCreate(
                    ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $data['mata_pelajaran_id'], 'tahun_ajaran_id' => $tahunAjaranId],
                    ['catatan' => $catatan, 'guru_id' => Auth::id()]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Catatan capaian kompetensi berhasil disimpan.');
    }

    public function storeNilaiPklUkk(Request $request)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
            'nilai.*.pkl' => 'nullable|integer|min:0|max:100',
            'nilai.*.ukk' => 'nullable|integer|min:0|max:100',
            'catatan' => 'nullable|array',
            'catatan.*.pkl' => 'nullable|string',
            'catatan.*.ukk' => 'nullable|string',
        ]);

        $mapel = $this->pastikanMapelMilikGuru($data['mata_pelajaran_id']);
        abort_unless($mapel->kategori === 'Kejuruan', 422, 'PKL/UKK hanya untuk mata pelajaran kejuruan.');
        $tahunAjaranId = $this->tahunAjaranAktifId();

        DB::transaction(function () use ($data, $tahunAjaranId) {
            foreach ($data['nilai'] as $siswaId => $perJenis) {
                foreach (['pkl', 'ukk'] as $jenis) {
                    $nilai = $perJenis[$jenis] ?? null;
                    $catatan = $data['catatan'][$siswaId][$jenis] ?? null;

                    if ($nilai === null && ! $catatan) {
                        continue;
                    }

                    NilaiPklUkk::updateOrCreate(
                        ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $data['mata_pelajaran_id'], 'tahun_ajaran_id' => $tahunAjaranId, 'jenis' => $jenis],
                        ['nilai' => $nilai, 'catatan' => $catatan, 'guru_id' => Auth::id()]
                    );
                }
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Nilai PKL/UKK berhasil disimpan.');
    }

    public function storeCatatanWaliKelas(Request $request)
    {
        $guru = Auth::user();
        $siswaWaliIds = Kelas::where('wali_kelas_id', $guru->id)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'catatan' => 'required|array',
            'catatan.*.catatan_karakter' => 'nullable|string|max:2000',
            'catatan.*.sakit' => 'nullable|integer|min:0|max:365',
            'catatan.*.izin' => 'nullable|integer|min:0|max:365',
            'catatan.*.tanpa_keterangan' => 'nullable|integer|min:0|max:365',
        ]);

        $tahunAjaranId = $this->tahunAjaranAktifId();

        DB::transaction(function () use ($data, $siswaWaliIds, $tahunAjaranId, $guru) {
            foreach ($data['catatan'] as $siswaId => $row) {
                if (! $siswaWaliIds->contains((int) $siswaId)) {
                    continue;
                }

                \App\Models\CatatanWaliKelas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAjaranId],
                    [
                        'catatan_karakter' => $row['catatan_karakter'] ?? null,
                        'sakit' => $row['sakit'] ?? 0,
                        'izin' => $row['izin'] ?? 0,
                        'tanpa_keterangan' => $row['tanpa_keterangan'] ?? 0,
                        'guru_id' => $guru->id,
                    ]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Catatan wali kelas berhasil disimpan.');
    }

    public function storeCatatanAkademik(Request $request)
    {
        $guru = Auth::user();
        $siswaBinaanIds = Kelas::where('guru_wali_id', $guru->id)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'catatan' => 'required|array',
            'catatan.*' => 'nullable|string|max:2000',
        ]);

        $tahunAjaranId = $this->tahunAjaranAktifId();

        DB::transaction(function () use ($data, $siswaBinaanIds, $tahunAjaranId, $guru) {
            foreach ($data['catatan'] as $siswaId => $catatan) {
                if (! $siswaBinaanIds->contains((int) $siswaId)) {
                    continue;
                }

                \App\Models\CatatanAkademikSiswa::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAjaranId],
                    ['catatan' => $catatan, 'guru_id' => $guru->id]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Catatan akademik berhasil disimpan.');
    }

    public function rilisRapor(Request $request, PenilaianService $penilaian)
    {
        $guru = Auth::user();
        $siswaBinaan = Kelas::where('guru_wali_id', $guru->id)->with('siswas')->get()->flatMap->siswas;

        $data = $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'in:'.$siswaBinaan->pluck('id')->implode(','),
        ]);

        $tahunAjaranId = $this->tahunAjaranAktifId();
        $belumLengkap = [];

        foreach ($data['siswa_ids'] as $siswaId) {
            $siswa = $siswaBinaan->firstWhere('id', (int) $siswaId);
            if (! $siswa || ! $penilaian->siswaLengkap($siswa, $tahunAjaranId)) {
                $belumLengkap[] = $siswa->nama ?? $siswaId;

                continue;
            }

            RaporFinal::updateOrCreate(
                ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAjaranId],
                ['status' => 'final', 'dirilis_at' => now(), 'dirilis_oleh' => $guru->id]
            );
        }

        if ($belumLengkap) {
            return back()->with('error', 'Nilai SAS belum lengkap untuk: '.implode(', ', $belumLengkap).'. Rapor siswa lain tetap dirilis.');
        }

        return redirect()->route('guru.portal')->with('success', 'Rapor berhasil dirilis.');
    }

    public function batalkanRilis(Request $request)
    {
        $guru = Auth::user();
        $siswaBinaan = Kelas::where('guru_wali_id', $guru->id)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'siswa_id' => 'required|in:'.$siswaBinaan->implode(','),
        ]);

        RaporFinal::where(['siswa_id' => $data['siswa_id'], 'tahun_ajaran_id' => $this->tahunAjaranAktifId()])
            ->update(['status' => 'draft', 'dirilis_at' => null, 'dirilis_oleh' => null]);

        return redirect()->route('guru.portal')->with('success', 'Rilis rapor dibatalkan, kembali ke status draft.');
    }
}
