<?php

namespace Tests\Feature\Bk;

use App\Models\GayaBelajar;
use App\Models\Guru;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GayaBelajarTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_data_gaya_belajar_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.gaya-belajar.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [
                'Visual' => [0, 1, 2],
                'Auditorial' => [0],
                'Kinestetik' => [],
            ],
            'hasil' => 'Visual',
        ]);

        $gb = GayaBelajar::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($gb);
        $response->assertRedirect(route('guru.bk.asesmen.gaya-belajar.show', $gb));
        $this->assertSame(3, $gb->visual);
        $this->assertSame(1, $gb->auditori);
        $this->assertSame(0, $gb->kinestetik);
        $this->assertSame('Visual', $gb->hasil);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.gaya-belajar.index'))->assertOk()->assertSee($siswa->nama);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $gb = GayaBelajar::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Visual' => [], 'Auditorial' => [], 'Kinestetik' => []],
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.gaya-belajar.show', $gb))->assertOk()->assertSee($siswa->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.gaya-belajar.update', $gb), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Visual' => [], 'Auditorial' => [0, 1], 'Kinestetik' => []],
            'hasil' => 'Auditorial',
        ])->assertRedirect(route('guru.bk.asesmen.gaya-belajar.show', $gb));

        $gb->refresh();
        $this->assertSame(2, $gb->auditori);
        $this->assertSame('Auditorial', $gb->hasil);
    }

    public function test_guru_bk_dapat_menghapus_data_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        $gb = GayaBelajar::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.gaya-belajar.destroy', $gb))
            ->assertRedirect(route('guru.bk.asesmen.gaya-belajar.index'));

        $this->assertDatabaseMissing('gaya_belajars', ['id' => $gb->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_gaya_belajar(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.gaya-belajar.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.gaya-belajar.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.gaya-belajar.index'))->assertRedirect(route('login'));
    }
}
