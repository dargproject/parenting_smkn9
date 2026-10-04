<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\PrestasiNonAkademik;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestasiNonAkademikTest extends TestCase
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

    private function waliKelas(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'wali_kelas'))->firstOrFail();
    }

    public function test_wali_kelas_dapat_mencatat_dan_menghapus_prestasi_siswa_binaannya(): void
    {
        $wali = $this->waliKelas();
        $kelasWali = Kelas::where('wali_kelas_id', $wali->id)->firstOrFail();
        $siswa = Siswa::where('kelas_id', $kelasWali->id)->firstOrFail();

        $this->sebagai($wali)->post(route('guru.wali-kelas.prestasi.store'), [
            'siswa_id' => $siswa->id,
            'nama_prestasi' => 'Juara 1 Lomba Lari 100m',
            'tingkat' => 'Kecamatan',
            'peringkat' => 'Juara 1',
            'tanggal' => now()->toDateString(),
            'penyelenggara' => 'Dinas Pemuda dan Olahraga',
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('prestasi_non_akademiks', [
            'siswa_id' => $siswa->id,
            'nama_prestasi' => 'Juara 1 Lomba Lari 100m',
            'guru_id' => $wali->id,
        ]);

        $prestasi = PrestasiNonAkademik::where('siswa_id', $siswa->id)->firstOrFail();

        $this->sebagai($wali)->put(route('guru.wali-kelas.prestasi.update', $prestasi), [
            'nama_prestasi' => 'Juara 1 Lomba Lari 100m (Diperbarui)',
            'tingkat' => 'Kabupaten/Kota',
            'tanggal' => now()->toDateString(),
        ])->assertRedirect(route('guru.portal'));

        $prestasi->refresh();
        $this->assertSame('Kabupaten/Kota', $prestasi->tingkat);

        $this->sebagai($wali)->delete(route('guru.wali-kelas.prestasi.destroy', $prestasi))->assertRedirect(route('guru.portal'));
        $this->assertDatabaseMissing('prestasi_non_akademiks', ['id' => $prestasi->id]);
    }

    public function test_wali_kelas_tidak_bisa_mencatat_prestasi_siswa_di_luar_binaannya(): void
    {
        $wali = $this->waliKelas();
        $kelasLuar = Kelas::create(['nama_kelas' => 'X Uji Prestasi', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswaLuar = Siswa::create([
            'nis' => '998877', 'nama' => 'Siswa Luar Prestasi', 'kelas_id' => $kelasLuar->id,
            'password' => bcrypt('password'), 'status_aktif' => true,
        ]);

        $this->sebagai($wali)->post(route('guru.wali-kelas.prestasi.store'), [
            'siswa_id' => $siswaLuar->id,
            'nama_prestasi' => 'Mencoba Prestasi Siswa Lain',
            'tingkat' => 'Sekolah',
            'tanggal' => now()->toDateString(),
        ])->assertSessionHasErrors('siswa_id');

        $this->assertDatabaseMissing('prestasi_non_akademiks', ['siswa_id' => $siswaLuar->id]);
    }

    public function test_prestasi_non_akademik_tampil_di_dashboard_orangtua(): void
    {
        $orangTua = OrangTua::with('siswa')->firstOrFail();

        PrestasiNonAkademik::create([
            'siswa_id' => $orangTua->siswa_id,
            'nama_prestasi' => 'Juara 1 Lomba Pidato',
            'tingkat' => 'Provinsi',
            'peringkat' => 'Juara 1',
            'tanggal' => now()->toDateString(),
            'penyelenggara' => 'Dinas Pendidikan Provinsi',
        ]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('Juara 1 Lomba Pidato')
            ->assertSee('Provinsi');
    }

    public function test_role_selain_wali_kelas_ditolak_mencatat_prestasi(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'wali_kelas'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('guru.wali-kelas.prestasi.store'), [
            'siswa_id' => Siswa::firstOrFail()->id,
            'nama_prestasi' => 'X',
            'tingkat' => 'Sekolah',
            'tanggal' => now()->toDateString(),
        ])->assertRedirect(route('login'));
    }
}
