<?php

namespace Tests\Feature\Bk;

use App\Models\Dcm;
use App\Models\Guru;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DcmTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_data_dcm_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.dcm.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['A' => ['A01', 'A05'], 'B' => ['B02']],
            'kesimpulan' => 'Perlu pemantauan kesehatan.',
        ]);

        $dcm = Dcm::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($dcm);
        $response->assertRedirect(route('guru.bk.asesmen.dcm.show', $dcm));
        $this->assertCount(2, $dcm->jawaban['A']);
        $this->assertCount(3, $dcm->masalah_teridentifikasi);
        $this->assertSame('Perlu pemantauan kesehatan.', $dcm->kesimpulan);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.dcm.index'))->assertOk()->assertSee($siswa->nama);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_dcm(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $dcm = Dcm::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['A' => ['A01']],
            'masalah_teridentifikasi' => ['A01 - Sering sakit ketika SD'],
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.dcm.show', $dcm))->assertOk()->assertSee($siswa->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.dcm.update', $dcm), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['C' => ['C01', 'C02']],
        ])->assertRedirect(route('guru.bk.asesmen.dcm.show', $dcm));

        $dcm->refresh();
        $this->assertEmpty($dcm->jawaban['A']);
        $this->assertCount(2, $dcm->jawaban['C']);
    }

    public function test_guru_bk_dapat_menghapus_data_dcm(): void
    {
        $guruBk = $this->guruBk();
        $dcm = Dcm::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.dcm.destroy', $dcm))
            ->assertRedirect(route('guru.bk.asesmen.dcm.index'));

        $this->assertDatabaseMissing('dcms', ['id' => $dcm->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_dcm(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.dcm.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.dcm.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.dcm.index'))->assertRedirect(route('login'));
    }
}
