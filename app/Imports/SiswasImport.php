<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswasImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Siswa
    {
        if (empty($row['nis'])) return null;
        return new Siswa([
            'nis' => $row['nis'], 'nisn' => $row['nisn'] ?? null, 'nipd' => $row['nipd'] ?? null,
            'nama' => $row['nama'] ?? '', 'kelas_id' => $row['kelas_id'] ?? null,
            'password' => Hash::make('password'), 'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null, 'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
        ]);
    }
}
