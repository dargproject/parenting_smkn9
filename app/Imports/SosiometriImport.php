<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Sosiometri;
use App\Services\Asesmen\AsesmenImportHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SosiometriImport implements ToCollection, WithHeadingRow
{
    public int $berhasil = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;

            $nama = trim((string) ($row['nama_lengkap'] ?? $row['nama_siswa'] ?? ''));
            $kelas = trim((string) ($row['kelas'] ?? ''));

            if ($nama === '' || $kelas === '') {
                $this->errors[] = "Baris {$lineNumber}: kolom Nama Lengkap dan Kelas wajib diisi.";

                continue;
            }

            $siswa = AsesmenImportHelper::resolveSiswa($row['nis'] ?? null, $nama, $kelas);

            if (! $siswa) {
                $this->errors[] = "Baris {$lineNumber}: siswa \"{$nama}\" di kelas \"{$kelas}\" tidak ditemukan.";

                continue;
            }

            DB::transaction(function () use ($siswa, $row, $lineNumber) {
                $sosiometri = Sosiometri::updateOrCreate(
                    ['siswa_id' => $siswa->id, 'tanggal' => AsesmenImportHelper::parseTanggal($row['timestamp'] ?? null)],
                    ['tahun_ajaran_id' => AsesmenImportHelper::resolveTahunAjaranId(null), 'instruksi' => null, 'jumlah_pilihan' => 3]
                );

                $sosiometri->respons()->delete();

                foreach (Sosiometri::PERTANYAAN as $key => $pertanyaan) {
                    $cell = (string) $this->questionColumnValue($row, $key);
                    $names = array_values(array_filter(array_map('trim', preg_split('/[,;]/', $cell, -1, PREG_SPLIT_NO_EMPTY) ?: [])));

                    foreach (array_slice($names, 0, 3) as $urutan => $name) {
                        $dipilih = Siswa::whereRaw('LOWER(nama) = ?', [mb_strtolower($name)])->first();

                        if (! $dipilih) {
                            $this->errors[] = "Baris {$lineNumber}: teman \"{$name}\" ({$key}) tidak ditemukan.";

                            continue;
                        }

                        $sosiometri->respons()->create([
                            'siswa_dipilih_id' => $dipilih->id,
                            'urutan' => $urutan + 1,
                            'pertanyaan' => $key,
                        ]);
                    }
                }
            });

            $this->berhasil++;
        }
    }

    private function questionColumnValue(Collection $row, string $key): mixed
    {
        $num = preg_replace('/[^0-9]/', '', $key);

        foreach ($row as $col => $value) {
            $norm = (string) $col;

            if (preg_match('/^'.preg_quote(strtolower($key), '/').'[_]/', $norm) === 1
                || ($num !== '' && preg_match('/^'.preg_quote($num, '/').'[_]/', $norm) === 1)) {
                return $value;
            }
        }

        return '';
    }
}
