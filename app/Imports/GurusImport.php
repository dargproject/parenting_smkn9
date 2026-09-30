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

    private string $passwordHash;

    public function __construct()
    {
        // Di-hash sekali di sini, bukan per baris: bcrypt sengaja lambat (~100-200ms),
        // ratusan baris x Hash::make() per baris bisa gampang melebihi batas waktu eksekusi PHP.
        $this->passwordHash = Hash::make('password');
    }

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
            'phone' => $row['phone'] ?? null, 'password' => $this->passwordHash, 'is_active' => true,
        ]);
    }
}