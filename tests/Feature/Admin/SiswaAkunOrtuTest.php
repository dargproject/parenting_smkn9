<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaAkunOrtuTest extends TestCase
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

    private function admin(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    private function buatSiswa(string $nis, ?string $noHpOrtu): Siswa
    {
        $kelas = Kelas::firstOrCreate(['nama_kelas' => 'X Generate Ortu Uji'], ['tingkat' => 'X', 'jurusan' => 'Uji']);

        return Siswa::create([
            'nis' => $nis, 'nama' => 'Siswa Generate Ortu '.$nis, 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true, 'no_hp_ortu' => $noHpOrtu,
        ]);
    }

    public function test_generate_semua_membuat_akun_untuk_siswa_yang_punya_no_hp_dan_belum_punya_akun(): void
    {
        $siswa = $this->buatSiswa('998901', '081234567891');

        $response = $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orang_tuas', ['siswa_id' => $siswa->id, 'username' => '081234567891']);
    }

    public function test_generate_semua_melewati_siswa_yang_belum_diisi_no_hp_ortu(): void
    {
        $siswa = $this->buatSiswa('998902', null);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $this->assertDatabaseMissing('orang_tuas', ['siswa_id' => $siswa->id]);
    }

    public function test_generate_semua_melewati_siswa_yang_sudah_punya_akun_tanpa_mengubahnya(): void
    {
        $siswa = $this->buatSiswa('998903', '081234567892');
        $akunLama = OrangTua::create([
            'siswa_id' => $siswa->id, 'nama' => 'Nama Akun Lama', 'username' => '081234567892',
            'password' => bcrypt('password-lama'), 'is_active' => true,
        ]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $this->assertSame(1, OrangTua::where('siswa_id', $siswa->id)->count());
        $this->assertSame('Nama Akun Lama', $akunLama->refresh()->nama);
    }

    public function test_generate_semua_melewati_nomor_hp_yang_sama_antar_kakak_adik(): void
    {
        $siswaSatu = $this->buatSiswa('998904', '081234567893');
        $siswaDua = $this->buatSiswa('998905', '081234567893');

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $this->assertSame(1, OrangTua::where('username', '081234567893')->count());
        $this->assertTrue(
            OrangTua::where('siswa_id', $siswaSatu->id)->exists() xor OrangTua::where('siswa_id', $siswaDua->id)->exists()
        );
    }

    public function test_bukan_admin_ditolak_generate_semua_akun_ortu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('admin.siswa.akun-ortu.generate-all'))->assertRedirect(route('login'));
    }
}
