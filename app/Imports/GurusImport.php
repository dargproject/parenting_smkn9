<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GurusImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Guru
    {
        if (empty($row['nip'])) return null;
        return new Guru([
            'nip' => $row['nip'], 'nama' => $row['nama'] ?? '', 'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null, 'password' => Hash::make('password'), 'is_active' => true,
        ]);
    }
}
