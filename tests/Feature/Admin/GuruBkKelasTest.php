<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruBkKelasTest extends TestCase
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

    public function test_admin_dapat_menetapkan_kelas_yang_ditangani_guru_bk(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
        $kelas = Kelas::limit(2)->get();

        $response = $this->sebagai($admin)->put(route('admin.guru.update', $guruBk), [
            'nama' => $guruBk->nama,
            'nip' => $guruBk->nip,
            'roles' => Role::where('name', 'guru_bk')->pluck('id')->all(),
            'kelas_bk' => $kelas->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('admin.guru.index'));

        $guruBk->refresh();
        $this->assertSame($kelas->pluck('id')->sort()->values()->all(), $guruBk->kelasBk->pluck('id')->sort()->values()->all());

        foreach ($kelas as $k) {
            $this->assertDatabaseHas('guru_bk_kelas', ['guru_id' => $guruBk->id, 'kelas_id' => $k->id]);
        }
    }

    public function test_mengganti_assignment_menghapus_kelas_lama_yang_tidak_disertakan(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
        $kelas = Kelas::limit(3)->get();

        $guruBk->kelasBk()->sync($kelas->pluck('id')->all());

        $this->sebagai($admin)->put(route('admin.guru.update', $guruBk), [
            'nama' => $guruBk->nama,
            'nip' => $guruBk->nip,
            'roles' => Role::where('name', 'guru_bk')->pluck('id')->all(),
            'kelas_bk' => [$kelas->first()->id],
        ])->assertRedirect(route('admin.guru.index'));

        $guruBk->refresh();
        $this->assertSame([$kelas->first()->id], $guruBk->kelasBk->pluck('id')->all());
    }

    public function test_kelas_mengetahui_guru_bk_yang_menanganinya(): void
    {
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
        $kelas = Kelas::firstOrFail();

        $guruBk->kelasBk()->attach($kelas->id);

        $this->assertTrue($kelas->guruBks->contains('id', $guruBk->id));
    }
}
