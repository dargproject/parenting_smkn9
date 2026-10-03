<?php

namespace Tests\Feature\Bk;

use App\Models\BimbinganIndividu;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BimbinganIndividuTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_layanan_konseling_individu_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.individu.store'), [
            'siswa_id' => $siswa->id,
            'tanggal_layanan' => now()->toDateString(),
            'judul' => 'Konseling terkait motivasi belajar',
            'deskripsi' => 'Siswa menunjukkan penurunan motivasi belajar.',
            'tindak_lanjut' => 'Pemantauan lanjutan dua minggu ke depan.',
        ]);

        $kasusBk = KasusBk::where('judul', 'Konseling terkait motivasi belajar')->first();
        $this->assertNotNull($kasusBk);

        $individu = BimbinganIndividu::where('kasus_bk_id', $kasusBk->id)->first();
        $this->assertNotNull($individu);
        $response->assertRedirect(route('guru.bk.individu.show', $individu));

        $this->assertDatabaseHas('kasus_bks', [
            'id' => $kasusBk->id,
            'siswa_id' => $siswa->id,
            'kategori' => 'Konseling Individu',
            'konselor_id' => $guruBk->id,
            'status' => 'antrean',
        ]);
        $this->assertSame(now()->toDateString(), $individu->tanggal_layanan->toDateString());
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_layanan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $kasusBk = KasusBk::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Penanganan Awal',
            'kategori' => 'Konseling Individu',
            'deskripsi' => 'Uraian awal.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $individu = BimbinganIndividu::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_layanan' => now()->toDateString(),
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.individu.show', $individu))->assertOk()->assertSee('Penanganan Awal');

        $this->sebagai($guruBk)->put(route('guru.bk.individu.update', $individu), [
            'siswa_id' => $siswa->id,
            'tanggal_layanan' => now()->toDateString(),
            'judul' => 'Penanganan Sudah Diubah',
            'deskripsi' => 'Uraian sudah diubah.',
        ])->assertRedirect(route('guru.bk.individu.show', $individu));

        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id, 'judul' => 'Penanganan Sudah Diubah']);
    }

    public function test_guru_bk_dapat_menghapus_layanan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Penanganan Akan Dihapus',
            'kategori' => 'Konseling Individu',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $individu = BimbinganIndividu::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_layanan' => now()->toDateString(),
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.individu.destroy', $individu))
            ->assertRedirect(route('guru.bk.individu.index'));

        $this->assertDatabaseMissing('bimbingan_individus', ['id' => $individu->id]);
        // Kasus BK yang dibuat otomatis sengaja tidak ikut terhapus.
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id]);
    }

    public function test_guru_bk_tidak_bisa_mengakses_layanan_milik_konselor_lain(): void
    {
        $guruBkA = $this->guruBk();
        $guruBkB = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Penanganan Rahasia A',
            'kategori' => 'Konseling Individu',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBkA->id,
        ]);
        $individu = BimbinganIndividu::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_layanan' => now()->toDateString(),
        ]);

        $this->sebagai($guruBkB)->get(route('guru.bk.individu.show', $individu))->assertForbidden();
        $this->sebagai($guruBkB)->delete(route('guru.bk.individu.destroy', $individu))->assertForbidden();
        $this->assertDatabaseHas('bimbingan_individus', ['id' => $individu->id]);

        $this->sebagai($guruBkB)->get(route('guru.bk.individu.index'))->assertOk()->assertDontSee('Penanganan Rahasia A');
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_konseling_individu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Penanganan Rahasia',
            'kategori' => 'Konseling Individu',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $this->guruBk()->id,
        ]);
        $individu = BimbinganIndividu::create([
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_layanan' => now()->toDateString(),
        ]);

        $this->sebagai($guruMapel)->get(route('guru.bk.individu.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.individu.show', $individu))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.individu.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.individu.index'))->assertRedirect(route('login'));
    }
}
