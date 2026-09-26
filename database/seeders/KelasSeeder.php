<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Guru;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ibuSiti = Guru::where('nama', 'like', '%SITI JULAIKAH%')->first();
        $pakHendra = Guru::where('nama', 'like', '%IKA BUDI%')->first();
        $pakDanny = Guru::where('nama', 'like', '%DANNY ARGA%')->first();

        $kelasList = [
            ['nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'jurusan' => 'TKJ'],
            ['nama_kelas' => 'XI RPL 2', 'tingkat' => 'XI', 'jurusan' => 'RPL', 'guru_wali' => $pakDanny],
            ['nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL'],
            ['nama_kelas' => 'X RPL 2', 'tingkat' => 'X', 'jurusan' => 'RPL'],
            ['nama_kelas' => 'X ANM 1', 'tingkat' => 'X', 'jurusan' => 'ANM'],
            ['nama_kelas' => 'X ANM 2', 'tingkat' => 'X', 'jurusan' => 'ANM'],
            ['nama_kelas' => 'X TBSM HONDA 1', 'tingkat' => 'X', 'jurusan' => 'TBSM'],
            ['nama_kelas' => 'X TBSM HONDA 2', 'tingkat' => 'X', 'jurusan' => 'TBSM'],
            ['nama_kelas' => 'X TBSM HONDA 3', 'tingkat' => 'X', 'jurusan' => 'TBSM'],
            ['nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ'],
            ['nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'jurusan' => 'TKJ'],
            ['nama_kelas' => 'X TEI', 'tingkat' => 'X', 'jurusan' => 'TEI'],
        ];

        foreach ($kelasList as $k) {
            Kelas::create([
                'nama_kelas' => $k['nama_kelas'],
                'tingkat' => $k['tingkat'],
                'jurusan' => $k['jurusan'],
                'wali_kelas_id' => $ibuSiti ? $ibuSiti->id : null,
                'guru_wali_id' => ($k['guru_wali'] ?? $pakHendra)?->id,
            ]);
        }
    }
}
