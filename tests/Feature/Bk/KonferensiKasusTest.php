<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KonferensiKasus;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonferensiKasusTest extends TestCase
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

    private function buatKasus(Guru $konselor): KasusBk
    {
        return KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Kasus Konferensi',
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $konselor->id,
        ]);
    }

    public function test_guru_bk_dapat_membuat_konferensi_kasus_baru_dengan_peserta(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = $this->buatKasus($guruBk);

        $response = $this->sebagai($guruBk)->post(route('guru.bk.konferensi.store'), [
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_konferensi' => now()->toDateString(),
            'tempat_pertemuan' => 'Ruang BK',
            'peserta' => [
                ['nama_peserta' => 'Bu Siti', 'peran_peserta' => 'Wali Kelas'],
                ['nama_peserta' => 'Pak Joko', 'peran_peserta' => 'Orang Tua'],
            ],
        ]);

        $konferensi = KonferensiKasus::where('kasus_bk_id', $kasusBk->id)->first();
        $this->assertNotNull($konferensi);
        $response->assertRedirect(route('guru.bk.konferensi.show', $konferensi));

        $this->assertSame(2, $konferensi->pesertas()->count());
        $this->assertDatabaseHas('konferensi_kasus_pesertas', ['konferensi_kasus_id' => $konferensi->id, 'nama_peserta' => 'Bu Siti', 'peran_peserta' => 'Wali Kelas']);
    }

    public function test_guru_bk_tidak_bisa_membuat_konferensi_untuk_kasus_milik_orang_lain(): void
    {
        $asal = $this->guruBk();
        $penyusup = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $penyusup->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = $this->buatKasus($asal);

        $this->sebagai($penyusup)->post(route('guru.bk.konferensi.store'), [
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_konferensi' => now()->toDateString(),
            'peserta' => [['nama_peserta' => 'X', 'peran_peserta' => 'Lainnya']],
        ])->assertForbidden();
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_peserta_konferensi_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = $this->buatKasus($guruBk);
        $konferensi = KonferensiKasus::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_konferensi' => now()->toDateString()]);
        $konferensi->pesertas()->create(['nama_peserta' => 'Awal', 'peran_peserta' => 'Guru BK']);

        $this->sebagai($guruBk)->get(route('guru.bk.konferensi.show', $konferensi))->assertOk()->assertSee('Awal');

        $this->sebagai($guruBk)->put(route('guru.bk.konferensi.update', $konferensi), [
            'kasus_bk_id' => $kasusBk->id,
            'tanggal_konferensi' => now()->toDateString(),
            'tempat_pertemuan' => 'Aula',
            'peserta' => [
                ['nama_peserta' => 'Diubah A', 'peran_peserta' => 'Kepala Sekolah'],
                ['nama_peserta' => 'Diubah B', 'peran_peserta' => 'Siswa'],
            ],
        ])->assertRedirect(route('guru.bk.konferensi.show', $konferensi));

        $this->assertSame(2, $konferensi->pesertas()->count());
        $this->assertDatabaseMissing('konferensi_kasus_pesertas', ['nama_peserta' => 'Awal']);
    }

    public function test_guru_bk_dapat_menghapus_konferensi_miliknya(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = $this->buatKasus($guruBk);
        $konferensi = KonferensiKasus::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_konferensi' => now()->toDateString()]);
        $konferensi->pesertas()->create(['nama_peserta' => 'X', 'peran_peserta' => 'Lainnya']);

        $this->sebagai($guruBk)->delete(route('guru.bk.konferensi.destroy', $konferensi))
            ->assertRedirect(route('guru.bk.konferensi.index'));

        $this->assertDatabaseMissing('konferensi_kasuses', ['id' => $konferensi->id]);
        $this->assertDatabaseMissing('konferensi_kasus_pesertas', ['konferensi_kasus_id' => $konferensi->id]);
        $this->assertDatabaseHas('kasus_bks', ['id' => $kasusBk->id]);
    }

    public function test_guru_bk_tidak_bisa_mengakses_konferensi_milik_konselor_lain(): void
    {
        $asal = $this->guruBk();
        $lain = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $lain->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = $this->buatKasus($asal);
        $konferensi = KonferensiKasus::create(['kasus_bk_id' => $kasusBk->id, 'tanggal_konferensi' => now()->toDateString()]);

        $this->sebagai($lain)->get(route('guru.bk.konferensi.show', $konferensi))->assertForbidden();
        $this->sebagai($lain)->delete(route('guru.bk.konferensi.destroy', $konferensi))->assertForbidden();
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_konferensi(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.konferensi.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.konferensi.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.konferensi.index'))->assertRedirect(route('login'));
    }
}
