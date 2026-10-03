<?php

namespace Tests\Feature\Admin;

use App\Models\BimbinganIndividu;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HapusSemuaSiswaTest extends TestCase
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

    private function admin(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    public function test_halaman_siswa_menampilkan_tombol_dan_jumlah_siswa_yang_benar(): void
    {
        $admin = $this->admin();
        $totalSiswa = Siswa::count();

        $this->sebagai($admin)->get(route('admin.siswa.index'))
            ->assertOk()
            ->assertSee('Hapus Semua Siswa')
            ->assertSee((string) $totalSiswa.' siswa', false);
    }

    public function test_admin_dapat_menghapus_semua_siswa_dan_data_terkait_ikut_cascade(): void
    {
        $admin = $this->admin();
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
        $siswa = Siswa::firstOrFail();

        $kasusBk = KasusBk::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Kasus Untuk Uji Cascade',
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $individu = BimbinganIndividu::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_layanan' => now()->toDateString(),
        ]);

        $this->assertTrue(Siswa::count() > 0);

        $this->sebagai($admin)->delete(route('admin.siswa.destroy-all'))
            ->assertRedirect();

        $this->assertSame(0, Siswa::count());
        $this->assertDatabaseMissing('kasus_bks', ['id' => $kasusBk->id]);
        $this->assertDatabaseMissing('bimbingan_individus', ['id' => $individu->id]);
    }

    public function test_role_selain_admin_ditolak_menghapus_semua_siswa(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();

        $totalSebelum = Siswa::count();

        $this->sebagai($guruMapel)->delete(route('admin.siswa.destroy-all'))->assertRedirect(route('login'));

        $this->assertSame($totalSebelum, Siswa::count());
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->delete(route('admin.siswa.destroy-all'))->assertRedirect(route('login'));
    }
}
