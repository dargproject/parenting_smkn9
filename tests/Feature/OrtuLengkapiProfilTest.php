<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrtuLengkapiProfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function buatAkun(bool $harusLengkapiProfil): OrangTua
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Lengkapi Profil Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '998950', 'nama' => 'Siswa Lengkapi Profil Uji', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true,
        ]);

        return OrangTua::create([
            'siswa_id' => $siswa->id, 'nama' => 'Orang Tua/Wali Siswa Lengkapi Profil Uji',
            'username' => '998950ortu', 'password' => Hash::make('password'), 'is_active' => true,
            'harus_lengkapi_profil' => $harusLengkapiProfil,
        ]);
    }

    public function test_modal_lengkapi_profil_tampil_untuk_akun_yang_dibuatkan_admin(): void
    {
        $orangTua = $this->buatAkun(harusLengkapiProfil: true);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('Lengkapi Akun Anda');
    }

    public function test_modal_tidak_tampil_untuk_akun_yang_sudah_lengkap(): void
    {
        $orangTua = $this->buatAkun(harusLengkapiProfil: false);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertDontSee('Lengkapi Akun Anda');
    }

    public function test_submit_lengkapi_profil_mengubah_nama_password_dan_mematikan_modal(): void
    {
        $orangTua = $this->buatAkun(harusLengkapiProfil: true);

        $response = $this->actingAs($orangTua, 'orangtua')->put(route('ortu.akun.update'), [
            'nama' => 'Budi Santoso',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $orangTua->refresh();
        $this->assertSame('Budi Santoso', $orangTua->nama);
        $this->assertFalse($orangTua->harus_lengkapi_profil);
        $this->assertTrue(Hash::check('rahasia123', $orangTua->password));

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertDontSee('Lengkapi Akun Anda');
    }

    public function test_lengkapi_profil_ditolak_jika_password_tidak_cocok_konfirmasi(): void
    {
        $orangTua = $this->buatAkun(harusLengkapiProfil: true);

        $this->actingAs($orangTua, 'orangtua')->put(route('ortu.akun.update'), [
            'nama' => 'Budi Santoso',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda-sekali',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($orangTua->refresh()->harus_lengkapi_profil);
    }
}
