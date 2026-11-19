<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PendaftaranAkunOrtuTest extends TestCase
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

    private function siapkanKelasDanSiswa(?Guru $waliKelas = null): array
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Daftar Ortu Uji', 'tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $waliKelas?->id]);
        $siswa = Siswa::create(['nis' => '998301', 'nama' => 'Siswa Daftar Ortu Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        return [$kelas, $siswa];
    }

    public function test_siswa_yang_belum_punya_akun_ortu_muncul_di_dropdown_kelas(): void
    {
        [$kelas, $siswa] = $this->siapkanKelasDanSiswa();

        $response = $this->getJson(route('ortu.api.siswa_by_kelas', $kelas));

        $response->assertOk()->assertJsonFragment(['id' => $siswa->id, 'nama' => $siswa->nama]);
    }

    public function test_siswa_yang_sudah_punya_akun_ortu_tidak_muncul_lagi_di_dropdown(): void
    {
        [$kelas, $siswa] = $this->siapkanKelasDanSiswa();
        OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Lama', 'username' => '81200000001', 'password' => bcrypt('password'), 'is_active' => true]);

        $response = $this->getJson(route('ortu.api.siswa_by_kelas', $kelas));

        $response->assertOk()->assertJsonMissing(['id' => $siswa->id]);
    }

    public function test_orangtua_bisa_daftar_sendiri_dan_akun_belum_aktif(): void
    {
        [, $siswa] = $this->siapkanKelasDanSiswa();

        $response = $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswa->id,
            'nama' => 'Budi Orang Tua Uji',
            'no_hp' => '0812-3456-7890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('ortu.login'));

        $this->assertDatabaseHas('orang_tuas', [
            'siswa_id' => $siswa->id,
            'nama' => 'Budi Orang Tua Uji',
            'username' => '081234567890',
            'is_active' => false,
        ]);
        $this->assertSame('081234567890', $siswa->fresh()->no_hp_ortu);
    }

    public function test_akun_yang_belum_disetujui_tidak_bisa_login(): void
    {
        [, $siswa] = $this->siapkanKelasDanSiswa();

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswa->id,
            'nama' => 'Budi Orang Tua Uji',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post(route('ortu.login.post'), ['username' => '081234567890', 'password' => 'password123'])
            ->assertRedirect(route('ortu.login'))
            ->assertSessionHas('error');

        $this->assertGuest('orangtua');
    }

    public function test_daftar_ulang_untuk_siswa_yang_sudah_punya_akun_ditolak(): void
    {
        [, $siswa] = $this->siapkanKelasDanSiswa();
        OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Lama', 'username' => '81200000001', 'password' => bcrypt('password'), 'is_active' => false]);

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswa->id,
            'nama' => 'Orang Lain',
            'no_hp' => '089988887777',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHas('error');

        $this->assertSame(1, OrangTua::where('siswa_id', $siswa->id)->count());
    }

    public function test_no_hp_yang_sudah_dipakai_siswa_lain_ditolak(): void
    {
        [$kelas, $siswaA] = $this->siapkanKelasDanSiswa();
        $siswaB = Siswa::create(['nis' => '998302', 'nama' => 'Siswa Daftar Ortu Uji B', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        OrangTua::create(['siswa_id' => $siswaA->id, 'nama' => 'Ortu A', 'username' => '081234567890', 'password' => bcrypt('password'), 'is_active' => true]);

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswaB->id,
            'nama' => 'Ortu B',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('orang_tuas', ['siswa_id' => $siswaB->id]);
    }

    public function test_wali_kelas_bisa_menyetujui_akun_ortu_siswa_binaannya(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Setujui Uji', 'nip' => '9998301', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelas);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Baru', 'username' => '081234567890', 'password' => bcrypt('password123'), 'is_active' => false]);

        $this->sebagai($waliKelas)->post(route('guru.wali-kelas.akun-ortu.setujui', $akun))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $akun->fresh()->is_active);

        // Setelah disetujui, akun bisa login dengan No. HP + password yang didaftarkan sendiri.
        $this->post(route('ortu.login.post'), ['username' => '081234567890', 'password' => 'password123'])
            ->assertRedirect(route('ortu.dashboard'));
        $this->assertAuthenticated('orangtua');
    }

    public function test_wali_kelas_bisa_menolak_akun_ortu_dan_pendaftaran_terhapus(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Tolak Uji', 'nip' => '9998302', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelas);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Baru', 'username' => '081234567890', 'password' => bcrypt('password123'), 'is_active' => false]);

        $this->sebagai($waliKelas)->delete(route('guru.wali-kelas.akun-ortu.tolak', $akun))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('orang_tuas', ['id' => $akun->id]);
    }

    public function test_wali_kelas_bisa_reset_password_akun_ortu_siswa_binaannya(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Reset Password Uji', 'nip' => '9998305', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelas);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Lupa Password', 'username' => '081234567890', 'password' => bcrypt('password-lama'), 'is_active' => true, 'harus_lengkapi_profil' => false]);

        $this->sebagai($waliKelas)->post(route('guru.wali-kelas.akun-ortu.reset-password', $akun))
            ->assertRedirect()
            ->assertSessionHas('success');

        $akun->refresh();
        $this->assertTrue(Hash::check('password', $akun->password));
        $this->assertTrue((bool) $akun->harus_lengkapi_profil);
    }

    public function test_wali_kelas_lain_tidak_bisa_reset_password_akun_ortu_kelas_lain(): void
    {
        $waliKelasA = Guru::create(['nama' => 'Wali Kelas Reset A Uji', 'nip' => '9998306', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasA->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $waliKelasB = Guru::create(['nama' => 'Wali Kelas Reset B Uji', 'nip' => '9998307', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasB->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelasA);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Lupa Password', 'username' => '081234567890', 'password' => bcrypt('password-lama'), 'is_active' => true]);

        $this->sebagai($waliKelasB)->post(route('guru.wali-kelas.akun-ortu.reset-password', $akun))->assertStatus(403);

        $this->assertTrue(Hash::check('password-lama', $akun->fresh()->password));
    }

    public function test_wali_kelas_lain_tidak_bisa_menyetujui_akun_ortu_kelas_lain(): void
    {
        $waliKelasA = Guru::create(['nama' => 'Wali Kelas A Uji', 'nip' => '9998303', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasA->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $waliKelasB = Guru::create(['nama' => 'Wali Kelas B Uji', 'nip' => '9998304', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasB->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelasA);
        $akun = OrangTua::create(['siswa_id' => $siswa->id, 'nama' => 'Ortu Baru', 'username' => '081234567890', 'password' => bcrypt('password123'), 'is_active' => false]);

        $this->sebagai($waliKelasB)->post(route('guru.wali-kelas.akun-ortu.setujui', $akun))->assertStatus(403);

        $this->assertFalse((bool) $akun->fresh()->is_active);
    }
}
