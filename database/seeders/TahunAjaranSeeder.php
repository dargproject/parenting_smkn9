<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TahunAjaran::create([
            'kode' => '2026/2027-1',
            'nama' => '2026/2027 Ganjil',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        TahunAjaran::create([
            'kode' => '2026/2027-2',
            'nama' => '2026/2027 Genap',
            'semester' => 'genap',
            'is_active' => false,
        ]);
    }
}
