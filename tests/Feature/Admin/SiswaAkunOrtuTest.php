<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_admin_bisa_menyetujui_satu_akun_ortu_lintas_kelas(): void
    {
        $siswa = $this->buatSiswa('998906', null);
        $akun = OrangTua::create([
            'siswa_id' => $siswa->id, 'nama' => 'Ortu Daftar Sendiri', 'username' => '081234567894',
            'password' => bcrypt('password'), 'is_active' => false,
        ]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.setujui', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $akun->fresh()->is_active);
    }

    public function test_admin_bisa_menyetujui_semua_akun_ortu_pending_sekaligus(): void
    {
        $siswaSatu = $this->buatSiswa('998907', null);
        $siswaDua = $this->buatSiswa('998908', null);
        $akunSudahAktif = $this->buatSiswa('998909', '081234567897');
        OrangTua::create(['siswa_id' => $siswaSatu->id, 'nama' => 'Ortu 1', 'username' => '081234567895', 'password' => bcrypt('password'), 'is_active' => false]);
        OrangTua::create(['siswa_id' => $siswaDua->id, 'nama' => 'Ortu 2', 'username' => '081234567896', 'password' => bcrypt('password'), 'is_active' => false]);
        $akunAktif = OrangTua::create(['siswa_id' => $akunSudahAktif->id, 'nama' => 'Ortu Aktif', 'username' => '081234567897', 'password' => bcrypt('password'), 'is_active' => true]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.setujui-semua'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, OrangTua::where('is_active', false)->count());
        $this->assertTrue((bool) $akunAktif->fresh()->is_active);
    }

    public function test_halaman_daftar_siswa_merender_baris_akun_ortu_pending(): void
    {
        $siswa = $this->buatSiswa('998910', null);
        OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Pending', 'username' => '081234567898', 'password' => bcrypt('password'), 'is_active' => false]);

        // Filter ke siswa ini saja -- daftar siswa dipaginasi, siswa baru bisa saja tidak
        // masuk 15 teratas kalau data seeder lebih banyak/lebih baru.
        $this->sebagai($this->admin())->get(route('admin.siswa.index', ['q' => $siswa->nis]))
            ->assertOk()
            ->assertSee('Menunggu Persetujuan', false)
            ->assertSee('Setujui Semua Akun Ortu', false);
    }

    public function test_admin_bisa_reset_password_akun_ortu_yang_lupa_password(): void
    {
        $siswa = $this->buatSiswa('998911', null);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Lupa Password', 'username' => '081234567899', 'password' => bcrypt('password-lama'), 'is_active' => true, 'harus_lengkapi_profil' => false]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.reset-password', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $akun->refresh();
        $this->assertTrue(Hash::check('password', $akun->password));
        $this->assertTrue((bool) $akun->harus_lengkapi_profil);
    }

    public function test_bukan_admin_ditolak_setujui_semua_akun_ortu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('admin.siswa.akun-ortu.setujui-semua'))->assertRedirect(route('login'));
    }
}
