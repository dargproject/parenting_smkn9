<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapPresensiTest extends TestCase
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

    public function test_tanpa_kelas_dipilih_menampilkan_pesan_arahan(): void
    {
        $guruBk = $this->guruBk();

        $this->sebagai($guruBk)->get(route('guru.bk.rekap-presensi.index'))
            ->assertOk()
            ->assertSee('Pilih kelas terlebih dahulu');
    }

    public function test_guru_bk_dapat_melihat_rekap_presensi_kelas_tertentu(): void
    {
        $guruBk = $this->guruBk();
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][now()->dayOfWeek];

        $kelas = Kelas::create(['nama_kelas' => 'X Uji Rekap Presensi', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '997701', 'nama' => 'Siswa Rekap Uji', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true,
        ]);
        $mapel = MataPelajaran::firstOrFail();
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))->firstOrFail();

        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guruMapel->id,
            'hari' => $namaHari, 'jam_mulai' => '07:00', 'jam_selesai' => '08:00', 'ruang' => '-',
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        Presensi::create([
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(),
            'status' => 'A', 'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        $response = $this->sebagai($guruBk)->get(route('guru.bk.rekap-presensi.index', [
            'kelas_id' => $kelas->id, 'tanggal' => now()->toDateString(),
        ]))->assertOk();

        $response->assertSee('X Uji Rekap Presensi');
        $response->assertSee('Siswa Rekap Uji');
        $response->assertSee($mapel->nama_mapel);
    }

    public function test_role_lain_ditolak_mengakses_rekap_presensi(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.rekap-presensi.index'))->assertRedirect(route('login'));
    }
}
