<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingPklTest extends TestCase
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

    public function test_admin_dapat_menetapkan_pembimbing_pkl_lewat_checklist(): void
    {
        $admin = $this->admin();
        $guruSatu = Guru::create(['nama' => 'Calon Pembimbing Satu', 'nip' => '9999401', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruDua = Guru::create(['nama' => 'Calon Pembimbing Dua', 'nip' => '9999402', 'password' => bcrypt('password'), 'is_active' => true]);

        $this->sebagai($admin)->get(route('admin.pembimbing-pkl.edit'))
            ->assertOk()
            ->assertSee('Calon Pembimbing Satu')
            ->assertSee('Calon Pembimbing Dua');

        $this->sebagai($admin)->put(route('admin.pembimbing-pkl.update'), [
            'guru_ids' => [$guruSatu->id, $guruDua->id],
        ])->assertRedirect(route('admin.pembimbing-pkl.edit'));

        $this->assertTrue($guruSatu->roles()->where('name', 'pembimbing_pkl')->exists());
        $this->assertTrue($guruDua->roles()->where('name', 'pembimbing_pkl')->exists());
    }

    public function test_melepas_centang_menghapus_role_pembimbing_pkl(): void
    {
        $admin = $this->admin();
        $guru = Guru::create(['nama' => 'Mantan Pembimbing Uji', 'nip' => '9999403', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'pembimbing_pkl'])->id);

        $this->sebagai($admin)->put(route('admin.pembimbing-pkl.update'), [
            'guru_ids' => [],
        ])->assertRedirect(route('admin.pembimbing-pkl.edit'));

        $this->assertFalse($guru->roles()->where('name', 'pembimbing_pkl')->exists());
    }

    public function test_guru_lain_yang_tidak_dicentang_tidak_ikut_berubah(): void
    {
        $admin = $this->admin();
        $guruDicentang = Guru::create(['nama' => 'Dicentang Uji', 'nip' => '9999404', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruTidakDicentang = Guru::create(['nama' => 'Tidak Dicentang Uji', 'nip' => '9999405', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruTidakDicentang->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $this->sebagai($admin)->put(route('admin.pembimbing-pkl.update'), [
            'guru_ids' => [$guruDicentang->id],
        ])->assertRedirect();

        $this->assertTrue($guruDicentang->roles()->where('name', 'pembimbing_pkl')->exists());
        $this->assertTrue($guruTidakDicentang->roles()->where('name', 'guru_mapel')->exists(), 'Role lain milik guru yang tidak dicentang tidak boleh ikut terhapus.');
        $this->assertFalse($guruTidakDicentang->roles()->where('name', 'pembimbing_pkl')->exists());
    }

    public function test_role_selain_admin_ditolak_mengakses_pembimbing_pkl(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))->firstOrFail();

        $this->sebagai($guruMapel)->get(route('admin.pembimbing-pkl.edit'))->assertRedirect(route('login'));
    }
}
