<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Hanya memetakan baris file ke [siswa_id => [tujuan_pembelajaran_id => nilai]] -- tidak menulis
 * ke database sendiri, supaya penyimpanan tetap lewat PenilaianController::simpanNilaiLm() yang
 * sama dipakai form manual (aturan remedial/pengayaan & rekalkulasi NA Sumatif jadi konsisten).
 */
class NilaiLmImport implements ToCollection, WithHeadingRow
{
    public array $nilai = [];

    public int $berhasil = 0;

    public array $errors = [];

    public function __construct(private int $kelasId, private Collection $tpList) {}

    public function collection(Collection $rows): void
    {
        $lookup = [];
        foreach ($this->tpList->values() as $i => $tp) {
            $label = trim((string) $tp->kode) !== '' ? $tp->kode : 'N'.($i + 1);
            $lookup[Str::slug($label, '_')] = $tp->id;
        }

        // Baris kosong per kolom N tidak langsung dibuang -- ditampung dulu supaya bisa
        // ditentukan belakangan: kolom N yang sudah mulai diisi (minimal satu siswa) tapi
        // kosong untuk siswa lain dianggap 0, sementara kolom N yang kosong semua (belum
        // digarap sama sekali) tetap dibiarkan kosong.
        $mentah = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;
            $nis = trim((string) ($row['nis'] ?? ''));

            if ($nis === '') {
                $this->errors[] = "Baris {$lineNumber}: kolom NIS wajib diisi.";

                continue;
            }

            $siswa = Siswa::where('nis', $nis)->where('kelas_id', $this->kelasId)->first();

            if (! $siswa) {
                $this->errors[] = "Baris {$lineNumber}: NIS {$nis} tidak ditemukan di kelas ini.";

                continue;
            }

            foreach ($lookup as $key => $tujuanPembelajaranId) {
                if (! $row->has($key)) {
                    continue;
                }

                $nilai = $row[$key];
                if ($nilai === null || trim((string) $nilai) === '') {
                    $mentah[$siswa->id][$tujuanPembelajaranId] = null;

                    continue;
                }

                if (! is_numeric($nilai) || (float) $nilai < 0 || (float) $nilai > 100) {
                    $this->errors[] = "Baris {$lineNumber} (NIS {$nis}): nilai \"{$nilai}\" tidak valid, harus angka 0-100.";

                    continue;
                }

                $mentah[$siswa->id][$tujuanPembelajaranId] = (int) round((float) $nilai);
            }
        }

        $tpTerisi = [];
        foreach ($mentah as $perTp) {
            foreach ($perTp as $tujuanPembelajaranId => $nilai) {
                if ($nilai !== null) {
                    $tpTerisi[$tujuanPembelajaranId] = true;
                }
            }
        }

        foreach ($mentah as $siswaId => $perTp) {
            $adaNilai = false;
            foreach ($perTp as $tujuanPembelajaranId => $nilai) {
                if ($nilai === null) {
                    if (! isset($tpTerisi[$tujuanPembelajaranId])) {
                        continue;
                    }

                    $nilai = 0;
                }

                $this->nilai[$siswaId][$tujuanPembelajaranId] = $nilai;
                $adaNilai = true;
            }

            if ($adaNilai) {
                $this->berhasil++;
            }
        }
    }
}
