<?php

namespace Tests\Feature\Bk;

use App\Models\Akpd;
use App\Models\Guru;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkpdTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_data_akpd_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $jawaban = [];
        foreach (range(1, 50) as $no) {
            $jawaban[$no] = $no % 2 === 0 ? 'Ya' : 'Tidak';
        }

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.akpd.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => $jawaban,
        ]);

        $akpd = Akpd::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($akpd);
        $response->assertRedirect(route('guru.bk.asesmen.akpd.show', $akpd));
        $this->assertSame('Ya', $akpd->jawaban[2]);
        $this->assertSame('Tidak', $akpd->jawaban[1]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.index'))->assertOk();
        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.index', ['tingkat' => 'XI']))->assertOk()->assertSee('AKPD')->assertSee('DCM')->assertSee('Gaya Belajar')->assertSee('Sosiometri')->assertSee('Bakat Minat');
        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.akpd.index'))->assertOk()->assertSee($siswa->nama);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_akpd(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $akpd = Akpd::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => array_fill(1, 50, 'Tidak'),
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.akpd.show', $akpd))->assertOk()->assertSee($siswa->nama);

        $jawabanBaru = array_fill(1, 50, 'Ya');
        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.akpd.update', $akpd), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => $jawabanBaru,
        ])->assertRedirect(route('guru.bk.asesmen.akpd.show', $akpd));

        $akpd->refresh();
        $this->assertSame('Ya', $akpd->jawaban[1]);
    }

    public function test_aspect_answers_menghitung_ya_per_aspek_dengan_benar(): void
    {
        $jawaban = array_fill(1, 50, 'Tidak');
        $jawaban[1] = 'Ya';
        $jawaban[2] = 'Ya';

        $akpd = Akpd::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => $jawaban,
        ]);

        $groups = $akpd->aspectAnswers();

        $this->assertSame('Pribadi', $groups[0]['aspect']);
        $this->assertSame(2, $groups[0]['ya_count']);
        $this->assertSame(10, $groups[0]['total']);
        $this->assertSame(0, $groups[1]['ya_count']);
    }

    public function test_guru_bk_dapat_menghapus_data_akpd(): void
    {
        $guruBk = $this->guruBk();
        $akpd = Akpd::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.akpd.destroy', $akpd))
            ->assertRedirect(route('guru.bk.asesmen.akpd.index'));

        $this->assertDatabaseMissing('akpds', ['id' => $akpd->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_akpd(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $akpd = Akpd::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.akpd.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.akpd.show', $akpd))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.akpd.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.index'))->assertRedirect(route('login'));
        $this->get(route('guru.bk.asesmen.akpd.index'))->assertRedirect(route('login'));
    }
}
