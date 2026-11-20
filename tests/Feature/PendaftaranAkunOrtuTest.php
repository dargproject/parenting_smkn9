<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\OrangTuaSiswa;
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

    private function siapkanKelasDanSiswa(?Guru $waliKelas = null, string $nis = '998301'): array
    {
        $kelas = Kelas::firstOrCreate(['nama_kelas' => 'X Daftar Ortu Uji'], ['tingkat' => 'X', 'jurusan' => 'Uji', 'wali_kelas_id' => $waliKelas?->id]);
        $siswa = Siswa::create(['nis' => $nis, 'nama' => 'Siswa Daftar Ortu Uji '.$nis, 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        return [$kelas, $siswa];
    }

    private function buatAkunOrtu(Siswa $siswa, string $username, string $password = 'password', bool $aktif = true): OrangTua
    {
        $orangTua = OrangTua::create(['nama' => 'Ortu Lama', 'username' => $username, 'password' => bcrypt($password), 'is_active' => $aktif]);
        $orangTua->links()->create(['siswa_id' => $siswa->id, 'is_active' => $aktif]);

        return $orangTua;
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
        $this->buatAkunOrtu($siswa, '81200000001');

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
            'nama' => 'Budi Orang Tua Uji',
            'username' => '081234567890',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('orang_tua_siswa', ['siswa_id' => $siswa->id, 'is_active' => false]);
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
        $this->buatAkunOrtu($siswa, '81200000001', aktif: false);

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswa->id,
            'nama' => 'Orang Lain',
            'no_hp' => '089988887777',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHas('error');

        $this->assertSame(1, OrangTuaSiswa::where('siswa_id', $siswa->id)->count());
    }

    public function test_no_hp_yang_sudah_terdaftar_dengan_password_benar_menautkan_anak_kedua(): void
    {
        [$kelas, $siswaA] = $this->siapkanKelasDanSiswa(nis: '998301');
        $siswaB = Siswa::create(['nis' => '998302', 'nama' => 'Siswa Daftar Ortu Uji B', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $akunA = $this->buatAkunOrtu($siswaA, '081234567890', password: 'passwordlama', aktif: true);

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswaB->id,
            'nama' => 'Nama Diabaikan',
            'no_hp' => '081234567890',
            'password' => 'passwordlama',
            'password_confirmation' => 'passwordlama',
        ])->assertRedirect(route('ortu.login'))->assertSessionHas('success');

        // Siswa B ditautkan ke akun yang SAMA dengan siswa A, bukan akun baru.
        $this->assertSame(1, OrangTua::where('username', '081234567890')->count());
        $this->assertDatabaseHas('orang_tua_siswa', ['orang_tua_id' => $akunA->id, 'siswa_id' => $siswaB->id, 'is_active' => false]);
        // Tautan siswa A (sudah aktif lebih dulu) tidak terganggu.
        $this->assertTrue((bool) $siswaA->linkOrtu->fresh()->is_active);
    }

    public function test_no_hp_yang_sudah_terdaftar_dengan_password_salah_ditolak(): void
    {
        [$kelas, $siswaA] = $this->siapkanKelasDanSiswa(nis: '998303');
        $siswaB = Siswa::create(['nis' => '998304', 'nama' => 'Siswa Daftar Ortu Uji C', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $this->buatAkunOrtu($siswaA, '081234567891', password: 'passwordlama', aktif: true);

        $this->post(route('ortu.daftar.store'), [
            'siswa_id' => $siswaB->id,
            'nama' => 'Orang Lain',
            'no_hp' => '081234567891',
            'password' => 'password-tebakan',
            'password_confirmation' => 'password-tebakan',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('orang_tua_siswa', ['siswa_id' => $siswaB->id]);
    }

    public function test_wali_kelas_bisa_menyetujui_akun_ortu_siswa_binaannya(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Setujui Uji', 'nip' => '9998301', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelas);
        $this->buatAkunOrtu($siswa, '081234567890', password: 'password123', aktif: false);

        $this->sebagai($waliKelas)->post(route('guru.wali-kelas.akun-ortu.setujui', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $siswa->linkOrtu->fresh()->is_active);
        $this->assertTrue((bool) $siswa->orangTua->fresh()->is_active);

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
        $akun = $this->buatAkunOrtu($siswa, '081234567890', password: 'password123', aktif: false);

        $this->sebagai($waliKelas)->delete(route('guru.wali-kelas.akun-ortu.tolak', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('orang_tuas', ['id' => $akun->id]);
    }

    public function test_wali_kelas_menolak_anak_kedua_tidak_menghapus_akun_yang_dipakai_anak_pertama(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Tolak Dua Anak Uji', 'nip' => '9998308', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [$kelas, $siswaSatu] = $this->siapkanKelasDanSiswa($waliKelas, '998309');
        $siswaDua = Siswa::create(['nis' => '998310', 'nama' => 'Siswa Tolak Dua Anak Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $akun = $this->buatAkunOrtu($siswaSatu, '081234567920', aktif: true);
        $akun->links()->create(['siswa_id' => $siswaDua->id, 'is_active' => false]);

        $this->sebagai($waliKelas)->delete(route('guru.wali-kelas.akun-ortu.tolak', $siswaDua))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('orang_tua_siswa', ['siswa_id' => $siswaDua->id]);
        $this->assertDatabaseHas('orang_tuas', ['id' => $akun->id]);
        $this->assertDatabaseHas('orang_tua_siswa', ['siswa_id' => $siswaSatu->id]);
    }

    public function test_wali_kelas_bisa_reset_password_akun_ortu_siswa_binaannya(): void
    {
        $waliKelas = Guru::create(['nama' => 'Wali Kelas Reset Password Uji', 'nip' => '9998305', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelas->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelas);
        $akun = $this->buatAkunOrtu($siswa, '081234567890', password: 'password-lama', aktif: true);
        $akun->update(['harus_lengkapi_profil' => false]);

        $this->sebagai($waliKelas)->post(route('guru.wali-kelas.akun-ortu.reset-password', $siswa))
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
        $akun = $this->buatAkunOrtu($siswa, '081234567890', password: 'password-lama', aktif: true);

        $this->sebagai($waliKelasB)->post(route('guru.wali-kelas.akun-ortu.reset-password', $siswa))->assertStatus(403);

        $this->assertTrue(Hash::check('password-lama', $akun->fresh()->password));
    }

    public function test_wali_kelas_lain_tidak_bisa_menyetujui_akun_ortu_kelas_lain(): void
    {
        $waliKelasA = Guru::create(['nama' => 'Wali Kelas A Uji', 'nip' => '9998303', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasA->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        $waliKelasB = Guru::create(['nama' => 'Wali Kelas B Uji', 'nip' => '9998304', 'password' => bcrypt('password'), 'is_active' => true]);
        $waliKelasB->roles()->attach(Role::firstOrCreate(['name' => 'wali_kelas'])->id);
        [, $siswa] = $this->siapkanKelasDanSiswa($waliKelasA);
        $this->buatAkunOrtu($siswa, '081234567890', password: 'password123', aktif: false);

        $this->sebagai($waliKelasB)->post(route('guru.wali-kelas.akun-ortu.setujui', $siswa))->assertStatus(403);

        $this->assertFalse((bool) $siswa->linkOrtu->fresh()->is_active);
    }
}
