<?php

namespace Database\Seeders;

use App\Models\CatatanKompetensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiPklUkk;
use App\Models\NilaiSas;
use App\Models\OrangTua;
use App\Models\RaporFinal;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenilaianSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'bobot_lm'], ['value' => 60]);
        Setting::updateOrCreate(['key' => 'bobot_sas'], ['value' => 40]);
        Setting::updateOrCreate(['key' => 'kktp_threshold'], ['value' => 75]);

        $ta = TahunAjaran::where('is_active', true)->first();
        $pakHendra = Guru::where('nama', 'like', '%IKA BUDI%')->first();
        $mapelJaringan = MataPelajaran::where('nama_mapel', 'like', '%Jaringan%')->first();
        $kelasXiTkj1 = Kelas::where('nama_kelas', 'XI TKJ 1')->first();

        if (! $ta || ! $pakHendra || ! $mapelJaringan || ! $kelasXiTkj1) {
            return;
        }

        $tp1 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapelJaringan->id, 'tahun_ajaran_id' => $ta->id, 'kode' => 'TP 3.1', 'deskripsi' => 'Konfigurasi Router Mikrotik', 'urutan' => 1]);
        $tp2 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapelJaringan->id, 'tahun_ajaran_id' => $ta->id, 'kode' => 'TP 3.2', 'deskripsi' => 'Instalasi Server Linux', 'urutan' => 2]);

        $siswaXiTkj1 = Siswa::where('kelas_id', $kelasXiTkj1->id)->get();

        foreach ($siswaXiTkj1 as $siswa) {
            // NIS 1004 (Dodi) sengaja rendah untuk mendemokan status remedial.
            $skor1 = $siswa->nis === '1004' ? 60 : rand(75, 95);
            $skor2 = $siswa->nis === '1004' ? 65 : rand(75, 95);

            NilaiLm::create(['siswa_id' => $siswa->id, 'tujuan_pembelajaran_id' => $tp1->id, 'tahun_ajaran_id' => $ta->id, 'nilai' => $skor1, 'guru_id' => $pakHendra->id]);
            NilaiLm::create(['siswa_id' => $siswa->id, 'tujuan_pembelajaran_id' => $tp2->id, 'tahun_ajaran_id' => $ta->id, 'nilai' => $skor2, 'guru_id' => $pakHendra->id]);

            // NIS 1005 (Eko) sengaja belum ada SAS untuk mendemokan status "belum lengkap".
            if ($siswa->nis !== '1005') {
                NilaiSas::create(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapelJaringan->id, 'tahun_ajaran_id' => $ta->id, 'nilai' => (int) round(($skor1 + $skor2) / 2), 'guru_id' => $pakHendra->id]);
            }

            CatatanKompetensi::create([
                'siswa_id' => $siswa->id,
                'mata_pelajaran_id' => $mapelJaringan->id,
                'tahun_ajaran_id' => $ta->id,
                'catatan' => 'Sudah menguasai konfigurasi dasar jaringan, perlu latihan lanjutan untuk manajemen VLAN.',
                'guru_id' => $pakHendra->id,
            ]);

            NilaiPklUkk::create(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapelJaringan->id, 'tahun_ajaran_id' => $ta->id, 'jenis' => 'pkl', 'nilai' => rand(75, 95), 'guru_id' => $pakHendra->id]);
        }

        // Andi Susanto (NIS 1001): rapor sudah dirilis + akun orang tua demo.
        $andi = $siswaXiTkj1->firstWhere('nis', '1001');
        if ($andi) {
            RaporFinal::updateOrCreate(
                ['siswa_id' => $andi->id, 'tahun_ajaran_id' => $ta->id],
                ['status' => 'final', 'dirilis_at' => now(), 'dirilis_oleh' => $pakHendra->id]
            );

            OrangTua::updateOrCreate(
                ['username' => 'ortu.andi'],
                ['siswa_id' => $andi->id, 'nama' => 'Bpk. Susanto', 'password' => Hash::make('password'), 'is_active' => true]
            );
        }
    }
}
