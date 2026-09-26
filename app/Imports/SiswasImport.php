<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswasImport implements ToModel, WithHeadingRow
{
    public int $berhasil = 0;

    public int $dilewati = 0;

    public function model(array $row): ?Siswa
    {
        if (empty($row['nis'])) {
            return null;
        }

        $nisn = $row['nisn'] ?? null;
        $nipd = $row['nipd'] ?? null;

        $sudahAda = Siswa::where('nis', $row['nis'])->exists()
            || ($nisn && Siswa::where('nisn', $nisn)->exists())
            || ($nipd && Siswa::where('nipd', $nipd)->exists());

        if ($sudahAda || empty($row['kelas_id']) || ! Kelas::whereKey($row['kelas_id'])->exists()) {
            $this->dilewati++;

            return null;
        }

        $this->berhasil++;

        return new Siswa([
            'nis' => $row['nis'], 'nisn' => $nisn, 'nipd' => $nipd,
            'nama' => $row['nama'] ?? '', 'kelas_id' => $row['kelas_id'],
            'password' => Hash::make('password'), 'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null, 'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
        ]);
    }
}