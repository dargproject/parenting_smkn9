<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TahunAjaran::create([
            'kode' => '2025/2026-1',
            'nama' => '2025/2026 Ganjil',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        TahunAjaran::create([
            'kode' => '2025/2026-2',
            'nama' => '2025/2026 Genap',
            'semester' => 'genap',
            'is_active' => false,
        ]);
    }
}
