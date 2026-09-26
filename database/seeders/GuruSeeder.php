<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Random\Engine\Mt19937;
use Random\Randomizer;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'kepsek', 'waka_kurikulum', 'waka_kesiswaan', 'guru_bk', 'wali_kelas', 'guru_wali', 'guru_mapel', 'tatib'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Role ekstra di luar daftar resmi (wali kelas, guru wali, BK) dipakai agar alur demo tetap jalan;
        // penetapan wali kelas/guru wali sebenarnya diatur Waka Kurikulum lewat menu "Wali Kelas & Guru Wali".
        $roleKhusus = [
            'Drs. MOH. GUNTUR SAYEKTI, M.Pd.' => ['kepsek'],
            'IKA BUDI YULIASTINI, M.Pd.' => ['waka_kurikulum', 'wali_kelas', 'guru_wali', 'guru_mapel'],
            'MUHAMMAD AZHAR SYAIFUDIN, S.Pd' => ['waka_kesiswaan', 'guru_mapel'],
            'SITI JULAIKAH, S.Pd.' => ['wali_kelas', 'guru_mapel'],
            'DANNY ARGA ARDANIS, S.T' => ['guru_mapel', 'guru_wali'],
            'FEBRI IRAWAN, S.Pd.' => ['guru_bk', 'guru_mapel'],
        ];

        $daftarGuru = [
            'Drs. MOH. GUNTUR SAYEKTI, M.Pd.', 'SUKIRMAN, S.Pd.', 'FITRIANOZA, S.Si.', 'SITI JULAIKAH, S.Pd.',
            'DYAH RATNASARI, S.Pd', 'IKA BUDI YULIASTINI, M.Pd.', 'NADZIF ULFIAH, S.Pd', 'SUSIYANTI, S.Pd.',
            'GIGIH PERKASA, S.T', 'ARIF KURNIAWAN, S.ST', 'BENING ZULAIKHA NURAINI, S.Psi', 'BAWON ROHMAWATI, S.Psi',
            'BAGYO SUPRAPTO, S.Pd.', 'EDNA AYU INDRIYANI, S.Pd.', 'BASKORO SINGGIH A., S.Pd', 'WINDA SULISTYANA, S.Pd',
            'DEVAGA BETAYOKA, S.Pd', 'WIWIT RIYANTI, S.Pd.', 'E.S. ENDAH WAHYUNING DYAH, S.Pd', 'HABSARI RAHAYU FAKHRUNNIA, S.Pd',
            'RISZKA INDARWATI, S.Pd', 'MUHAMMAD AZHAR SYAIFUDIN, S.Pd', 'Dra. YULIYATI', 'FEBRI IRAWAN, S.Pd.',
            'DIDIK HARIYANTO, S.Si.', 'MAYANG PUSPITARINI, S.Psi', "AHMAT SAMSUL MA'ARIF, S.Pd.", 'NUR LAILA DWI FITRIYAH, S.Pd.',
            'SONY SAIFULLAH PURWANTO, M.Pd.', 'ZOHAN FACHRULL WAHAB, S.Kom', 'IWAN ADI CAHYONO, S.Kom', 'TRI YUANA PUSPITASARI, S.Sn',
            'DODY PURNAMA PUTRA, S.Sn', 'SETYO BAGUS FRISTANTO, S.Pd', 'ANDRIAN DANI IRAWAN, S.Pd', 'ERINTA KUSUMASNINGTYAS, S.Pd',
            'MUFIDA ROYANTI, S.Pd', 'FADJAR TJAHJONO, S.Pd', 'HARIADI SOETRISNO, S.T', 'NUR KHOLIS, S.Pd',
            'TAUFIK AFANDI, S.T', 'ARYA WIDIYA HARDOKO, S.Pd', 'MOH. LATIF RISYDA, S.Pd', 'AHMAT ARIP, S.Pd',
            'RIA AFIANTI, S.Pd.', 'WAHIDAH, M.Pd', 'TATY IRAWATI YOESOEF, S.E', 'YUNICO RUSWILDAWANTO, S.Pd.',
            'SANDI INDAH SARI, S.S', 'LUTVIA UTAMI, S.Pd', 'TRI SUKOWIBOWO, S.T.', 'RINA NOVIANTI, S.Hum.',
            'DANNY ARGA ARDANIS, S.T', 'ARGA DAHANA, S.Pd', 'M. ARFAN AFANDI, S.T', 'WIJANG SUTOHARDI, S.Pd.',
        ];

        $rng = new Randomizer(new Mt19937(2026));
        $terpakai = [];

        foreach ($daftarGuru as $nama) {
            do {
                $lahir = sprintf('%04d%02d%02d', $rng->getInt(1965, 1997), $rng->getInt(1, 12), $rng->getInt(1, 28));
                $tmt = sprintf('%04d%02d', $rng->getInt(1992, 2022), $rng->getInt(1, 12));
                $nip = $lahir.$tmt.$rng->getInt(1, 2).sprintf('%03d', $rng->getInt(1, 999));
            } while (isset($terpakai[$nip]));
            $terpakai[$nip] = true;

            $guru = Guru::create(['nama' => $nama, 'nip' => $nip, 'password' => Hash::make('password')]);
            $guru->roles()->attach(Role::whereIn('name', $roleKhusus[$nama] ?? ['guru_mapel'])->pluck('id'));
        }
    }
}
