<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringJurnalKepsekTest extends TestCase
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

    private function kepsek(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'kepsek'))->firstOrFail();
    }

    public function test_monitoring_jurnal_menampilkan_status_sesuai_jam_dan_pengisian(): void
    {
        $kepsek = $this->kepsek();
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][now()->dayOfWeek];
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $kelas = Kelas::create(['nama_kelas' => 'X Uji Monitoring', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $mapel = MataPelajaran::firstOrFail();
        $guru = Guru::create(['nama' => 'Guru Uji Monitoring', 'nip' => '9997701', 'password' => bcrypt('password'), 'is_active' => true]);

        $jadwalSudahIsi = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $namaHari, 'jam_mulai' => '00:00', 'jam_selesai' => '00:01', 'ruang' => '-',
            'tahun_ajaran_id' => $taAktifId,
        ]);
        JurnalMengajar::create([
            'jadwal_pelajaran_id' => $jadwalSudahIsi->id, 'tanggal' => now()->toDateString(),
            'materi' => 'Materi uji', 'guru_id' => $guru->id,
        ]);

        $jadwalTerlambat = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $namaHari, 'jam_mulai' => '00:02', 'jam_selesai' => '00:03', 'ruang' => '-',
            'tahun_ajaran_id' => $taAktifId,
        ]);

        $jadwalBelumWaktunya = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $namaHari, 'jam_mulai' => '23:58', 'jam_selesai' => '23:59', 'ruang' => '-',
            'tahun_ajaran_id' => $taAktifId,
        ]);

        $response = $this->sebagai($kepsek)->get(route('kepsek.dashboard'))->assertOk();
        $response->assertSee('Monitoring Jurnal Guru Hari Ini');
        $response->assertSee('Sudah Isi');
        $response->assertSee('Terlambat');
        $response->assertSee('Belum Waktunya');
        $response->assertSee('Guru Uji Monitoring');
    }

    public function test_monitoring_jurnal_kosong_jika_tidak_ada_jadwal_hari_ini(): void
    {
        $kepsek = $this->kepsek();

        JadwalPelajaran::query()->delete();

        $this->sebagai($kepsek)->get(route('kepsek.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada jadwal pelajaran hari ini.');
    }
}
