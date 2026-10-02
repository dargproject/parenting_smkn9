<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\KategoriKasus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriKasusTest extends TestCase
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

    public function test_admin_dapat_melihat_dan_membuat_kategori_kasus(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();

        $this->sebagai($admin)->get(route('admin.kategori-kasus.index'))->assertOk();

        $this->sebagai($admin)
            ->post(route('admin.kategori-kasus.store'), ['nama_kategori' => 'Kesehatan'])
            ->assertRedirect(route('admin.kategori-kasus.index'));

        $this->assertDatabaseHas('kategori_kasus', ['nama_kategori' => 'Kesehatan']);
    }

    public function test_admin_dapat_mengubah_kategori_kasus(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        $kategori = KategoriKasus::create(['nama_kategori' => 'Awal']);

        $this->sebagai($admin)
            ->put(route('admin.kategori-kasus.update', $kategori), ['nama_kategori' => 'Sudah Diubah'])
            ->assertRedirect(route('admin.kategori-kasus.index'));

        $this->assertDatabaseHas('kategori_kasus', ['id' => $kategori->id, 'nama_kategori' => 'Sudah Diubah']);
    }

    public function test_admin_dapat_menghapus_kategori_kasus(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        $kategori = KategoriKasus::create(['nama_kategori' => 'Akan Dihapus']);

        $this->sebagai($admin)->delete(route('admin.kategori-kasus.destroy', $kategori));

        $this->assertDatabaseMissing('kategori_kasus', ['id' => $kategori->id]);
    }

    public function test_nama_kategori_harus_unik(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        // 'Pribadi' sudah di-seed BkSeeder sebagai bagian dari DatabaseSeeder di setUp().
        $this->sebagai($admin)
            ->post(route('admin.kategori-kasus.store'), ['nama_kategori' => 'Pribadi'])
            ->assertSessionHasErrors('nama_kategori');
    }

    public function test_guru_non_admin_tidak_bisa_mengakses_kategori_kasus(): void
    {
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();

        $this->sebagai($guruBk)->get(route('admin.kategori-kasus.index'))->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_tidak_bisa_mengakses_kategori_kasus(): void
    {
        $this->get(route('admin.kategori-kasus.index'))->assertRedirect(route('login'));
    }
}
