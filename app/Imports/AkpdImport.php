<?php

namespace App\Imports;

use App\Models\Akpd;
use App\Services\Asesmen\AsesmenImportHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AkpdImport implements ToCollection, WithHeadingRow
{
    public int $berhasil = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;

            $nama = trim((string) ($row['nama_siswa'] ?? ''));
            $kelas = trim((string) ($row['kelas'] ?? ''));

            if ($nama === '' || $kelas === '') {
                $this->errors[] = "Baris {$lineNumber}: kolom Nama Siswa dan Kelas wajib diisi.";

                continue;
            }

            $siswa = AsesmenImportHelper::resolveSiswa($row['nis'] ?? null, $nama, $kelas);

            if (! $siswa) {
                $this->errors[] = "Baris {$lineNumber}: siswa \"{$nama}\" di kelas \"{$kelas}\" tidak ditemukan.";

                continue;
            }

            $jawaban = [];
            foreach (range(1, 50) as $no) {
                $value = strtolower(trim((string) $this->questionColumnValue($row, $no)));
                $jawaban[$no] = match (true) {
                    $value === 'ya' => 'Ya',
                    $value === 'tidak' => 'Tidak',
                    default => null,
                };
            }

            Akpd::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => AsesmenImportHelper::parseTanggal($row['timestamp'] ?? null),
                ],
                [
                    'tahun_ajaran_id' => AsesmenImportHelper::resolveTahunAjaranId($row['tahun_pelajaran'] ?? null),
                    'jawaban' => $jawaban,
                ]
            );

            $this->berhasil++;
        }
    }

    /**
     * Header asli berformat "{no}. <teks soal>" yang dinormalisasi WithHeadingRow jadi "{no}_<teks>".
     */
    private function questionColumnValue(Collection $row, int $no): mixed
    {
        foreach ($row as $key => $value) {
            if (preg_match('/^(\d+)/', (string) $key, $m) === 1 && (int) $m[1] === $no) {
                return $value;
            }
        }

        return null;
    }
}
