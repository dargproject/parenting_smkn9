<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaProfilTest extends TestCase
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

    public function test_guru_bk_dapat_melihat_profil_siswa(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $this->sebagai($guruBk)->get(route('guru.bk.siswa.show', $siswa))->assertOk()->assertSee($siswa->nama);
    }

    public function test_guru_bk_dapat_menyimpan_profil_dan_data_keluarga_siswa(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $this->sebagai($guruBk)->put(route('guru.bk.siswa.profil.update', $siswa), [
            'alamat' => 'Jl. Contoh No. 10',
            'tempat_lahir' => 'Malang',
            'hobi' => 'Membaca',
            'rencana_lulus' => 'Kuliah',
        ])->assertRedirect(route('guru.bk.siswa.show', $siswa));

        $this->assertDatabaseHas('profil_siswas', ['siswa_id' => $siswa->id, 'alamat' => 'Jl. Contoh No. 10', 'rencana_lulus' => 'Kuliah']);

        $this->sebagai($guruBk)->put(route('guru.bk.siswa.keluarga.update', $siswa), [
            'nama_ayah' => 'Bapak Contoh',
            'nama_ibu' => 'Ibu Contoh',
            'punya_kamar_sendiri' => '1',
        ])->assertRedirect(route('guru.bk.siswa.show', $siswa));

        $this->assertDatabaseHas('data_keluarga_siswas', ['siswa_id' => $siswa->id, 'nama_ayah' => 'Bapak Contoh', 'punya_kamar_sendiri' => true]);
    }

    public function test_ringkasan_kasus_hanya_menampilkan_kasus_milik_konselor_yang_login(): void
    {
        $guruBkA = $this->guruBk();
        $guruBkB = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $siswa = Siswa::firstOrFail();

        KasusBk::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Kasus Rahasia Milik A',
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBkA->id,
        ]);

        $this->sebagai($guruBkA)->get(route('guru.bk.siswa.show', $siswa))->assertOk()->assertSee('Kasus Rahasia Milik A');
        $this->sebagai($guruBkB)->get(route('guru.bk.siswa.show', $siswa))->assertOk()->assertDontSee('Kasus Rahasia Milik A');
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_profil_siswa(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();
        $siswa = Siswa::firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.siswa.show', $siswa))->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.siswa.show', Siswa::firstOrFail()))->assertRedirect(route('login'));
    }
}
