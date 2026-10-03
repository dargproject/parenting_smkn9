<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\PengunduranDiri;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengunduranDiriTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_catatan_pengunduran_diri_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.pengunduran-diri.store'), [
            'siswa_id' => $siswa->id,
            'tanggal_pengunduran' => now()->toDateString(),
            'nama_ortu_wali' => 'Budi Santoso',
            'alamat_ortu_wali' => 'Jl. Merdeka No. 1, Malang',
            'alasan_pengunduran' => 'Pindah mengikuti orang tua ke kota lain.',
        ]);

        $record = PengunduranDiri::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('guru.bk.pengunduran-diri.show', $record));
        $this->assertSame('Budi Santoso', $record->nama_ortu_wali);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_catatan(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $record = PengunduranDiri::create([
            'siswa_id' => $siswa->id,
            'nama_ortu_wali' => 'Nama Awal',
            'alamat_ortu_wali' => 'Alamat awal.',
            'alasan_pengunduran' => 'Alasan awal.',
            'tanggal_pengunduran' => now()->toDateString(),
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.pengunduran-diri.show', $record))->assertOk()->assertSee($siswa->nama);

        $this->sebagai($guruBk)->put(route('guru.bk.pengunduran-diri.update', $record), [
            'siswa_id' => $siswa->id,
            'tanggal_pengunduran' => now()->toDateString(),
            'nama_ortu_wali' => 'Nama Diubah',
            'alamat_ortu_wali' => 'Alamat diubah.',
            'alasan_pengunduran' => 'Alasan diubah.',
        ])->assertRedirect(route('guru.bk.pengunduran-diri.show', $record));

        $this->assertDatabaseHas('pengunduran_diris', ['id' => $record->id, 'nama_ortu_wali' => 'Nama Diubah']);
    }

    public function test_guru_bk_dapat_menghapus_catatan(): void
    {
        $guruBk = $this->guruBk();
        $record = PengunduranDiri::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'nama_ortu_wali' => 'X',
            'alamat_ortu_wali' => 'X',
            'alasan_pengunduran' => 'X',
            'tanggal_pengunduran' => now()->toDateString(),
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.pengunduran-diri.destroy', $record))
            ->assertRedirect(route('guru.bk.pengunduran-diri.index'));

        $this->assertDatabaseMissing('pengunduran_diris', ['id' => $record->id]);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_pengunduran_diri(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.pengunduran-diri.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.pengunduran-diri.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.pengunduran-diri.index'))->assertRedirect(route('login'));
    }
}
