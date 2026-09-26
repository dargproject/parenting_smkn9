<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'admin',
            'kepsek',
            'waka_kurikulum',
            'waka_kesiswaan',
            'guru_bk',
            'wali_kelas',
            'guru_wali',
            'guru_mapel',
            'tatib',
        ] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
