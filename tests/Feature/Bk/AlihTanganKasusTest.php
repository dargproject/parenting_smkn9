<?php

namespace Tests\Feature\Bk;

use App\Models\AlihTanganKasus;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlihTanganKasusTest extends TestCase
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

    private function konselorKedua(): Guru
    {
        $guru = Guru::create(['nama' => 'Konselor Kedua', 'nip' => '999888777', 'password' => bcrypt('password')]);
        $guru->roles()->attach(Role::where('name', 'guru_bk')->first());

        return $guru;
    }

    private function buatKasus(Guru $konselor, string $judul = 'Kasus Alih Tangan'): KasusBk
    {
        return KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => $judul,
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $konselor->id,
        ]);
    }

    public function test_guru_bk_dapat_mengalihkan_kasus_miliknya_dan_kepemilikan_berpindah(): void
    {
        $asal = $this->guruBk();
        $tujuan = $this->konselorKedua();
        $kasusBk = $this->buatKasus($asal);

        $response = $this->sebagai($asal)->post(route('guru.bk.alih-tangan.store'), [
            'kasus_bk_id' => $kasusBk->id,
            'konselor_tujuan_id' => $tujuan->id,
            'tanggal_alih' => now()->toDateString(),
            'alasan_alih' => 'Membutuhkan penanganan lebih lanjut.',
        ]);

        $record = AlihTanganKasus::where('kasus_bk_id', $kasusBk->id)->first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('guru.bk.alih-tangan.show', $record));

        $this->assertSame($asal->id, $record->konselor_asal_id);
        $this->assertSame($tujuan->id, $record->konselor_tujuan_id);

        $kasusBk->refresh();
        $this->assertSame($tujuan->id, $kasusBk->konselor_id);
    }

    public function test_guru_bk_tidak_bisa_mengalihkan_kasus_milik_orang_lain(): void
    {
        $asal = $this->guruBk();
        $penyusup = $this->konselorKedua();
        $kasusBk = $this->buatKasus($asal);

        $this->sebagai($penyusup)->post(route('guru.bk.alih-tangan.store'), [
            'kasus_bk_id' => $kasusBk->id,
            'konselor_tujuan_id' => $asal->id,
            'tanggal_alih' => now()->toDateString(),
        ])->assertForbidden();

        $kasusBk->refresh();
        $this->assertSame($asal->id, $kasusBk->konselor_id);
    }

    public function test_pengalih_dan_penerima_dapat_melihat_catatan_dan_diperbarui(): void
    {
        $asal = $this->guruBk();
        $tujuan = $this->konselorKedua();
        $ketiga = Guru::create(['nama' => 'Konselor Ketiga', 'nip' => '111222333', 'password' => bcrypt('password')]);
        $ketiga->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = $this->buatKasus($asal);
        $record = AlihTanganKasus::create([
            'kasus_bk_id' => $kasusBk->id,
            'konselor_asal_id' => $asal->id,
            'konselor_tujuan_id' => $tujuan->id,
            'tanggal_alih' => now()->toDateString(),
        ]);
        $kasusBk->update(['konselor_id' => $tujuan->id]);

        $this->sebagai($asal)->get(route('guru.bk.alih-tangan.show', $record))->assertOk();
        $this->sebagai($tujuan)->get(route('guru.bk.alih-tangan.show', $record))->assertOk();

        // Penerima mengalihkan lagi ke konselor ketiga -- kepemilikan kasus pindah lagi.
        $this->sebagai($tujuan)->put(route('guru.bk.alih-tangan.update', $record), [
            'kasus_bk_id' => $kasusBk->id,
            'konselor_tujuan_id' => $ketiga->id,
            'tanggal_alih' => now()->toDateString(),
        ])->assertRedirect(route('guru.bk.alih-tangan.show', $record));

        $kasusBk->refresh();
        $this->assertSame($ketiga->id, $kasusBk->konselor_id);
    }

    public function test_guru_bk_lain_yang_tidak_terlibat_ditolak_mengakses_catatan(): void
    {
        $asal = $this->guruBk();
        $tujuan = $this->konselorKedua();
        $takTerlibat = Guru::create(['nama' => 'Konselor Tak Terlibat', 'nip' => '444555666', 'password' => bcrypt('password')]);
        $takTerlibat->roles()->attach(Role::where('name', 'guru_bk')->first());

        $kasusBk = $this->buatKasus($asal);
        $record = AlihTanganKasus::create([
            'kasus_bk_id' => $kasusBk->id,
            'konselor_asal_id' => $asal->id,
            'konselor_tujuan_id' => $tujuan->id,
            'tanggal_alih' => now()->toDateString(),
        ]);

        $this->sebagai($takTerlibat)->get(route('guru.bk.alih-tangan.show', $record))->assertForbidden();
        $this->sebagai($takTerlibat)->delete(route('guru.bk.alih-tangan.destroy', $record))->assertForbidden();
    }

    public function test_guru_bk_dapat_menghapus_catatan_tanpa_mengembalikan_kepemilikan(): void
    {
        $asal = $this->guruBk();
        $tujuan = $this->konselorKedua();
        $kasusBk = $this->buatKasus($asal);
        $record = AlihTanganKasus::create([
            'kasus_bk_id' => $kasusBk->id,
            'konselor_asal_id' => $asal->id,
            'konselor_tujuan_id' => $tujuan->id,
            'tanggal_alih' => now()->toDateString(),
        ]);
        $kasusBk->update(['konselor_id' => $tujuan->id]);

        $this->sebagai($tujuan)->delete(route('guru.bk.alih-tangan.destroy', $record))
            ->assertRedirect(route('guru.bk.alih-tangan.index'));

        $this->assertDatabaseMissing('alih_tangan_kasuses', ['id' => $record->id]);
        $kasusBk->refresh();
        $this->assertSame($tujuan->id, $kasusBk->konselor_id);
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_alih_tangan(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.alih-tangan.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.alih-tangan.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.alih-tangan.index'))->assertRedirect(route('login'));
    }
}
