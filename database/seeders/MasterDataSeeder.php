<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\Pelanggaran;
use App\Models\KasusBk;
use App\Models\PanggilanOrtu;
use App\Models\Pengumuman;
use App\Models\JurnalMengajar;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MasterPelanggaran;
use App\Models\Pasal;
use App\Models\JenisPelanggaran;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pakHendra = Guru::where('nama', 'like', '%IKA BUDI%')->first();
        $ibuSiti = Guru::where('nama', 'like', '%SITI JULAIKAH%')->first();
        $pakDanny = Guru::where('nama', 'like', '%DANNY ARGA%')->first();
        $pakAgus = Guru::where('nama', 'like', '%FEBRI IRAWAN%')->first();

        $kelas = Kelas::where('nama_kelas', 'XI TKJ 1')->first();
        $siswas = Siswa::where('kelas_id', $kelas->id)->get();

        // 1. Mata Pelajaran
        $mapelJaringan = MataPelajaran::create([
            'nama_mapel' => 'Administrasi Infrastruktur Jaringan',
            'kategori' => 'Kejuruan',
            'beban_jp' => 6,
            'guru_id' => $pakHendra->id,
        ]);

        $mapelPemrograman = MataPelajaran::create([
            'nama_mapel' => 'Pemrograman Web & Perangkat Bergerak',
            'kategori' => 'Kejuruan',
            'beban_jp' => 6,
            'guru_id' => $pakDanny->id,
        ]);

        // 2. Jadwal Pelajaran
        $jadwalJaringan = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapelJaringan->id,
            'guru_id' => $pakHendra->id,
            'hari' => 'Selasa',
            'jam_mulai' => '08:30',
            'jam_selesai' => '09:15',
            'ruang' => 'Lab RPS 1',
        ]);

        $jadwalPemrograman = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapelPemrograman->id,
            'guru_id' => $pakDanny->id,
            'hari' => 'Selasa',
            'jam_mulai' => '07:00',
            'jam_selesai' => '07:45',
            'ruang' => 'Lab RPS 1',
        ]);

        // 4. Presensi
        foreach ($siswas as $siswa) {
            $status = 'H';
            if ($siswa->nis == '1004') $status = 'S';
            if ($siswa->nis == '1005') $status = 'A';

            Presensi::create([
                'siswa_id' => $siswa->id,
                'jadwal_pelajaran_id' => $jadwalJaringan->id,
                'tanggal' => Carbon::now()->toDateString(),
                'status' => $status,
                'keterangan' => $status == 'A' ? 'Tanpa Keterangan' : null,
                'is_verified' => $status == 'A' ? false : true,
                'verification_status' => $status == 'A' ? 'Pending Review' : 'Verified Valid',
            ]);
        }

        // 5. Katalog Pelanggaran (Tata Tertib)
        $pasals = [
            1 => Pasal::create(['kode' => 'PASAL_1', 'nama' => 'Kerajinan']),
            2 => Pasal::create(['kode' => 'PASAL_2', 'nama' => 'Kerapian']),
            3 => Pasal::create(['kode' => 'PASAL_3', 'nama' => 'Kepribadian']),
            4 => Pasal::create(['kode' => 'PASAL_4', 'nama' => 'Ketertiban']),
            5 => Pasal::create(['kode' => 'PASAL_5', 'nama' => 'Sikap Terhadap Sekolah, Kepala Sekolah, Guru, Pegawai']),
        ];

        $jenisPelanggarans = [
            1 => JenisPelanggaran::create(['kode' => 'HARIAN', 'nama' => 'Harian', 'poin' => 1]),
            2 => JenisPelanggaran::create(['kode' => 'KHUSUS', 'nama' => 'Khusus', 'poin' => 3]),
            3 => JenisPelanggaran::create(['kode' => 'BERAT', 'nama' => 'Berat', 'poin' => 10]),
        ];

        $tatibList = [
            [1, 1, 'Terlambat masuk sekolah'],
            [1, 1, 'Tidak mengikuti jam pelajaran'],
            [1, 1, 'Terlambat karena ijin keluar'],
            [1, 1, 'Ijin keluar bukan kegiatan sekolah'],
            [1, 1, 'Tidak masuk karena ijin'],
            [1, 1, 'Ijin keluar kelas tidak kembali lagi'],
            [1, 1, 'Keluar kelas tanpa ijin dan tidak kembali'],
            [1, 2, 'Tidak masuk sekolah tanpa keterangan'],
            [1, 2, 'Tidak masuk dengan keterangan palsu'],
            [1, 2, 'Pulang sebelum waktunya tanpa keterangan'],
            [1, 2, 'Tidak mengikuti kegiatan upacara bendera'],
            [1, 2, 'Tidak mengikuti kegiatan sekolah (PHBN, PHBI, dll)'],
            [1, 2, 'Tidak mengikuti kegiatan keagamaan di sekolah'],
            [2, 1, 'Tidak memakai seragam sesuai ketentuan'],
            [2, 1, 'Atribut sekolah tidak lengkap'],
            [2, 1, 'Tidak memasukkan baju bagi laki-laki'],
            [2, 1, 'Memakai sepatu dengan warna selain hitam (bertali selain hitam)'],
            [2, 1, 'Memakai topi di lingkungan sekolah (bukan topi sekolah)'],
            [2, 1, 'Memakai jaket dan sejenisnya selain jaket organisasi resmi sekolah'],
            [2, 1, 'Memakai sandal, sepatu sandal bukan karena sakit'],
            [2, 2, 'Mengubah bentuk seragam'],
            [2, 2, 'Memakai atribut sekolah lain'],
            [2, 2, 'Memakai perhiasan/aksesoris berlebihan'],
            [2, 2, 'Membawa/memakai make up berlebihan'],
            [2, 2, 'Murid berambut panjang/gondrong/model'],
            [2, 2, 'Murid mewarna rambut'],
            [2, 2, 'Mencat kuku tangan/kaki'],
            [2, 3, 'Bertato dan bertindik'],
            [3, 1, 'Tidak melaksanakan piket kelas'],
            [3, 1, 'Membuang sampah sembarangan'],
            [3, 1, 'Merusak tanaman hias atau di taman'],
            [3, 2, 'Bermesraan di lingkungan sekolah dan sekitarnya'],
            [3, 2, 'Merusak inventaris sekolah'],
            [3, 2, 'Mencorat-coret dinding, meja, kursi, kaca, pintu milik sekolah'],
            [3, 2, 'Mencorat-coret buku paket, jurnal, data absensi milik sekolah'],
            [3, 3, 'Melakukan tindakan asusila'],
            [3, 3, 'Mencuri/mengambil dengan paksa milik orang lain'],
            [3, 3, 'Merusak/menghilangkan barang milik guru, pegawai dan teman'],
            [4, 1, 'Mengaktifkan HP, MP3, portable dan sejenisnya saat jam pelajaran (bukan untuk kepentingan pelajaran yang telah diijinkan oleh guru mapel)'],
            [4, 1, 'Mencharger HP, MP3, portabel dan sejenisnya di sekolah, kecuali media pembelajaran seperti laptop, tablet'],
            [4, 1, 'Menerima tamu tanpa ijin guru piket/pihak sekolah'],
            [4, 1, 'Menerima tamu di luar lingkungan sekolah'],
            [4, 1, 'Naik kendaraan keluar/masuk sekolah'],
            [4, 2, 'Membawa rokok sendiri/titipan'],
            [4, 3, 'Menghisap rokok di lingkungan sekolah dan sekitarnya'],
            [4, 3, 'Memperjualbelikan rokok'],
            [4, 2, 'Membawa majalah, VCD, DVD, video porno milik sendiri atau titipan'],
            [4, 2, 'Membagikan gambar/video porno melalui HP'],
            [4, 2, 'Membawa dan menggunakan senjata yang membahayakan orang lain'],
            [4, 2, 'Membawa, menggunakan alat-alat perjudian'],
            [4, 2, 'Terlibat dalam perjudian dan sejenisnya'],
            [4, 2, 'Mengganggu kelas yang sedang belajar'],
            [4, 2, 'Ditemukan di luar sekolah saat jam pelajaran'],
            [4, 2, 'Naik kendaraan ugal-ugalan di lingkungan sekolah'],
            [4, 2, 'Melakukan kegiatan atas nama sekolah tanpa ijin sekolah'],
            [4, 3, 'Membawa, memperjualbelikan, menggunakan narkotika dan sejenisnya'],
            [4, 3, 'Berkelahi di lingkungan sekolah dan sekitarnya'],
            [4, 3, 'Menghasut, memprovokasi tindakan perkelahian'],
            [4, 3, 'Terlibat perkelahian di sekolah dan sekitarnya'],
            [4, 3, 'Terlibat tawuran pelajar'],
            [4, 3, 'Mengkoordinir, memprovokasi tindakan menentang sekolah'],
            [4, 3, 'Terlibat dalam organisasi terlarang di luar sekolah'],
            [5, 3, 'Memalsukan tanda tangan kepala sekolah, guru, pegawai'],
            [5, 3, 'Memalsukan stempel sekolah'],
            [5, 3, 'Membuat surat sekolah palsu'],
            [5, 3, 'Melawan kepala sekolah, guru, pegawai dengan ucapan kasar'],
            [5, 3, 'Melawan kepala sekolah, guru, pegawai dengan tindakan'],
            [5, 3, 'Melawan kepala sekolah, guru, pegawai disertai ancaman'],
        ];

        foreach ($tatibList as [$pasalKode, $jenisKode, $namaPelanggaran]) {
            MasterPelanggaran::create([
                'pasal_id' => $pasals[$pasalKode]->id,
                'jenis_id' => $jenisPelanggarans[$jenisKode]->id,
                'nama_pelanggaran' => $namaPelanggaran,
                'is_active' => true,
            ]);
        }

        $tidakMengikutiJamPelajaran = MasterPelanggaran::where('nama_pelanggaran', 'Tidak mengikuti jam pelajaran')->first();

        // 6. Pelanggaran
        $dodi = $siswas->where('nis', '1004')->first();
        if ($dodi) {
            Pelanggaran::create([
                'siswa_id' => $dodi->id,
                'master_pelanggaran_id' => $tidakMengikutiJamPelajaran?->id,
                'tanggal' => Carbon::now()->subDays(5)->toDateString(),
                'kategori' => $jenisPelanggarans[1]->nama,
                'judul' => 'Tidak mengikuti jam pelajaran',
                'deskripsi' => 'Meninggalkan area kelas sebelum jam berakhir.',
                'poin' => $jenisPelanggarans[1]->poin,
                'pelapor_id' => $pakHendra->id,
            ]);
        }

        // 6. Kasus BK
        if ($dodi && $pakAgus) {
            KasusBk::create([
                'siswa_id' => $dodi->id,
                'judul' => 'Kasus Perundungan Siber',
                'kategori' => 'Mendesak',
                'deskripsi' => 'Laporan dari teman sekelas terkait cyberbullying.',
                'status' => 'antrean',
                'konselor_id' => $pakAgus->id,
            ]);
        }

        // 7. Panggilan Ortu
        $eko = $siswas->where('nis', '1005')->first();
        if ($eko) {
            PanggilanOrtu::create([
                'siswa_id' => $eko->id,
                'tanggal' => Carbon::now()->addDays(2)->toDateString(),
                'waktu' => '09:00:00',
                'ruang' => 'Ruang BK',
                'alasan' => 'Akumulasi ketidakhadiran (Alpa) tinggi terdeteksi di sistem absensi.',
                'status' => 'Menunggu Konfirmasi',
                'pemanggil_id' => $ibuSiti->id,
            ]);
        }

        // 8. Pengumuman
        Pengumuman::create([
            'judul' => 'Ujian Tengah Semester (UTS)',
            'deskripsi' => 'Pengumuman jadwal UTS Ganjil dimulai tanggal 1 September 2026. Kartu ujian dapat diunduh di portal.',
            'kategori' => 'Akademik',
            'pembuat_id' => $pakHendra->id,
        ]);

        // 9. Jurnal Mengajar
        JurnalMengajar::create([
            'jadwal_pelajaran_id' => $jadwalJaringan->id,
            'tanggal' => Carbon::now()->toDateString(),
            'materi' => 'Routing Statis Cisco Packet Tracer',
            'foto' => 'Mengajar_Jaringan.jpg',
            'guru_id' => $pakHendra->id,
        ]);
    }
}
