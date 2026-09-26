<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $kelasList = Kelas::all();

        $nisCounter = 1001;

        foreach ($kelasList as $kelas) {
            if ($kelas->nama_kelas === 'XI RPL 2') {
                continue; // diisi terpisah setelah loop (lihat siswaXiRpl2)
            }

            if ($kelas->nama_kelas === 'XI TKJ 1') {
                // Data spesifik untuk XI TKJ 1 agar sesuai dengan mockup
                Siswa::create([
                    'nis' => (string) $nisCounter++,
                    'nama' => 'Andi Susanto',
                    'kelas_id' => $kelas->id,
                    'password' => Hash::make('password'),
                    'catatan_wali' => 'Andi menunjukkan kepemimpinan yang baik dan prestasi akademik menonjol semester ini. Pertahankan!',
                    'catatan_akademik' => 'Kemampuan konfigurasi Cisco di atas rata-rata. Direkomendasikan sertifikasi CCNA.',
                    'counseling_stress' => 3,
                    'counseling_career' => 'Bekerja di Industri',
                    'counseling_note' => 'Termotivasi tinggi untuk bekerja setelah lulus. Sedang mempersiapkan portofolio jaringan.',
                    'total_alpa' => 1,
                    'total_violation_points' => 10,
                ]);

                Siswa::create([
                    'nis' => (string) $nisCounter++,
                    'nama' => 'Bunga Citra',
                    'kelas_id' => $kelas->id,
                    'password' => Hash::make('password'),
                    'catatan_wali' => 'Siswa aktif, ramah, dan mematuhi seluruh peraturan tata tertib sekolah.',
                    'catatan_akademik' => 'Ketekunan dalam menyelesaikan tugas harian sangat baik, namun perlu peningkatan keberanian saat sesi diskusi.',
                    'counseling_stress' => 4,
                    'counseling_career' => 'Melanjutkan Kuliah',
                    'counseling_note' => 'Memiliki minat di bidang teknik informatika, didorong untuk mempersiapkan jalur prestasi SNBP.',
                    'total_alpa' => 0,
                    'total_violation_points' => 0,
                ]);

                Siswa::create([
                    'nis' => (string) $nisCounter++,
                    'nama' => 'Caca Marica',
                    'kelas_id' => $kelas->id,
                    'password' => Hash::make('password'),
                    'catatan_wali' => 'Pertahankan prestasi akademik, serta keterlibatan aktif dalam kegiatan OSIS.',
                    'catatan_akademik' => 'Prestasi akademik stabil. Pemahaman materi perancangan layout web responsif sangat baik.',
                    'counseling_stress' => 2,
                    'counseling_career' => 'Wirausaha Mandiri',
                    'counseling_note' => 'Sangat berminat pada UI/UX freelancing. Sudah mulai merintis projek kecil.',
                    'total_alpa' => 0,
                    'total_violation_points' => 0,
                ]);

                Siswa::create([
                    'nis' => (string) $nisCounter++,
                    'nama' => 'Dodi Hermawan',
                    'kelas_id' => $kelas->id,
                    'password' => Hash::make('password'),
                    'catatan_wali' => 'Butuh pendampingan intensif dari orang tua untuk meningkatkan kepedulian jam belajar di rumah.',
                    'catatan_akademik' => 'Nilai di beberapa mata pelajaran kejuruan produktif masih di bawah KKM. Memerlukan remedial terpadu.',
                    'counseling_stress' => 8,
                    'counseling_career' => 'Bekerja di Industri',
                    'counseling_note' => 'Mengalami tekanan emosional karena tertinggal materi produktif. Konselor menjadwalkan remedial khusus.',
                    'total_alpa' => 4,
                    'total_violation_points' => 35,
                ]);

                Siswa::create([
                    'nis' => (string) $nisCounter++,
                    'nama' => 'Eko Saputro',
                    'kelas_id' => $kelas->id,
                    'password' => Hash::make('password'),
                    'catatan_wali' => 'Eko sering membolos tanpa alasan yang jelas. Pihak sekolah telah menerbitkan Surat Panggilan Ortu 1.',
                    'catatan_akademik' => 'Cukup mahir dalam praktik perakitan PC, perlu meningkatkan kehadiran di kelas teori.',
                    'counseling_stress' => 7,
                    'counseling_career' => 'Wirausaha Mandiri',
                    'counseling_note' => 'Mengaku kurang berminat di teori, lebih menyukai praktikum lapangan. BK mengarahkan pemahaman regulasi industri.',
                    'total_alpa' => 5,
                    'total_violation_points' => 50,
                ]);
            } else {
                // Generate 5 random students for other classes
                for ($i = 0; $i < 5; $i++) {
                    Siswa::create([
                        'nis' => (string) $nisCounter++,
                        'nama' => $faker->name,
                        'kelas_id' => $kelas->id,
                        'password' => Hash::make('password'),
                        'catatan_wali' => $faker->sentence,
                        'catatan_akademik' => $faker->sentence,
                        'counseling_stress' => rand(1, 5),
                        'counseling_career' => $faker->randomElement(['Bekerja di Industri', 'Melanjutkan Kuliah', 'Wirausaha Mandiri']),
                        'counseling_note' => $faker->sentence,
                        'total_alpa' => rand(0, 2),
                        'total_violation_points' => rand(0, 15),
                    ]);
                }
            }
        }

        // Kelas binaan Pak Danny (guru wali) - XI RPL 2
        $xiRpl2 = Kelas::where('nama_kelas', 'XI RPL 2')->first();
        if ($xiRpl2) {
            foreach ($this->siswaXiRpl2() as $data) {
                Siswa::create($data + [
                    'nis' => (string) $nisCounter++,
                    'kelas_id' => $xiRpl2->id,
                    'password' => Hash::make('password'),
                ]);
            }
        }
    }

    private function siswaXiRpl2(): array
    {
        return [
            [
                'nama' => 'Rizky Ramadhan',
                'catatan_wali' => 'Aktif di kegiatan ekskul dan mudah bekerja sama dalam kelompok.',
                'catatan_akademik' => 'Logika pemrograman dasar sangat baik, perlu latihan lebih pada basis data.',
                'counseling_stress' => 2,
                'counseling_career' => 'Bekerja di Industri',
                'counseling_note' => 'Berminat menjadi web developer setelah lulus.',
                'total_alpa' => 0,
                'total_violation_points' => 0,
            ],
            [
                'nama' => 'Nadia Putri Anggraini',
                'catatan_wali' => 'Rajin, disiplin, dan sering membantu teman yang kesulitan belajar.',
                'catatan_akademik' => 'Nilai konsisten di atas KKM pada seluruh mata pelajaran produktif.',
                'counseling_stress' => 3,
                'counseling_career' => 'Melanjutkan Kuliah',
                'counseling_note' => 'Menargetkan jalur prestasi ke jurusan Teknik Informatika.',
                'total_alpa' => 0,
                'total_violation_points' => 0,
            ],
            [
                'nama' => 'Fajar Setiawan',
                'catatan_wali' => 'Perlu pendampingan agar lebih tepat waktu saat masuk sekolah.',
                'catatan_akademik' => 'Nilai praktik baik, namun tugas teori sering terlambat dikumpulkan.',
                'counseling_stress' => 5,
                'counseling_career' => 'Wirausaha Mandiri',
                'counseling_note' => 'Tertarik membuka jasa pembuatan website untuk UMKM.',
                'total_alpa' => 2,
                'total_violation_points' => 5,
            ],
            [
                'nama' => 'Salsabila Azzahra',
                'catatan_wali' => 'Santun dan aktif bertanya di kelas.',
                'catatan_akademik' => 'Pemahaman desain antarmuka sangat baik, perlu memperkuat logika algoritma.',
                'counseling_stress' => 3,
                'counseling_career' => 'Melanjutkan Kuliah',
                'counseling_note' => 'Tertarik pada bidang UI/UX.',
                'total_alpa' => 1,
                'total_violation_points' => 0,
            ],
            [
                'nama' => 'Dimas Prasetyo',
                'catatan_wali' => 'Cenderung pendiam, perlu didorong lebih aktif dalam diskusi.',
                'catatan_akademik' => 'Nilai berada di sekitar KKM, disarankan mengikuti remedial pada mapel basis data.',
                'counseling_stress' => 6,
                'counseling_career' => 'Bekerja di Industri',
                'counseling_note' => 'Ingin magang di perusahaan software setelah kelas XII.',
                'total_alpa' => 1,
                'total_violation_points' => 10,
            ],
        ];
    }
}
