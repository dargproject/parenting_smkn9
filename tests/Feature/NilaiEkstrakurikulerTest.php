<?php

namespace Tests\Feature;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Guru;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiEkstrakurikulerTest extends TestCase
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

    private function pembina(string $nama = 'Pembina Satu', string $nip = '9999101'): Guru
    {
        $guru = Guru::create(['nama' => $nama, 'nip' => $nip, 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'pembina_ekskul'])->id);

        return $guru;
    }

    public function test_pembina_dapat_mengelola_peserta_dan_nilai_ekskul_miliknya(): void
    {
        $pembina = $this->pembina();
        $ekskul = Ekstrakurikuler::create(['nama_ekskul' => 'Basket', 'guru_id' => $pembina->id, 'is_active' => true]);
        $siswa = Siswa::firstOrFail();

        $this->sebagai($pembina)->get(route('guru.ekskul.index'))->assertOk()->assertSee('Basket');

        $this->sebagai($pembina)->post(route('guru.ekskul.siswa.store'), [
            'ekstrakurikuler_id' => $ekskul->id,
            'siswa_id' => $siswa->id,
        ])->assertRedirect();

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->firstOrFail();
        $this->assertDatabaseHas('ekstrakurikuler_siswas', [
            'ekstrakurikuler_id' => $ekskul->id,
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $tahunAjaranAktif->id,
        ]);

        $this->sebagai($pembina)->post(route('guru.ekskul.nilai.store'), [
            'ekstrakurikuler_id' => $ekskul->id,
            'siswa_id' => $siswa->id,
            'nilai' => 'A',
        ])->assertRedirect();

        $this->assertDatabaseHas('nilai_ekstrakurikulers', [
            'ekstrakurikuler_id' => $ekskul->id,
            'siswa_id' => $siswa->id,
            'nilai' => 'A',
            'guru_id' => $pembina->id,
        ]);

        $roster = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)->where('siswa_id', $siswa->id)->firstOrFail();
        $this->sebagai($pembina)->delete(route('guru.ekskul.siswa.destroy', $roster))->assertRedirect();
        $this->assertDatabaseMissing('ekstrakurikuler_siswas', ['id' => $roster->id]);
    }

    public function test_pembina_tidak_bisa_mengelola_ekskul_milik_pembina_lain(): void
    {
        $pembinaA = $this->pembina('Pembina A', '9999102');
        $pembinaB = $this->pembina('Pembina B', '9999103');
        $ekskulB = Ekstrakurikuler::create(['nama_ekskul' => 'Paduan Suara', 'guru_id' => $pembinaB->id, 'is_active' => true]);
        $siswa = Siswa::firstOrFail();

        $this->sebagai($pembinaA)->get(route('guru.ekskul.index'))->assertOk()->assertDontSee('Paduan Suara');

        $this->sebagai($pembinaA)->post(route('guru.ekskul.siswa.store'), [
            'ekstrakurikuler_id' => $ekskulB->id,
            'siswa_id' => $siswa->id,
        ])->assertStatus(404);

        $this->sebagai($pembinaA)->post(route('guru.ekskul.nilai.store'), [
            'ekstrakurikuler_id' => $ekskulB->id,
            'siswa_id' => $siswa->id,
            'nilai' => 'A',
        ])->assertStatus(404);
    }

    public function test_role_lain_ditolak_mengakses_menu_ekskul(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'pembina_ekskul'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.ekskul.index'))->assertRedirect(route('login'));
    }
}
