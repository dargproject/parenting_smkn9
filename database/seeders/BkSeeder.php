<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KategoriKasus;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class BkSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = ['Pribadi', 'Belajar', 'Karir', 'Sosial'];
        foreach ($kategoris as $nama) {
            KategoriKasus::firstOrCreate(['nama_kategori' => $nama]);
        }

        $konselor = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->first();
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');
        $siswaDemo = Siswa::inRandomOrder()->limit(3)->get();

        if (! $konselor || ! $tahunAjaranId || $siswaDemo->isEmpty()) {
            return;
        }

        $kategoriBelajar = KategoriKasus::where('nama_kategori', 'Belajar')->first();
        $kategoriPribadi = KategoriKasus::where('nama_kategori', 'Pribadi')->first();
        $kategoriSosial = KategoriKasus::where('nama_kategori', 'Sosial')->first();

        $demoKasus = [
            [
                'siswa_id' => $siswaDemo->get(0)?->id,
                'judul' => 'Konseling Individu',
                'kategori' => 'Normal',
                'kategori_id' => $kategoriBelajar?->id,
                'deskripsi' => 'Siswa kesulitan mengikuti pelajaran matematika dan sering terlambat mengumpulkan tugas.',
                'status' => 'antrean',
                'prioritas' => 'sedang',
                'tanggal_mulai' => now()->subDays(2)->toDateString(),
            ],
            [
                'siswa_id' => $siswaDemo->get(1)?->id,
                'judul' => 'Konseling Individu',
                'kategori' => 'Mendesak',
                'kategori_id' => $kategoriPribadi?->id,
                'deskripsi' => 'Siswa menunjukkan perubahan perilaku dan tampak murung selama dua minggu terakhir.',
                'status' => 'proses',
                'prioritas' => 'tinggi',
                'tanggal_mulai' => now()->subDays(5)->toDateString(),
                'tindak_lanjut' => 'Sudah dilakukan sesi konseling pertama, dijadwalkan sesi lanjutan minggu depan.',
            ],
            [
                'siswa_id' => $siswaDemo->get(2)?->id ?? $siswaDemo->get(0)?->id,
                'judul' => 'Konseling Individu',
                'kategori' => 'Rujukan Wali',
                'kategori_id' => $kategoriSosial?->id,
                'deskripsi' => 'Rujukan dari wali kelas terkait konflik pertemanan di kelas.',
                'status' => 'selesai',
                'prioritas' => 'rendah',
                'tanggal_mulai' => now()->subDays(14)->toDateString(),
                'tanggal_selesai' => now()->subDays(10)->toDateString(),
                'tindak_lanjut' => 'Konflik sudah mereda, siswa dan teman-temannya sudah berbaikan.',
            ],
        ];

        foreach ($demoKasus as $data) {
            if (! $data['siswa_id']) {
                continue;
            }

            KasusBk::updateOrCreate(
                ['siswa_id' => $data['siswa_id'], 'judul' => $data['judul'], 'tahun_ajaran_id' => $tahunAjaranId],
                $data + ['tahun_ajaran_id' => $tahunAjaranId, 'konselor_id' => $konselor->id]
            );
        }
    }
}
