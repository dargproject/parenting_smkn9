<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\MasterPelanggaran;
use App\Models\Pasal;
use App\Models\Pelanggaran;
use App\Models\PenguranganPoin;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\SanksiService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanksiTatibTest extends TestCase
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
            'guru_id' => $guru->id,
            'guru_nama' => $guru->nama,
            'roles' => $roles,
            'role' => $roles[0] ?? '',
        ]);
    }

    private function tatib(): Guru
    {
        $guru = Guru::create(['nama' => 'Petugas Tatib Uji', 'nip' => '9996801', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'tatib'])->id);

        return $guru;
    }

    private function kelas(): Kelas
    {
        return Kelas::firstOrCreate(['nama_kelas' => 'X Uji Sanksi'], ['tingkat' => 'X', 'jurusan' => 'Uji']);
    }

    private function masterPelanggaran(int $poin): MasterPelanggaran
    {
        $jenis = JenisPelanggaran::create(['kode' => 'J-'.$poin.'-'.uniqid(), 'nama' => "Jenis $poin poin", 'poin' => $poin]);
        $pasal = Pasal::create(['kode' => 'P-'.uniqid(), 'nama' => 'Pasal Uji']);

        return MasterPelanggaran::create([
            'nama_pelanggaran' => "Pelanggaran $poin poin ".uniqid(),
            'pasal_id' => $pasal->id,
            'jenis_id' => $jenis->id,
            'is_active' => true,
        ]);
    }

    public function test_status_semester_sesuai_ambang_kebijakan(): void
    {
        $sanksi = new SanksiService;

        $this->assertNull($sanksi->statusSemester(9)['kode']);
        $this->assertSame('SP1', $sanksi->statusSemester(10)['kode']);
        $this->assertSame('SP1', $sanksi->statusSemester(23)['kode']);
        $this->assertSame('SP2', $sanksi->statusSemester(24)['kode']);
        $this->assertSame('SP2', $sanksi->statusSemester(39)['kode']);
        $this->assertSame('SP3', $sanksi->statusSemester(40)['kode']);
        $this->assertSame('SP3', $sanksi->statusSemester(49)['kode']);
        $this->assertSame('DO', $sanksi->statusSemester(50)['kode']);
    }

    public function test_status_harian_sesuai_ambang_kebijakan(): void
    {
        $sanksi = new SanksiService;

        $this->assertNull($sanksi->statusHarian(2));
        $this->assertSame('P1', $sanksi->statusHarian(3)['kode']);
        $this->assertSame('P2', $sanksi->statusHarian(6)['kode']);
    }

    public function test_catat_pelanggaran_dengan_tindakan_mencatat_siapa_yang_menindak(): void
    {
        $tatib = $this->tatib();
        $siswa = Siswa::create(['nis' => '996801', 'nama' => 'Siswa Tindakan Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $master = $this->masterPelanggaran(10);

        $this->sebagai($tatib)->post(route('guru.pelanggaran.store'), [
            'siswa_id' => $siswa->id,
            'master_pelanggaran_id' => $master->id,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Kejadian uji',
            'tindakan' => 'Menulis istighfar 25 kali',
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('pelanggarans', [
            'siswa_id' => $siswa->id,
            'tindakan' => 'Menulis istighfar 25 kali',
            'ditindak_oleh' => $tatib->id,
        ]);
    }

    public function test_catat_pelanggaran_tanpa_tindakan_tidak_mencatat_ditindak_oleh(): void
    {
        $tatib = $this->tatib();
        $siswa = Siswa::create(['nis' => '996802', 'nama' => 'Siswa Tanpa Tindakan Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $master = $this->masterPelanggaran(5);

        $this->sebagai($tatib)->post(route('guru.pelanggaran.store'), [
            'siswa_id' => $siswa->id,
            'master_pelanggaran_id' => $master->id,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Kejadian uji',
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('pelanggarans', [
            'siswa_id' => $siswa->id,
            'tindakan' => null,
            'ditindak_oleh' => null,
        ]);
    }

    public function test_get_poin_siswa_menghitung_poin_bersih_dan_status(): void
    {
        $tatib = $this->tatib();
        $siswa = Siswa::create(['nis' => '996803', 'nama' => 'Siswa Poin Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $master = $this->masterPelanggaran(15);

        Pelanggaran::create([
            'siswa_id' => $siswa->id, 'master_pelanggaran_id' => $master->id, 'tanggal' => now()->toDateString(),
            'tahun_ajaran_id' => $taAktifId, 'kategori' => 'Uji', 'judul' => 'Uji', 'deskripsi' => 'Uji', 'poin' => 15,
        ]);
        PenguranganPoin::create([
            'siswa_id' => $siswa->id, 'tahun_ajaran_id' => $taAktifId, 'tanggal' => now()->toDateString(),
            'kegiatan' => 'Petugas Upacara', 'poin' => 3, 'diberikan_oleh' => $tatib->id,
        ]);

        $response = $this->sebagai($tatib)->getJson(route('guru.api.siswa_poin', $siswa));

        $response->assertOk();
        $response->assertJson(['poin_semester' => 12, 'status_semester' => ['kode' => 'SP1']]);
    }

    public function test_tatib_bisa_kurangi_poin_siswa(): void
    {
        $tatib = $this->tatib();
        $siswa = Siswa::create(['nis' => '996804', 'nama' => 'Siswa Kurangi Poin Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($tatib)->post(route('guru.tatib.pengurangan-poin.store'), [
            'siswa_id' => $siswa->id,
            'kegiatan' => 'Petugas Upacara',
            'tanggal' => now()->toDateString(),
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('pengurangan_poins', [
            'siswa_id' => $siswa->id, 'poin' => 3, 'diberikan_oleh' => $tatib->id,
        ]);
    }

    public function test_tatib_tidak_bisa_kurangi_poin_dua_kali_sebulan(): void
    {
        $tatib = $this->tatib();
        $siswa = Siswa::create(['nis' => '996805', 'nama' => 'Siswa Kurangi Poin Dua Kali Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($tatib)->post(route('guru.tatib.pengurangan-poin.store'), [
            'siswa_id' => $siswa->id, 'kegiatan' => 'Petugas Upacara', 'tanggal' => now()->toDateString(),
        ])->assertRedirect(route('guru.portal'));

        $this->sebagai($tatib)->post(route('guru.tatib.pengurangan-poin.store'), [
            'siswa_id' => $siswa->id, 'kegiatan' => 'Juara Lomba', 'tanggal' => now()->toDateString(),
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(1, PenguranganPoin::where('siswa_id', $siswa->id)->count());
    }

    public function test_portal_tatib_merender_pane_kurangi_poin(): void
    {
        $this->sebagai($this->tatib())->get(route('guru.portal'))->assertOk();
    }

    public function test_portal_wali_kelas_merender_pane_rekap_poin(): void
    {
        $kelas = $this->kelas();
        $guru = Guru::create(['nama' => 'Wali Kelas Uji', 'nip' => '9996807', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $kelas->update(['wali_kelas_id' => $guru->id]);

        $this->sebagai($guru)->get(route('guru.portal'))->assertOk();
    }

    public function test_non_tatib_ditolak_kurangi_poin(): void
    {
        $guruMapel = Guru::create(['nama' => 'Guru Mapel Bukan Tatib', 'nip' => '9996806', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruMapel->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);
        $siswa = Siswa::create(['nis' => '996806', 'nama' => 'Siswa Ditolak Uji', 'kelas_id' => $this->kelas()->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($guruMapel)->post(route('guru.tatib.pengurangan-poin.store'), [
            'siswa_id' => $siswa->id, 'kegiatan' => 'Petugas Upacara', 'tanggal' => now()->toDateString(),
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('pengurangan_poins', ['siswa_id' => $siswa->id]);
    }
}
