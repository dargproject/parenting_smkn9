<?php

namespace App\Imports;

use App\Models\Dcm;
use App\Services\Asesmen\AsesmenImportHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DcmImport implements ToCollection, WithHeadingRow
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
            foreach (Dcm::SECTIONS as $letter => $title) {
                $cell = (string) ($row[$letter.'_'.$this->normalize($title)] ?? $this->sectionCell($row, $letter) ?? '');

                if (preg_match_all('/\b([A-Z]\d{2})\b/i', $cell, $m)) {
                    $codes = array_unique(array_map('strtoupper', $m[1]));
                    $valid = array_keys(Dcm::QUESTION_GROUPS[$letter] ?? []);
                    $jawaban[$letter] = array_values(array_intersect($codes, $valid));
                }
            }

            $dcm = new Dcm(['jawaban' => $jawaban]);

            Dcm::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => AsesmenImportHelper::parseTanggal($row['timestamp'] ?? null),
                ],
                [
                    'tahun_ajaran_id' => AsesmenImportHelper::resolveTahunAjaranId($row['tahun_pelajaran'] ?? null),
                    'jawaban' => $jawaban,
                    'masalah_teridentifikasi' => $dcm->masalahSummary(),
                    'kesimpulan' => trim((string) ($row['kesimpulan'] ?? '')) ?: null,
                    'catatan' => trim((string) ($row['catatan'] ?? '')) ?: null,
                ]
            );

            $this->berhasil++;
        }
    }

    private function sectionCell(Collection $row, string $letter): ?string
    {
        foreach ($row as $key => $value) {
            if (preg_match('/^'.preg_quote(strtolower($letter), '/').'[_]/', (string) $key) === 1) {
                return (string) $value;
            }
        }

        return null;
    }

    private function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '_', $text) ?? '';

        return trim($text, '_');
    }
}
