<?php

namespace App\Imports;

use App\Models\Peminatan;
use App\Services\Asesmen\AsesmenImportHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PeminatanImport implements ToCollection, WithHeadingRow
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
            foreach (Peminatan::SECTIONS as $section) {
                $cell = (string) $this->sectionCell($row, $section);
                $validCodes = array_keys(Peminatan::QUESTION_GROUPS[$section] ?? []);
                preg_match_all('/\b([A-Z]{2}\d{2})\b/i', $cell, $m);
                $codes = array_unique(array_map('strtoupper', $m[1] ?? []));
                $jawaban[$section] = array_values(array_intersect($codes, $validCodes));
            }

            $top3 = Peminatan::dominantIntelligencesFrom($jawaban);

            Peminatan::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => AsesmenImportHelper::parseTanggal($row['timestamp'] ?? null),
                ],
                [
                    'tahun_ajaran_id' => AsesmenImportHelper::resolveTahunAjaranId($row['tahun_pelajaran'] ?? null),
                    'jawaban' => $jawaban,
                    'pilihan1' => $top3[0] ?: null,
                    'pilihan2' => $top3[1] ?: null,
                    'pilihan3' => $top3[2] ?: null,
                    'hasil' => trim((string) ($row['hasil'] ?? '')) ?: ($top3[0] ?: null),
                    'catatan' => trim((string) ($row['catatan'] ?? '')) ?: null,
                ]
            );

            $this->berhasil++;
        }
    }

    private function sectionCell(Collection $row, string $section): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', $section));

        foreach ($row as $col => $value) {
            if (str_starts_with((string) $col, $key)) {
                return (string) $value;
            }
        }

        return '';
    }
}
