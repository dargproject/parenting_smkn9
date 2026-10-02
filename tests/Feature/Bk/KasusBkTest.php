<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KategoriKasus;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasusBkTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_kasus_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();
        $kategori = KategoriKasus::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.kasus.store'), [
            'siswa_id' => $siswa->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Konseling Individu Uji',
            'tanggal_mulai' => now()->toDateString(),
            'deskripsi' => 'Deskripsi uji coba.',
            'prioritas' => 'sedang',
        ]);

        $kasusBk = KasusBk::where('judul', 'Konseling Individu Uji')->first();
        $response->assertRedirect(route('guru.bk.kasus.show', $kasusBk));

        $this->assertDatabaseHas('kasus_bks', [
            'siswa_id' => $siswa->id,
            'judul' => 'Konseling Individu Uji',
            'konselor_id' => $guruBk->id,
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'kategori_id' => $kategori->id,
        ]);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_kasus_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();
        $ta = TahunAjaran::where('is_active', true)->first();

        $kasusBk = KasusBk::create([
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $ta?->id,
            'judul' => 'Kasus Awal',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi awal.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.kasus.show', $kasusBk))->assertOk()->assertSee('Kasus Awal');

        $this->sebagai($guruBk)->put(route('guru.bk.kasus.update', $kasusBk), [
            'siswa_id' => $siswa->id,
            'judul' => 'Kasus Sudah Diubah',
            'tanggal_mulai' => now()->toDateString(),
            'deskripsi' => 'Deskripsi sudah diubah.',
            'prioritas' => 'tinggi',
        ])->assertRedirect(route('guru.bk.kasus.show', $kasusBk));

        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id, 'judul' => 'Kasus Sudah Diubah', 'prioritas' => 'tinggi']);
    }

    public function test_guru_bk_dapat_mengubah_status_kasus_dan_tanggal_selesai_otomatis_terisi(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kasus Status',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->patch(route('guru.bk.kasus.status', $kasusBk), ['status' => 'selesai'])
            ->assertRedirect();

        $kasusBk->refresh();
        $this->assertSame('selesai', $kasusBk->status);
        $this->assertNotNull($kasusBk->tanggal_selesai);
    }

    public function test_guru_bk_dapat_menghapus_kasus_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kasus Akan Dihapus',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.kasus.destroy', $kasusBk))
            ->assertRedirect(route('guru.bk.kasus.index'));

        $this->assertDatabaseMissing('kasus_bks', ['id' => $kasusBk->id]);
    }

    public function test_guru_bk_tidak_bisa_mengakses_kasus_milik_konselor_lain(): void
    {
        $guruBkA = $this->guruBk();
        $guruBkB = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusMilikA = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kasus Rahasia A',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBkA->id,
        ]);

        $this->sebagai($guruBkB)->get(route('guru.bk.kasus.show', $kasusMilikA))->assertForbidden();
        $this->sebagai($guruBkB)->delete(route('guru.bk.kasus.destroy', $kasusMilikA))->assertForbidden();
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusMilikA->id]);

        // Index guru_bk B tidak boleh menampilkan kasus milik A.
        $this->sebagai($guruBkB)->get(route('guru.bk.kasus.index'))->assertOk()->assertDontSee('Kasus Rahasia A');
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_semua_halaman_bk(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $kasusBk = KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kasus Rahasia',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $this->guruBk()->id,
        ]);

        $this->sebagai($guruMapel)->get(route('guru.bk.dashboard'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.kasus.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.kasus.show', $kasusBk))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->get(route('guru.bk.riwayat-siswa', Siswa::firstOrFail()))->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.dashboard'))->assertRedirect(route('login'));
        $this->get(route('guru.bk.kasus.index'))->assertRedirect(route('login'));
    }

    public function test_endpoint_jejak_rekam_mengembalikan_data_json_yang_benar(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        KasusBk::create([
            'siswa_id' => $siswa->id,
            'judul' => 'Kasus Untuk Jejak Rekam',
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $response = $this->sebagai($guruBk)->getJson(route('guru.bk.riwayat-siswa', $siswa));

        $response->assertOk()->assertJsonStructure([
            'siswa' => ['id', 'nama', 'nis', 'kelas'],
            'poin_pelanggaran',
            'persen_kehadiran',
            'catatan_terakhir',
            'jumlah_kasus',
            'timeline',
        ]);
        $response->assertJsonFragment(['judul' => 'Kasus Untuk Jejak Rekam']);
    }
}
