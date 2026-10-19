<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_guru_mengisi_remember_token_agar_tetap_login(): void
    {
        $guru = Guru::create([
            'nama' => 'Guru Remember Uji', 'nip' => '9996701',
            'password' => bcrypt('password'), 'is_active' => true,
        ]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $this->assertNull($guru->remember_token);

        $this->post(route('login.post'), ['nip' => $guru->nip, 'password' => 'password'])
            ->assertRedirect(route('guru.portal'));

        $this->assertNotNull($guru->fresh()->remember_token);
    }

    public function test_sesi_kosong_tapi_auth_masih_login_direhidrasi_oleh_role_middleware(): void
    {
        $guru = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))->firstOrFail();

        // Simulasikan kondisi setelah cookie "remember me" otomatis login-kan ulang guru
        // (Auth::check() true) tapi sesi aplikasi masih kosong -- tanpa session() manual,
        // beda dari helper sebagai() yang dipakai test lain.
        $response = $this->actingAs($guru)->get(route('guru.portal'));

        $response->assertOk();
        $this->assertSame($guru->id, session('guru_id'));
        $this->assertContains('guru_mapel', session('roles'));
    }

    public function test_tanpa_sesi_dan_tanpa_auth_tetap_diarahkan_ke_login(): void
    {
        $this->get(route('guru.portal'))->assertRedirect(route('login'));
    }

    public function test_login_orangtua_mengisi_remember_token_agar_tetap_login(): void
    {
        $siswa = Siswa::whereDoesntHave('orangTua')->firstOrFail();
        $ortu = OrangTua::create([
            'siswa_id' => $siswa->id, 'nama' => 'Ortu Remember Uji',
            'username' => 'ortu_remember_uji', 'password' => bcrypt('password'), 'is_active' => true,
        ]);

        $this->assertNull($ortu->remember_token);

        $this->post(route('ortu.login.post'), ['username' => $ortu->username, 'password' => 'password'])
            ->assertRedirect(route('ortu.dashboard'));

        $this->assertNotNull($ortu->fresh()->remember_token);
    }

    public function test_buka_halaman_login_guru_saat_masih_login_langsung_lanjut_ke_portal(): void
    {
        // Skenario PWA Android: start_url selalu /login, meski cookie remember masih valid.
        $guru = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))->firstOrFail();

        $this->actingAs($guru)->get(route('login'))->assertRedirect(route('guru.portal'));
    }

    public function test_buka_halaman_login_guru_tanpa_sesi_tetap_tampilkan_form(): void
    {
        $this->get(route('login'))->assertOk()->assertViewIs('auth.login');
    }

    public function test_buka_halaman_login_ortu_saat_masih_login_langsung_lanjut_ke_dashboard(): void
    {
        $siswa = Siswa::whereDoesntHave('orangTua')->firstOrFail();
        $ortu = OrangTua::create([
            'siswa_id' => $siswa->id, 'nama' => 'Ortu Login Uji',
            'username' => 'ortu_login_uji', 'password' => bcrypt('password'), 'is_active' => true,
        ]);

        $this->actingAs($ortu, 'orangtua')->get(route('ortu.login'))->assertRedirect(route('ortu.dashboard'));
    }
}
