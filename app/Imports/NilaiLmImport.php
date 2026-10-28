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

            $adaNilai = false;
            foreach ($lookup as $key => $tujuanPembelajaranId) {
                if (! $row->has($key)) {
                    continue;
                }

                $nilai = $row[$key];
                if ($nilai === null || trim((string) $nilai) === '') {
                    continue;
                }

                if (! is_numeric($nilai) || (float) $nilai < 0 || (float) $nilai > 100) {
                    $this->errors[] = "Baris {$lineNumber} (NIS {$nis}): nilai \"{$nilai}\" tidak valid, harus angka 0-100.";

                    continue;
                }

                $this->nilai[$siswa->id][$tujuanPembelajaranId] = (int) round((float) $nilai);
                $adaNilai = true;
            }

            if ($adaNilai) {
                $this->berhasil++;
            }
        }
    }
}
