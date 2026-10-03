<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Sosiometri;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SosiometriTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_data_sosiometri_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::limit(3)->get();
        $pengisi = $siswas[0];
        $teman1 = $siswas[1];
        $teman2 = $siswas[2];

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.sosiometri.store'), [
            'siswa_id' => $pengisi->id,
            'tanggal' => now()->toDateString(),
            'pilihan' => [
                'Q1' => [$teman1->id, $teman2->id],
                'Q2' => [$teman2->id],
            ],
        ]);

        $sosiometri = Sosiometri::where('siswa_id', $pengisi->id)->first();
        $this->assertNotNull($sosiometri);
        $response->assertRedirect(route('guru.bk.asesmen.sosiometri.show', $sosiometri));

        $this->assertSame(2, $sosiometri->respons()->where('pertanyaan', 'Q1')->count());
        $this->assertSame(1, $sosiometri->respons()->where('pertanyaan', 'Q2')->count());
        $this->assertDatabaseHas('sosiometri_respons', ['sosiometri_id' => $sosiometri->id, 'siswa_dipilih_id' => $teman1->id, 'pertanyaan' => 'Q1', 'urutan' => 1]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.sosiometri.index'))->assertOk()->assertSee($pengisi->nama);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_sosiometri(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::limit(3)->get();
        $pengisi = $siswas[0];

        $sosiometri = Sosiometri::create([
            'siswa_id' => $pengisi->id,
            'tanggal' => now()->toDateString(),
            'jumlah_pilihan' => 3,
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.sosiometri.show', $sosiometri))->assertOk()->assertSee($pengisi->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.sosiometri.update', $sosiometri), [
            'siswa_id' => $pengisi->id,
            'tanggal' => now()->toDateString(),
            'pilihan' => ['Q3' => [$siswas[1]->id]],
        ])->assertRedirect(route('guru.bk.asesmen.sosiometri.show', $sosiometri));

        $this->assertSame(1, $sosiometri->respons()->where('pertanyaan', 'Q3')->count());
    }

    public function test_guru_bk_dapat_menghapus_data_sosiometri(): void
    {
        $guruBk = $this->guruBk();
        $sosiometri = Sosiometri::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jumlah_pilihan' => 3,
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.sosiometri.destroy', $sosiometri))
            ->assertRedirect(route('guru.bk.asesmen.sosiometri.index'));

        $this->assertDatabaseMissing('sosiometris', ['id' => $sosiometri->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_sosiometri(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.sosiometri.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.sosiometri.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.sosiometri.index'))->assertRedirect(route('login'));
    }
}
