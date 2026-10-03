<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\Peminatan;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminatanTest extends TestCase
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

    private function guruBk(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
    }

    public function test_guru_bk_dapat_membuat_data_tes_bakat_minat_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.peminatan.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [
                'Linguistik' => ['LG01', 'LG02', 'LG03'],
                'Musikal' => ['MU01'],
            ],
        ]);

        $peminatan = Peminatan::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($peminatan);
        $response->assertRedirect(route('guru.bk.asesmen.peminatan.show', $peminatan));
        $this->assertSame('Linguistik', $peminatan->pilihan1);
        $this->assertSame('Musikal', $peminatan->pilihan2);
        $this->assertSame('Linguistik', $peminatan->hasil);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.peminatan.index'))->assertOk()->assertSee($siswa->nama);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_tes_bakat_minat(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $peminatan = Peminatan::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Linguistik' => ['LG01']],
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.peminatan.show', $peminatan))->assertOk()->assertSee($siswa->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.peminatan.update', $peminatan), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Kinestetik' => ['KI01', 'KI02', 'KI03', 'KI04']],
        ])->assertRedirect(route('guru.bk.asesmen.peminatan.show', $peminatan));

        $peminatan->refresh();
        $this->assertSame('Kinestetik', $peminatan->pilihan1);
    }

    public function test_guru_bk_dapat_menghapus_data_tes_bakat_minat(): void
    {
        $guruBk = $this->guruBk();
        $peminatan = Peminatan::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.peminatan.destroy', $peminatan))
            ->assertRedirect(route('guru.bk.asesmen.peminatan.index'));

        $this->assertDatabaseMissing('peminatans', ['id' => $peminatan->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_tes_bakat_minat(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.peminatan.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.peminatan.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.peminatan.index'))->assertRedirect(route('login'));
    }
}
