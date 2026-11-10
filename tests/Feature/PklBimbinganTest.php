<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\PenempatanPkl;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PklBimbinganTest extends TestCase
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

    private function pembimbing(string $nama = 'Pembimbing PKL Satu', string $nip = '9999301'): Guru
    {
        $guru = Guru::create(['nama' => $nama, 'nip' => $nip, 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'pembimbing_pkl'])->id);

        return $guru;
    }

    private function penempatan(Guru $pembimbing, Siswa $siswa): PenempatanPkl
    {
        return PenempatanPkl::create([
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'nama_iduka' => 'PT Bimbingan Uji',
            'guru_pembimbing_id' => $pembimbing->id,
        ]);
    }

    public function test_pembimbing_dapat_mengisi_periode_pkl_untuk_semua_siswa_bimbingan_sekaligus(): void
    {
        $pembimbing = $this->pembimbing();
        $siswaSatu = Siswa::firstOrFail();
        $siswaDua = Siswa::where('id', '!=', $siswaSatu->id)->firstOrFail();
        $penempatanSatu = $this->penempatan($pembimbing, $siswaSatu);
        $penempatanDua = $this->penempatan($pembimbing, $siswaDua);

        $this->sebagai($pembimbing)->get(route('guru.pkl.index'))->assertOk()->assertSee('PT Bimbingan Uji');

        $this->sebagai($pembimbing)->put(route('guru.pkl.periode'), [
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-09-30',
        ])->assertRedirect();

        $this->assertSame('2026-07-01', $penempatanSatu->refresh()->tanggal_mulai->toDateString());
        $this->assertSame('2026-09-30', $penempatanSatu->tanggal_selesai->toDateString());
        $this->assertSame('2026-07-01', $penempatanDua->refresh()->tanggal_mulai->toDateString());
        $this->assertSame('2026-09-30', $penempatanDua->tanggal_selesai->toDateString());
    }

    public function test_periode_massal_tidak_mengubah_siswa_bimbingan_pembimbing_lain(): void
    {
        $pembimbingA = $this->pembimbing('Pembimbing A', '9999302');
        $pembimbingB = $this->pembimbing('Pembimbing B', '9999303');
        $siswaA = Siswa::firstOrFail();
        $siswaB = Siswa::where('id', '!=', $siswaA->id)->firstOrFail();
        $penempatanA = $this->penempatan($pembimbingA, $siswaA);
        $penempatanB = $this->penempatan($pembimbingB, $siswaB);

        $this->sebagai($pembimbingA)->put(route('guru.pkl.periode'), [
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-09-30',
        ])->assertRedirect();

        $this->assertSame('2026-07-01', $penempatanA->refresh()->tanggal_mulai->toDateString());
        $this->assertNull($penempatanB->refresh()->tanggal_mulai);
    }

    public function test_pembimbing_dapat_mengisi_catatan_perkembangan_dengan_predikat(): void
    {
        $pembimbing = $this->pembimbing();
        $siswa = Siswa::firstOrFail();
        $penempatan = $this->penempatan($pembimbing, $siswa);

        $this->sebagai($pembimbing)->put(route('guru.pkl.update', $penempatan), [
            'predikat_etika' => 'Sangat Baik',
            'catatan_etika' => 'Sangat disiplin.',
            'predikat_kemandirian' => 'Baik',
            'catatan_kemandirian' => 'Cukup mandiri.',
            'predikat_administrasi' => 'Cukup',
            'catatan_administrasi' => 'Jurnal kadang terlambat.',
            'predikat_progres' => '75%',
            'catatan_progres' => 'Laporan hampir selesai.',
            'catatan' => 'Berjalan lancar.',
        ])->assertRedirect();

        $penempatan->refresh();
        $this->assertSame('Sangat Baik', $penempatan->predikat_etika);
        $this->assertSame('Sangat disiplin.', $penempatan->catatan_etika);
        $this->assertSame('Baik', $penempatan->predikat_kemandirian);
        $this->assertSame('Cukup', $penempatan->predikat_administrasi);
        $this->assertSame('75%', $penempatan->predikat_progres);
        $this->assertSame('Laporan hampir selesai.', $penempatan->catatan_progres);
        $this->assertSame('Berjalan lancar.', $penempatan->catatan);
    }

    public function test_pembimbing_tidak_bisa_mengubah_catatan_siswa_bukan_bimbingannya(): void
    {
        $pembimbingA = $this->pembimbing('Pembimbing A', '9999302');
        $pembimbingB = $this->pembimbing('Pembimbing B', '9999303');
        $siswa = Siswa::firstOrFail();
        $penempatan = $this->penempatan($pembimbingB, $siswa);

        $this->sebagai($pembimbingA)->get(route('guru.pkl.index'))->assertOk()->assertDontSee('PT Bimbingan Uji');

        $this->sebagai($pembimbingA)->put(route('guru.pkl.update', $penempatan), [
            'predikat_etika' => 'Baik',
        ])->assertStatus(403);
    }

    public function test_role_selain_pembimbing_pkl_ditolak_mengakses_menu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'pembimbing_pkl'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.pkl.index'))->assertRedirect(route('login'));
    }
}
