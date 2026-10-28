<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MasterPelanggaran;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiSas;
use App\Models\Pelanggaran;
use App\Models\Peminatan;
use App\Models\Presensi;
use App\Models\PrestasiNonAkademik;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaporParentingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function sebagai(Guru $guru): static
    {
        $roles = $guru->roles->pluck('name')->all();

        return $this->actingAs($guru)->withSession([
            'guru_id' => $guru->id, 'guru_nama' => $guru->nama, 'roles' => $roles, 'role' => $roles[0] ?? '',
        ]);
    }

    public function test_wali_kelas_bisa_cetak_rapor_parenting_siswa_binaannya(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Rapor Uji', 'nip' => '9997201', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji A', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997201', 'nama' => 'Siswa Rapor Uji A', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_guru_wali_bisa_cetak_rapor_parenting_siswa_binaannya(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Rapor Uji', 'nip' => '9997202', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji B', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997202', 'nama' => 'Siswa Rapor Uji B', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_wali_kelas_bisa_lihat_preview_html_rapor_parenting(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Preview Uji', 'nip' => '9997210', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji J', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997210', 'nama' => 'Siswa Preview Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertSee('Siswa Preview Uji');
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type'));
    }

    public function test_guru_ditolak_lihat_preview_rapor_siswa_bukan_binaannya(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Lain Preview Uji', 'nip' => '9997211', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        Kelas::create(['nama_kelas' => 'X Rapor Uji K', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);

        $kelasLain = Kelas::create(['nama_kelas' => 'X Rapor Uji L', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswaLain = Siswa::create(['nis' => '997211', 'nama' => 'Siswa Bukan Binaan Preview Uji', 'kelas_id' => $kelasLain->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswaLain))->assertStatus(403);
    }

    public function test_guru_ditolak_cetak_rapor_siswa_bukan_binaannya(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Lain Rapor Uji', 'nip' => '9997203', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        Kelas::create(['nama_kelas' => 'X Rapor Uji C', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);

        $kelasLain = Kelas::create(['nama_kelas' => 'X Rapor Uji D', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswaLain = Siswa::create(['nis' => '997204', 'nama' => 'Siswa Bukan Binaan Rapor Uji', 'kelas_id' => $kelasLain->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswaLain))->assertStatus(403);
    }

    public function test_rapor_tetap_berhasil_dicetak_dengan_nilai_akademik_per_mapel(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Nilai Rapor Uji', 'nip' => '9997206', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji F', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997206', 'nama' => 'Siswa Nilai Rapor Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Umum Rapor Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MUR-'.uniqid(), 'beban_jp' => 2]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $tp = TujuanPembelajaran::create([
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId,
            'kode' => 'N1', 'deskripsi' => 'TP Rapor Uji', 'urutan' => 1,
        ]);
        NilaiLm::create(['siswa_id' => $siswa->id, 'tujuan_pembelajaran_id' => $tp->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 50]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_mapel_tanpa_nilai_tetap_disertakan_dari_kurikulum_kelas(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Mapel Kosong Uji', 'nip' => '9997207', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji G', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997207', 'nama' => 'Siswa Mapel Kosong Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        // Mapel ditetapkan ke kelas lewat struktur kurikulum, tapi belum ada nilai sama sekali.
        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Belum Dinilai Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MBD-'.uniqid(), 'beban_jp' => 2]);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_mapel_yang_sudah_ada_nilai_tetap_tampil_walau_tanpa_kurikulum_kelas(): void
    {
        // Kasus produksi: kelas belum punya KelasMataPelajaran sama sekali, tapi guru sudah
        // sempat input nilai -- mapel itu harus tetap tampil di rapor, bukan hilang.
        $guru = Guru::create(['nama' => 'Guru Wali Tanpa Kurikulum Uji', 'nip' => '9997209', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji I', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997209', 'nama' => 'Siswa Tanpa Kurikulum Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Sudah Dinilai Tanpa Kurikulum Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MSD-'.uniqid(), 'beban_jp' => 2]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $tp = TujuanPembelajaran::create([
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId,
            'kode' => 'N1', 'deskripsi' => 'TP Tanpa Kurikulum Uji', 'urutan' => 1,
        ]);
        NilaiLm::create(['siswa_id' => $siswa->id, 'tujuan_pembelajaran_id' => $tp->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 80]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_catatan_kedisiplinan_dengan_tindakan_tidak_bikin_error(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Kedisiplinan Uji', 'nip' => '9997208', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji H', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997208', 'nama' => 'Siswa Kedisiplinan Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $master = MasterPelanggaran::firstOrFail();
        Pelanggaran::create([
            'siswa_id' => $siswa->id, 'master_pelanggaran_id' => $master->id, 'tanggal' => now()->toDateString(),
            'tahun_ajaran_id' => $taAktifId, 'kategori' => 'Uji', 'judul' => 'Terlambat masuk kelas',
            'deskripsi' => 'Uji', 'poin' => 5, 'tindakan' => 'Menulis surat pernyataan',
        ]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.cetak', $siswa));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_hanya_mapel_remedial_yang_tampil_di_bagian_akademik(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Remedial Uji', 'nip' => '9997214', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji O', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997214', 'nama' => 'Siswa Remedial Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $mapelKurang = MataPelajaran::create(['nama_mapel' => 'Mapel Kurang Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MKU-'.uniqid(), 'beban_jp' => 2]);
        $mapelBaik = MataPelajaran::create(['nama_mapel' => 'Mapel Baik Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MBU-'.uniqid(), 'beban_jp' => 2]);
        NilaiSas::create(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapelKurang->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 50]);
        NilaiSas::create(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapelBaik->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 90]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertSee('Mapel Kurang Uji');
        $response->assertDontSee('Mapel Baik Uji');
    }

    public function test_prestasi_non_akademik_tampil_di_rapor(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Prestasi Uji', 'nip' => '9997215', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji P', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997215', 'nama' => 'Siswa Prestasi Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        PrestasiNonAkademik::create([
            'siswa_id' => $siswa->id, 'nama_prestasi' => 'Juara 1 Lomba Robotik Uji', 'tingkat' => 'Kota',
            'peringkat' => 'Juara 1', 'tanggal' => now()->toDateString(), 'penyelenggara' => 'Dinas Pendidikan',
        ]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertSee('Juara 1 Lomba Robotik Uji');
    }

    public function test_catatan_wali_kelas_tidak_lagi_tampil_di_bagian_v(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Bagian V Uji', 'nip' => '9997216', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji Q', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997216', 'nama' => 'Siswa Bagian V Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertDontSee('Catatan Wali Kelas:');
        $response->assertSee('Tanggapan / Harapan Orang Tua:');
    }

    public function test_rincian_kehadiran_dan_ketidakhadiran_per_mapel_tampil_di_rapor(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Presensi Uji', 'nip' => '9997217', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji R', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997217', 'nama' => 'Siswa Presensi Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Alpa Uji', 'kategori' => 'Nasional', 'kode_mapel' => 'MAU-'.uniqid(), 'beban_jp' => 2]);
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30', 'ruang' => 'R1',
        ]);

        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(), 'status' => 'H', 'tahun_ajaran_id' => $taAktifId]);
        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->subDay()->toDateString(), 'status' => 'A', 'tahun_ajaran_id' => $taAktifId]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertSee('Alpa (tanpa keterangan): 1');
        $response->assertSee('Mapel Alpa Uji');
        $response->assertSee('Tidak Hadir per Mata Pelajaran');
    }

    public function test_peminatan_yang_ditampilkan_ke_ortu_muncul_di_rapor(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Peminatan Uji', 'nip' => '9997212', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji M', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997212', 'nama' => 'Siswa Peminatan Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        Peminatan::create([
            'siswa_id' => $siswa->id, 'tanggal' => now()->toDateString(), 'jawaban' => [],
            'pilihan1' => 'Logis-Matematik', 'pilihan2' => 'Kinestetik', 'pilihan3' => 'Musikal',
            'catatan' => 'Catatan BK uji', 'tampilkan_ke_ortu' => true,
        ]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertSee('Logis-Matematik');
        $response->assertSee('Catatan BK uji');
    }

    public function test_peminatan_yang_tidak_ditampilkan_ke_ortu_tidak_muncul_di_rapor(): void
    {
        $guru = Guru::create(['nama' => 'Wali Kelas Peminatan Privat Uji', 'nip' => '9997213', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji N', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997213', 'nama' => 'Siswa Peminatan Privat Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        Peminatan::create([
            'siswa_id' => $siswa->id, 'tanggal' => now()->toDateString(), 'jawaban' => [],
            'pilihan1' => 'Naturalis', 'tampilkan_ke_ortu' => false,
        ]);

        $response = $this->sebagai($guru)->get(route('guru.rapor-parenting.preview', $siswa));

        $response->assertOk();
        $response->assertDontSee('Naturalis');
    }

    public function test_portal_merender_menu_cetak_rapor_parenting_untuk_wali_kelas_dan_guru_wali(): void
    {
        $guru = Guru::create(['nama' => 'Dual Role Rapor Uji', 'nip' => '9997205', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rapor Uji E', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $guru->id, 'guru_wali_id' => $guru->id]);
        Siswa::create(['nis' => '997205', 'nama' => 'Siswa Dual Rapor Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $response = $this->sebagai($guru)->get(route('guru.portal'));

        $response->assertOk();
        $response->assertSee('Cetak Rapor Parenting');
        $response->assertSee('Siswa Dual Rapor Uji');
        // Pane kontennya cuma sekali walau guru punya 2 role sekaligus (tidak dobel di-render).
        $this->assertSame(1, substr_count($response->getContent(), 'id="pane-rapor-parenting"'));
    }
}
