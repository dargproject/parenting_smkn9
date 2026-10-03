<?php

namespace App\Imports;

use App\Models\GayaBelajar;
use App\Services\Asesmen\AsesmenImportHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GayaBelajarImport implements ToCollection, WithHeadingRow
{
    public int $berhasil = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $flat = GayaBelajar::flatQuestions();

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

            $jawaban = ['Visual' => [], 'Auditorial' => [], 'Kinestetik' => []];
            foreach ($flat as $no => $question) {
                $value = strtolower(trim((string) $this->questionColumnValue($row, $no)));
                if ($value === 'ya') {
                    $jawaban[$question['group']][] = $question['index'];
                }
            }

            $scores = GayaBelajar::scoresFromJawaban($jawaban);

            GayaBelajar::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => AsesmenImportHelper::parseTanggal($row['timestamp'] ?? null),
                ],
                [
                    'tahun_ajaran_id' => AsesmenImportHelper::resolveTahunAjaranId($row['tahun_pelajaran'] ?? null),
                    'jawaban' => $jawaban,
                    'visual' => $scores['visual'],
                    'auditori' => $scores['auditori'],
                    'kinestetik' => $scores['kinestetik'],
                    'hasil' => trim((string) ($row['hasil'] ?? '')) ?: $this->dominant($scores),
                    'faktor_penghambat' => trim((string) ($row['faktor_penghambat'] ?? '')) ?: null,
                    'faktor_pendukung' => trim((string) ($row['faktor_pendukung'] ?? '')) ?: null,
                ]
            );

            $this->berhasil++;
        }
    }

    private function dominant(array $scores): ?string
    {
        $labels = ['visual' => 'Visual', 'auditori' => 'Auditorial', 'kinestetik' => 'Kinestetik'];
        arsort($scores);
        $top = array_key_first($scores);

        return $scores[$top] > 0 ? $labels[$top] : null;
    }

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
