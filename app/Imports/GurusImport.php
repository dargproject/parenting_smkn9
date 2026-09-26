<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GurusImport implements ToModel, WithHeadingRow
{
    public int $berhasil = 0;

    public int $dilewati = 0;

    public function model(array $row): ?Guru
    {
        if (empty($row['nip'])) {
            return null;
        }

        $email = $row['email'] ?? null;
        if (Guru::where('nip', $row['nip'])->exists() || ($email && Guru::where('email', $email)->exists())) {
            $this->dilewati++;

            return null;
        }

        $this->berhasil++;

        return new Guru([
            'nip' => $row['nip'], 'nama' => $row['nama'] ?? '', 'email' => $email,
            'phone' => $row['phone'] ?? null, 'password' => Hash::make('password'), 'is_active' => true,
        ]);
    }
}