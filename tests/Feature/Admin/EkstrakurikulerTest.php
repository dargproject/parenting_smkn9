<?php

namespace Tests\Feature\Admin;

use App\Models\Ekstrakurikuler;
use App\Models\Guru;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EkstrakurikulerTest extends TestCase
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

    private function pembinaEkskul(): Guru
    {
        $guru = Guru::create(['nama' => 'Pembina Uji', 'nip' => '9999001', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'pembina_ekskul'])->id);

        return $guru;
    }

    public function test_admin_dapat_mengelola_ekstrakurikuler(): void
    {
        $admin = $this->admin();
        $pembina = $this->pembinaEkskul();

        $this->sebagai($admin)->post(route('admin.ekstrakurikuler.store'), [
            'nama_ekskul' => 'Pramuka',
            'pembina_ids' => [$pembina->id],
            'is_active' => '1',
        ])->assertRedirect(route('admin.ekstrakurikuler.index'));

        $ekskul = Ekstrakurikuler::where('nama_ekskul', 'Pramuka')->firstOrFail();
        $this->assertSame([$pembina->id], $ekskul->pembinas->pluck('id')->all());
        $this->assertTrue($ekskul->is_active);

        $this->sebagai($admin)->put(route('admin.ekstrakurikuler.update', $ekskul), [
            'nama_ekskul' => 'Pramuka Penggalang',
            'pembina_ids' => [$pembina->id],
        ])->assertRedirect(route('admin.ekstrakurikuler.index'));

        $ekskul->refresh();
        $this->assertSame('Pramuka Penggalang', $ekskul->nama_ekskul);
        $this->assertFalse($ekskul->is_active);

        $this->sebagai($admin)->delete(route('admin.ekstrakurikuler.destroy', $ekskul))->assertRedirect();
        $this->assertDatabaseMissing('ekstrakurikulers', ['id' => $ekskul->id]);
    }

    public function test_admin_dapat_menetapkan_lebih_dari_satu_pembina(): void
    {
        $admin = $this->admin();
        $pembinaSatu = $this->pembinaEkskul();
        $pembinaDua = Guru::create(['nama' => 'Pembina Dua Uji', 'nip' => '9999002', 'password' => bcrypt('password'), 'is_active' => true]);
        $pembinaDua->roles()->attach(Role::firstOrCreate(['name' => 'pembina_ekskul'])->id);

        $this->sebagai($admin)->post(route('admin.ekstrakurikuler.store'), [
            'nama_ekskul' => 'Basket',
            'pembina_ids' => [$pembinaSatu->id, $pembinaDua->id],
            'is_active' => '1',
        ])->assertRedirect(route('admin.ekstrakurikuler.index'));

        $ekskul = Ekstrakurikuler::where('nama_ekskul', 'Basket')->firstOrFail();
        $this->assertEqualsCanonicalizing([$pembinaSatu->id, $pembinaDua->id], $ekskul->pembinas->pluck('id')->all());

        $this->sebagai($admin)->get(route('admin.ekstrakurikuler.index'))
            ->assertSee('Pembina Uji')
            ->assertSee('Pembina Dua Uji');

        // Lepas satu pembina lewat update (sync) -- hanya pembinaDua yang tersisa.
        $this->sebagai($admin)->put(route('admin.ekstrakurikuler.update', $ekskul), [
            'nama_ekskul' => 'Basket',
            'pembina_ids' => [$pembinaDua->id],
            'is_active' => '1',
        ])->assertRedirect(route('admin.ekstrakurikuler.index'));

        $this->assertSame([$pembinaDua->id], $ekskul->refresh()->pembinas->pluck('id')->all());
    }

    public function test_role_selain_admin_ditolak_mengakses_ekstrakurikuler(): void
    {
        $pembina = $this->pembinaEkskul();

        $this->sebagai($pembina)->get(route('admin.ekstrakurikuler.index'))->assertRedirect(route('login'));
    }
}
