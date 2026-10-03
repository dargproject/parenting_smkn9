<?php

namespace Tests\Feature\Bk;

use App\Models\BimbinganKelompok;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BimbinganKelompokTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_layanan_konseling_kelompok_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::limit(3)->get();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.kelompok.store'), [
            'siswa_ids' => $siswas->pluck('id')->all(),
            'tanggal_layanan' => now()->toDateString(),
            'judul' => 'Bimbingan kelompok motivasi belajar',
            'deskripsi' => 'Beberapa siswa menunjukkan penurunan motivasi belajar bersama.',
            'tindak_lanjut' => 'Pemantauan lanjutan dua minggu ke depan.',
        ]);

        $kasusBk = KasusBk::where('judul', 'Bimbingan kelompok motivasi belajar')->first();
        $this->assertNotNull($kasusBk);
        $this->assertSame($siswas[0]->id, $kasusBk->siswa_id);

        $kelompok = BimbinganKelompok::where('kasus_bk_id', $kasusBk->id)->first();
        $this->assertNotNull($kelompok);
        $response->assertRedirect(route('guru.bk.kelompok.show', $kelompok));

        $this->assertSame(3, $kelompok->pesertas()->count());
        foreach ($siswas as $siswa) {
            $this->assertDatabaseHas('bimbingan_kelompok_siswas', ['bimbingan_kelompok_id' => $kelompok->id, 'siswa_id' => $siswa->id]);
        }
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_peserta_layanan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::limit(3)->get();

        $kasusBk = KasusBk::create([
            'siswa_id' => $siswas[0]->id,
            'judul' => 'Penanganan Awal',
            'kategori' => 'Konseling Kelompok',
            'deskripsi' => 'Uraian awal.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $kelompok = BimbinganKelompok::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_layanan' => now()->toDateString()]);
        $kelompok->siswas()->attach([$siswas[0]->id, $siswas[1]->id]);

        $this->sebagai($guruBk)->get(route('guru.bk.kelompok.show', $kelompok))->assertOk()->assertSee($siswas[0]->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.kelompok.update', $kelompok), [
            'siswa_ids' => [$siswas[1]->id, $siswas[2]->id],
            'tanggal_layanan' => now()->toDateString(),
            'judul' => 'Penanganan Sudah Diubah',
            'deskripsi' => 'Uraian sudah diubah.',
        ])->assertRedirect(route('guru.bk.kelompok.show', $kelompok));

        $this->assertSame(2, $kelompok->pesertas()->count());
        $this->assertDatabaseMissing('bimbingan_kelompok_siswas', ['bimbingan_kelompok_id' => $kelompok->id, 'siswa_id' => $siswas[0]->id]);
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id, 'judul' => 'Penanganan Sudah Diubah']);
    }

    public function test_guru_bk_dapat_menghapus_layanan_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::limit(2)->get();
        $kasusBk = KasusBk::create([
            'siswa_id' => $siswas[0]->id,
            'judul' => 'Penanganan Akan Dihapus',
            'kategori' => 'Konseling Kelompok',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);
        $kelompok = BimbinganKelompok::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_layanan' => now()->toDateString()]);
        $kelompok->siswas()->attach($siswas->pluck('id'));

        $this->sebagai($guruBk)->delete(route('guru.bk.kelompok.destroy', $kelompok))
            ->assertRedirect(route('guru.bk.kelompok.index'));

        $this->assertDatabaseMissing('bimbingan_kelompoks', ['id' => $kelompok->id]);
        $this->assertDatabaseMissing('bimbingan_kelompok_siswas', ['bimbingan_kelompok_id' => $kelompok->id]);
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id]);
    }

    public function test_guru_bk_tidak_bisa_mengakses_layanan_milik_konselor_lain(): void
    {
        $guruBkA = $this->guruBk();
        $guruBkB = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $siswas = Siswa::limit(2)->get();
        $kasusBk = KasusBk::create([
            'siswa_id' => $siswas[0]->id,
            'judul' => 'Penanganan Rahasia A',
            'kategori' => 'Konseling Kelompok',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBkA->id,
        ]);
        $kelompok = BimbinganKelompok::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_layanan' => now()->toDateString()]);
        $kelompok->siswas()->attach($siswas->pluck('id'));

        $this->sebagai($guruBkB)->get(route('guru.bk.kelompok.show', $kelompok))->assertForbidden();
        $this->sebagai($guruBkB)->delete(route('guru.bk.kelompok.destroy', $kelompok))->assertForbidden();
        $this->assertDatabaseHas('bimbingan_kelompoks', ['id' => $kelompok->id]);

        $this->sebagai($guruBkB)->get(route('guru.bk.kelompok.index'))->assertOk()->assertDontSee('Penanganan Rahasia A');
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_konseling_kelompok(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.kelompok.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.kelompok.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.kelompok.index'))->assertRedirect(route('login'));
    }
}
