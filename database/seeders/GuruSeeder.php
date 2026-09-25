<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat roles
        $roles = [
            'admin',
            'kepsek',
            'waka_kurikulum',
            'waka_kesiswaan',
            'guru_bk',
            'wali_kelas',
            'guru_wali',
            'guru_mapel',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Buat Guru
        $pakHendra = Guru::create([
            'nama' => 'Bpk. Hendra Wijaya, S.T',
            'nip' => '198001012005011001',
            'password' => Hash::make('password'),
        ]);

        // Pak Hendra memiliki 4 role sekaligus (termasuk guru wali)
        $pakHendra->roles()->attach(Role::whereIn('name', ['waka_kurikulum', 'wali_kelas', 'guru_wali', 'guru_mapel'])->pluck('id'));

        $ibuSiti = Guru::create([
            'nama' => 'Ibu Siti Rahmawati, S.Kom',
            'nip' => '198502022010012002',
            'password' => Hash::make('password'),
        ]);
        $ibuSiti->roles()->attach(Role::whereIn('name', ['wali_kelas', 'guru_mapel'])->pluck('id'));

        $pakDanny = Guru::create([
            'nama' => 'Pak Danny, S.T',
            'nip' => '199003032015011003',
            'password' => Hash::make('password'),
        ]);
        // Pak Danny: guru mapel sekaligus guru wali (kelas XI RPL 2)
        $pakDanny->roles()->attach(Role::whereIn('name', ['guru_mapel', 'guru_wali'])->pluck('id'));

        // Kepala Sekolah
        $kepsek = Guru::create([
            'nama' => 'Bpk. Suharto, S.Pd., M.M',
            'nip' => '196505051990011001',
            'password' => Hash::make('password'),
        ]);
        $kepsek->roles()->attach(Role::whereIn('name', ['kepsek'])->pluck('id'));

        // Waka Kesiswaan
        $bukRina = Guru::create([
            'nama' => 'Ibu Rina Kartika, S.Pd',
            'nip' => '198209152006042004',
            'password' => Hash::make('password'),
        ]);
        $bukRina->roles()->attach(Role::whereIn('name', ['waka_kesiswaan'])->pluck('id'));

        // Guru BK (multi-role: guru BK sekaligus guru mapel)
        $pakAgus = Guru::create([
            'nama' => 'Bpk. Agus Setiawan, S.Pd',
            'nip' => '198711202012011005',
            'password' => Hash::make('password'),
        ]);
        $pakAgus->roles()->attach(Role::whereIn('name', ['guru_bk', 'guru_mapel'])->pluck('id'));
    }
}
