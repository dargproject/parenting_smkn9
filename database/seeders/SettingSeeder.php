<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\TahunAjaran;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();

        $settings = [
            ['key' => 'nama_sekolah', 'value' => 'SMK Negeri 9 Malang'],
            ['key' => 'tahun_ajaran_aktif_id', 'value' => $tahunAjaranAktif ? $tahunAjaranAktif->id : null],
            ['key' => 'semester_aktif', 'value' => $tahunAjaranAktif ? $tahunAjaranAktif->semester : 'ganjil'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
