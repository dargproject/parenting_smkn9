<?php

namespace App\Http\Controllers\Guru;

use App\Exports\NilaiLmTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\NilaiLmImport;
use App\Models\CatatanAkademikSiswa;
use App\Models\CatatanKompetensi;
use App\Models\CatatanWaliKelas;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\LogPerubahanNilai;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\Presensi;
use App\Models\RaporFinal;
use App\Models\RujukanBk;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            KelasMataPelajaran::where('mata_pelajaran_id', $mapel->id)->where('guru_id', Auth::id())->exists(),
            403,
            'Anda bukan pengampu mata pelajaran ini.'
        );

        return $mapel;
    }

    /**
     * Tujuan Pembelajaran (N) independen per kelas, jadi pengecekan kepemilikan guru harus
     * tahu kelasnya juga -- bukan cuma mapelnya -- supaya guru tidak bisa mengubah N milik
     * kelas lain hanya karena kebetulan mengampu mapel yang sama di kelas tsb.
     */
    private function pastikanMapelKelasMilikGuru(int $mataPelajaranId, int $kelasId): void
    {
        abort_unless(
            KelasMataPelajaran::where('mata_pelajaran_id', $mataPelajaranId)
                ->where('kelas_id', $kelasId)
                ->where('guru_id', Auth::id())
                ->exists(),
            403,
            'Anda bukan pengampu mata pelajaran ini di kelas tersebut.'
        );
    }

    public function storeTujuanPembelajaran(Request $request)
    {
        $tpUnik = fn (string $kolom) => Rule::unique('tujuan_pembelajarans', $kolom)
            ->where('mata_pelajaran_id', $request->input('mata_pelajaran_id'))
            ->where('kelas_id', $request->input('kelas_id'))
            ->where('tahun_ajaran_id', $this->tahunAjaranAktifId());

        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
            'kode' => ['nullable', 'string', 'max:20', $tpUnik('kode')],
            'deskripsi' => ['required', 'string', 'max:500', $tpUnik('deskripsi')],
            'urutan' => 'nullable|integer|min:0',
        ], [
            'kode.unique' => 'Kode N tersebut sudah dipakai pada kelas & mapel ini.',
            'deskripsi.unique' => 'Nilai (N) dengan deskripsi yang sama sudah ada pada kelas & mapel ini.',
        ]);

        $this->pastikanMapelKelasMilikGuru($data['mata_pelajaran_id'], $data['kelas_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        $urutanBerikutnya = 1 + (int) TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])
            ->where('kelas_id', $data['kelas_id'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->max('urutan');

        TujuanPembelajaran::create($data + [
            'tahun_ajaran_id' => $tahunAjaranId,
            'urutan' => $data['urutan'] ?? $urutanBerikutnya,
        ]);

        return redirect()->route('guru.portal')->with('success', 'Kolom N berhasil ditambahkan.');
    }

    public function updateTujuanPembelajaran(Request $request, TujuanPembelajaran $tujuanPembelajaran)
    {
        $this->pastikanMapelKelasMilikGuru($tujuanPembelajaran->mata_pelajaran_id, $tujuanPembelajaran->kelas_id);

        $tpUnik = fn (string $kolom) => Rule::unique('tujuan_pembelajarans', $kolom)
            ->where('mata_pelajaran_id', $tujuanPembelajaran->mata_pelajaran_id)
            ->where('kelas_id', $tujuanPembelajaran->kelas_id)
            ->where('tahun_ajaran_id', $tujuanPembelajaran->tahun_ajaran_id)
            ->ignore($tujuanPembelajaran->id);

        $data = $request->validate([
            'kode' => ['nullable', 'string', 'max:20', $tpUnik('kode')],
            'deskripsi' => ['required', 'string', 'max:500', $tpUnik('deskripsi')],
        ], [
            'kode.unique' => 'Kode N tersebut sudah dipakai pada kelas & mapel ini.',
            'deskripsi.unique' => 'Nilai (N) dengan deskripsi yang sama sudah ada pada kelas & mapel ini.',
        ]);

        $tujuanPembelajaran->update($data);

        return redirect()->route('guru.portal')->with('success', 'Kolom N berhasil diperbarui.');
    }

    public function destroyTujuanPembelajaran(TujuanPembelajaran $tujuanPembelajaran)
    {
        $this->pastikanMapelKelasMilikGuru($tujuanPembelajaran->mata_pelajaran_id, $tujuanPembelajaran->kelas_id);

        $tujuanPembelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Kolom N berhasil dihapus.');
    }

    public function storeNilaiLm(Request $request, PenilaianService $penilaian)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nilai' => 'required|array',
            'nilai.*.*' => 'nullable|integer|min:0|max:100',
            'remedial' => 'nullable|array',
            'remedial.*.*' => 'nullable|integer|min:0|max:100',
            'pengayaan' => 'nullable|array',
            'nilai_pengayaan' => 'nullable|array',
            'nilai_pengayaan.*.*' => 'nullable|integer|min:0|max:100',
            'catatan_pengayaan' => 'nullable|array',
            'catatan_pengayaan.*.*' => 'nullable|string|max:500',
        ]);

        $this->pastikanMapelKelasMilikGuru($data['mata_pelajaran_id'], $data['kelas_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        $tpValid = TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])->where('kelas_id', $data['kelas_id'])->where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');
        $tpKiriman = collect($data['nilai'])->flatMap(fn ($perTp) => array_keys($perTp))->unique();
        if ($tpKiriman->diff($tpValid)->isNotEmpty()) {
            return back()->with('error', 'Sebagian kolom N bukan milik semester/tahun ajaran aktif. Muat ulang halaman lalu isi kembali.');
        }

        $this->simpanNilaiLm(
            $data['nilai'], $data['remedial'] ?? [], $data['pengayaan'] ?? [], $data['nilai_pengayaan'] ?? [], $data['catatan_pengayaan'] ?? [],
            $data['mata_pelajaran_id'], $data['kelas_id'], $tahunAjaranId, $penilaian
        );

        return redirect()->route('guru.portal')->with('success', 'Nilai Formatif berhasil disimpan; Nilai Akhir Sumatif diperbarui otomatis dari rata-ratanya.');
    }

    /**
     * Logika inti penyimpanan Nilai LM, dipakai bersama oleh input manual (storeNilaiLm) dan
     * import Excel/CSV (importNilaiLm) -- supaya aturan remedial/pengayaan & rekalkulasi NA
     * Sumatif selalu konsisten dari kedua jalur, tidak ada logika yang didobel-tulis.
     */
    private function simpanNilaiLm(array $nilaiPerSiswaTp, array $remedial, array $pengayaan, array $nilaiPengayaan, array $catatanPengayaan, int $mataPelajaranId, int $kelasId, int $tahunAjaranId, PenilaianService $penilaian): void
    {
        $kktp = $penilaian->kktpThreshold();

        DB::transaction(function () use ($nilaiPerSiswaTp, $remedial, $pengayaan, $nilaiPengayaan, $catatanPengayaan, $mataPelajaranId, $kelasId, $tahunAjaranId, $penilaian, $kktp) {
            foreach ($nilaiPerSiswaTp as $siswaId => $perTp) {
                foreach ($perTp as $tujuanPembelajaranId => $nilai) {
                    if ($nilai === null || $nilai === '') {
                        continue;
                    }

                    $sebelum = NilaiLm::where('siswa_id', $siswaId)->where('tujuan_pembelajaran_id', $tujuanPembelajaranId)->first();

                    $nl = NilaiLm::updateOrCreate(
                        ['siswa_id' => $siswaId, 'tujuan_pembelajaran_id' => $tujuanPembelajaranId],
                        ['nilai' => $nilai, 'tahun_ajaran_id' => $tahunAjaranId, 'guru_id' => Auth::id()]
                    );

                    if ($sebelum && (int) $sebelum->nilai !== (int) $nilai) {
                        LogPerubahanNilai::create([
                            'nilai_lm_id' => $nl->id,
                            'kolom' => 'nilai',
                            'nilai_lama' => $sebelum->nilai,
                            'nilai_baru' => $nilai,
                            'guru_id' => Auth::id(),
                        ]);
                    }

                    if ((int) $nilai < $kktp) {
                        $baruRemedial = $remedial[$siswaId][$tujuanPembelajaranId] ?? null;
                        $baruRemedial = ($baruRemedial === null || $baruRemedial === '') ? null : (int) $baruRemedial;
                        $lamaRemedial = $nl->nilai_remedial;
                        if ($lamaRemedial !== $baruRemedial) {
                            LogPerubahanNilai::create([
                                'nilai_lm_id' => $nl->id,
                                'kolom' => 'nilai_remedial',
                                'nilai_lama' => $lamaRemedial,
                                'nilai_baru' => $baruRemedial,
                                'guru_id' => Auth::id(),
                            ]);
                        }
                        $nl->update(['nilai_remedial' => $baruRemedial, 'sudah_pengayaan' => false, 'nilai_pengayaan' => null, 'catatan_pengayaan' => null]);
                    } else {
                        $sudah = isset($pengayaan[$siswaId][$tujuanPembelajaranId]);

                        $baruNilaiPengayaan = $nilaiPengayaan[$siswaId][$tujuanPembelajaranId] ?? null;
                        $baruNilaiPengayaan = ($baruNilaiPengayaan === null || $baruNilaiPengayaan === '') ? null : (int) $baruNilaiPengayaan;
                        $baruNilaiPengayaan = $sudah ? $baruNilaiPengayaan : null;
                        $lamaNilaiPengayaan = $nl->nilai_pengayaan;
                        if ($lamaNilaiPengayaan !== $baruNilaiPengayaan) {
                            LogPerubahanNilai::create([
                                'nilai_lm_id' => $nl->id,
                                'kolom' => 'nilai_pengayaan',
                                'nilai_lama' => $lamaNilaiPengayaan,
                                'nilai_baru' => $baruNilaiPengayaan,
                                'guru_id' => Auth::id(),
                            ]);
                        }

                        $nl->update([
                            'nilai_remedial' => null,
                            'sudah_pengayaan' => $sudah,
                            'nilai_pengayaan' => $baruNilaiPengayaan,
                            'catatan_pengayaan' => $sudah ? ($catatanPengayaan[$siswaId][$tujuanPembelajaranId] ?? null) : null,
                        ]);
                    }
                }
            }

            $tpIds = TujuanPembelajaran::where('mata_pelajaran_id', $mataPelajaranId)->where('kelas_id', $kelasId)->where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');
            foreach (array_keys($nilaiPerSiswaTp) as $siswaId) {
                $nilaiLmSiswa = NilaiLm::where('siswa_id', $siswaId)->whereIn('tujuan_pembelajaran_id', $tpIds)->get();
                $rata = $penilaian->rataLm($nilaiLmSiswa);
                if ($rata === null) {
                    continue;
                }

                NilaiSas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'mata_pelajaran_id' => $mataPelajaranId, 'tahun_ajaran_id' => $tahunAjaranId],
                    ['nilai' => (int) round($rata), 'guru_id' => Auth::id()]
                );
            }
        });
    }

    /**
     * Template CSV untuk import nilai -- header = NIS, Nama, lalu satu kolom per N (pakai label
     * yang sama dengan yang ditampilkan di form input manual: kode kalau diisi, atau "N<urutan>").
     * Nilai yang sudah ada diisikan otomatis supaya guru tinggal mengedit yang perlu diubah saja.
     */
    public function templateNilaiLm(Request $request)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $this->pastikanMapelKelasMilikGuru($data['mata_pelajaran_id'], $data['kelas_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        $tpList = TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])
            ->where('kelas_id', $data['kelas_id'])->where('tahun_ajaran_id', $tahunAjaranId)
            ->orderBy('urutan')->get();

        abort_if($tpList->isEmpty(), 422, 'Belum ada kolom N untuk mapel & kelas ini. Tambahkan N terlebih dahulu di Langkah 1.');

        $mapel = MataPelajaran::findOrFail($data['mata_pelajaran_id']);
        $kelas = Kelas::findOrFail($data['kelas_id']);
        $siswaKelas = $kelas->siswas()->where('status_aktif', true)->orderBy('nama')->get();
        $labels = $tpList->values()->map(fn ($tp, $i) => $tp->kode ?: 'N'.($i + 1));

        $nilaiAda = NilaiLm::whereIn('tujuan_pembelajaran_id', $tpList->pluck('id'))
            ->whereIn('siswa_id', $siswaKelas->pluck('id'))
            ->get()->keyBy(fn ($n) => $n->siswa_id.'-'.$n->tujuan_pembelajaran_id);

        $rows = $siswaKelas->map(function ($siswa) use ($tpList, $nilaiAda) {
            $row = [$siswa->nis, $siswa->nama];
            foreach ($tpList as $tp) {
                $row[] = $nilaiAda[$siswa->id.'-'.$tp->id]->nilai ?? '';
            }

            return $row;
        });

        return Excel::download(
            new NilaiLmTemplateExport(array_merge(['NIS', 'Nama Siswa'], $labels->all()), $rows),
            'template-nilai-'.Str::slug($mapel->nama_mapel).'-'.Str::slug($kelas->nama_kelas).'.xlsx'
        );
    }

    public function importNilaiLm(Request $request, PenilaianService $penilaian)
    {
        $data = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->pastikanMapelKelasMilikGuru($data['mata_pelajaran_id'], $data['kelas_id']);
        $tahunAjaranId = $this->tahunAjaranAktifId();

        $tpList = TujuanPembelajaran::where('mata_pelajaran_id', $data['mata_pelajaran_id'])
            ->where('kelas_id', $data['kelas_id'])->where('tahun_ajaran_id', $tahunAjaranId)
            ->orderBy('urutan')->get();

        abort_if($tpList->isEmpty(), 422, 'Belum ada kolom N untuk mapel & kelas ini. Tambahkan N terlebih dahulu di Langkah 1.');

        $import = new NilaiLmImport($data['kelas_id'], $tpList);
        Excel::import($import, $request->file('file'));

        if (empty($import->nilai)) {
            return back()->with('error', 'Tidak ada nilai yang berhasil diimpor. '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        $this->simpanNilaiLm($import->nilai, [], [], [], [], $data['mata_pelajaran_id'], $data['kelas_id'], $tahunAjaranId, $penilaian);

        $pesan = "{$import->berhasil} siswa berhasil diimpor nilainya.";
        if ($import->errors) {
            return redirect()->route('guru.portal')->with('error', $pesan.' '.count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return redirect()->route('guru.portal')->with('success', $pesan.' Nilai Akhir Sumatif diperbarui otomatis dari rata-ratanya.');
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
                    CatatanKompetensi::where(['siswa_id' => $siswaId, 'mata_pelajaran_id' => $data['mata_pelajaran_id'], 'tahun_ajaran_id' => $tahunAjaranId])->delete();

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

    public function storeCatatanWaliKelas(Request $request, PenilaianService $penilaian)
    {
        $guru = Auth::user();
        $kelasWaliIds = Kelas::where('wali_kelas_id', $guru->id)->pluck('id');
        $siswaWaliIds = Kelas::whereIn('id', $kelasWaliIds)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'catatan' => 'required|array',
            'catatan.*.catatan_karakter' => 'nullable|string|max:2000',
        ]);

        $tahunAjaranId = $this->tahunAjaranAktifId();

        // Sakit/Izin/Alpa tidak lagi diketik manual: dihitung otomatis dari presensi asli (hari
        // Sakit/Izin/Alpa Penuh, lihat PenilaianService::rekapHarian) agar selalu sesuai dengan
        // Rekap Presensi Mapel dan tidak perlu diinput dua kali oleh wali kelas.
        $presensiSemester = Presensi::whereIn('jadwal_pelajaran_id', JadwalPelajaran::whereIn('kelas_id', $kelasWaliIds)->pluck('id'))
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();

        DB::transaction(function () use ($data, $siswaWaliIds, $tahunAjaranId, $guru, $presensiSemester, $penilaian) {
            foreach ($data['catatan'] as $siswaId => $row) {
                if (! $siswaWaliIds->contains((int) $siswaId)) {
                    continue;
                }

                $rekap = $penilaian->rekapHarian($presensiSemester->where('siswa_id', (int) $siswaId));

                CatatanWaliKelas::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAjaranId],
                    [
                        'catatan_karakter' => $row['catatan_karakter'] ?? null,
                        'sakit' => $rekap['sakit'],
                        'izin' => $rekap['izin'],
                        'tanpa_keterangan' => $rekap['alpa'],
                        'guru_id' => $guru->id,
                    ]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Catatan wali kelas berhasil disimpan.');
    }

    public function storeRujukanBk(Request $request)
    {
        $guru = Auth::user();
        $kelasWaliIds = Kelas::where('wali_kelas_id', $guru->id)->pluck('id');
        $siswaWaliIds = Kelas::whereIn('id', $kelasWaliIds)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');

        $data = $request->validate([
            'siswa_id' => ['required', 'integer', Rule::in($siswaWaliIds)],
            'kategori' => 'required|string|max:50',
            'alasan' => 'required|string|max:2000',
        ]);

        RujukanBk::create($data + ['dirujuk_oleh' => $guru->id]);

        return redirect()->route('guru.portal')->with('success', 'Rujukan ke BK berhasil dikirim, menunggu ditinjau oleh guru BK.');
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

                CatatanAkademikSiswa::updateOrCreate(
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
            return back()->with('error', 'Nilai belum lengkap untuk: '.implode(', ', $belumLengkap).'. Rapor siswa lain tetap dirilis.');
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
