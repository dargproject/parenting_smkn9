<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KunjunganRumah;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KunjunganRumahTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_kunjungan_rumah_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.kunjungan-rumah.store'), [
            'siswa_id' => $siswa->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'diproses',
            'judul' => 'Kunjungan terkait kehadiran siswa',
            'deskripsi' => 'Siswa sering tidak masuk tanpa keterangan.',
        ]);

        $kasusBk = KasusBk::where('judul', 'Kunjungan terkait kehadiran siswa')->first();
        $this->assertNotNull($kasusBk);

        $kunjungan = KunjunganRumah::where('kasus_bk_id', $kasusBk->id)->first();
        $this->assertNotNull($kunjungan);
        $response->assertRedirect(route('guru.bk.kunjungan-rumah.show', $kunjungan));

        $this->assertSame('diproses', $kunjungan->status);
        $this->assertDatabaseHas('kasus_bks', [
            'id' => $kasusBk->id,
            'kategori' => 'Kunjungan Rumah',
            'konselor_id' => $guruBk->id,
        ]);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_status_kunjungan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $kasusBk = KasusBk::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Penanganan Awal',
            'kategori' => 'Kunjungan Rumah',
            'deskripsi' => 'Uraian awal.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $kunjungan = KunjunganRumah::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'diproses',
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.kunjungan-rumah.show', $kunjungan))->assertOk()->assertSee($siswa->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.kunjungan-rumah.update', $kunjungan), [
            'siswa_id' => $siswa->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'ditunda',
            'judul' => 'Penanganan Sudah Diubah',
            'deskripsi' => 'Uraian sudah diubah.',
        ])->assertRedirect(route('guru.bk.kunjungan-rumah.show', $kunjungan));

        $kunjungan->refresh();
        $this->assertSame('ditunda', $kunjungan->status);
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id, 'judul' => 'Penanganan Sudah Diubah']);
    }

    public function test_guru_bk_dapat_menghapus_kunjungan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Penanganan Akan Dihapus',
            'kategori' => 'Kunjungan Rumah',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $kunjungan = KunjunganRumah::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'diproses',
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.kunjungan-rumah.destroy', $kunjungan))
            ->assertRedirect(route('guru.bk.kunjungan-rumah.index'));

        $this->assertDatabaseMissing('kunjungan_rumahs', ['id' => $kunjungan->id]);
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id]);
    }

    public function test_guru_bk_tidak_bisa_mengakses_kunjungan_milik_konselor_lain(): void
    {
        $guruBkA = $this->guruBk();
        $guruBkB = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kunjungan Rahasia A',
            'kategori' => 'Kunjungan Rumah',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBkA->id,
        ]);
        $kunjungan = KunjunganRumah::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'diproses',
        ]);

        $this->sebagai($guruBkB)->get(route('guru.bk.kunjungan-rumah.show', $kunjungan))->assertForbidden();
        $this->sebagai($guruBkB)->delete(route('guru.bk.kunjungan-rumah.destroy', $kunjungan))->assertForbidden();
        $this->assertDatabaseHas('kunjungan_rumahs', ['id' => $kunjungan->id]);

        $this->sebagai($guruBkB)->get(route('guru.bk.kunjungan-rumah.index'))->assertOk()->assertDontSee('Kunjungan Rahasia A');
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_kunjungan_rumah(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.kunjungan-rumah.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.kunjungan-rumah.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.kunjungan-rumah.index'))->assertRedirect(route('login'));
    }
}
