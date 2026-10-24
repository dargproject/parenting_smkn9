<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapAlpaGuruWaliTest extends TestCase
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
            'guru_id' => $guru->id, 'guru_nama' => $guru->nama, 'roles' => $roles, 'role' => $roles[0] ?? '',
        ]);
    }

    private function siapkanData(): array
    {
        $guruWali = Guru::create(['nama' => 'Guru Wali Alpa Uji', 'nip' => '9997101', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruWali->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);

        $kelas = Kelas::create(['nama_kelas' => 'X Alpa Uji', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guruWali->id]);
        $siswa = Siswa::create(['nis' => '997101', 'nama' => 'Siswa Alpa Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $mapel = MataPelajaran::firstOrFail();
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guruWali->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:00', 'ruang' => '-',
            'tahun_ajaran_id' => $taAktifId,
        ]);

        return compact('guruWali', 'kelas', 'siswa', 'mapel', 'jadwal');
    }

    public function test_menghitung_alpa_bulan_ini_per_mapel(): void
    {
        ['guruWali' => $guruWali, 'siswa' => $siswa, 'mapel' => $mapel, 'jadwal' => $jadwal] = $this->siapkanData();

        // 3x alpa bulan ini -> harus masuk hitungan & kena ambang merah (>=3).
        foreach ([now(), now()->subDays(2), now()->subDays(4)] as $tanggal) {
            Presensi::create([
                'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id,
                'tanggal' => $tanggal->toDateString(), 'status' => 'A',
            ]);
        }
        // Alpa bulan lalu, di luar rentang default -- tidak boleh ikut terhitung.
        Presensi::create([
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->subMonthsNoOverflow(2)->toDateString(), 'status' => 'A',
        ]);

        $mulai = now()->startOfMonth()->toDateString();
        $selesai = now()->toDateString();

        $response = $this->sebagai($guruWali)->getJson(route('guru.wali.rekap-alpa', ['mulai' => $mulai, 'selesai' => $selesai]));

        $response->assertOk();
        $response->assertJsonFragment(['nama' => $mapel->nama_mapel]);
        $data = $response->json();
        $this->assertSame($siswa->id, $data['siswa'][0]['id']);
        $this->assertSame(3, $data['siswa'][0]['total']);
        $this->assertSame(3, $data['siswa'][0]['perMapel'][$mapel->id]['alpa']);
        $this->assertCount(3, $data['siswa'][0]['perMapel'][$mapel->id]['detail']);
    }

    public function test_sakit_dan_izin_ikut_tercatat_dengan_keterangan_tapi_tidak_masuk_total_alpa(): void
    {
        ['guruWali' => $guruWali, 'siswa' => $siswa, 'mapel' => $mapel, 'jadwal' => $jadwal] = $this->siapkanData();

        Presensi::create([
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->toDateString(), 'status' => 'S', 'keterangan' => 'Demam tinggi',
        ]);
        Presensi::create([
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->subDay()->toDateString(), 'status' => 'I', 'keterangan' => 'Acara keluarga',
        ]);

        $response = $this->sebagai($guruWali)->getJson(route('guru.wali.rekap-alpa'));

        $response->assertOk();
        $data = $response->json();
        $sel = $data['siswa'][0]['perMapel'][$mapel->id];

        $this->assertSame(0, $sel['alpa']);
        $this->assertSame(1, $sel['sakit']);
        $this->assertSame(1, $sel['izin']);
        $this->assertSame(0, $data['siswa'][0]['total']);
        $this->assertCount(2, $sel['detail']);
        $this->assertTrue(collect($sel['detail'])->contains(fn ($d) => $d['status'] === 'S' && $d['keterangan'] === 'Demam tinggi'));
        $this->assertTrue(collect($sel['detail'])->contains(fn ($d) => $d['status'] === 'I' && $d['keterangan'] === 'Acara keluarga'));
    }

    public function test_siswa_di_luar_kelas_binaan_tidak_ikut(): void
    {
        ['guruWali' => $guruWali, 'jadwal' => $jadwal] = $this->siapkanData();

        $kelasLain = Kelas::create(['nama_kelas' => 'X Bukan Binaan Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswaLain = Siswa::create(['nis' => '997102', 'nama' => 'Siswa Bukan Binaan Uji', 'kelas_id' => $kelasLain->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        Presensi::create([
            'siswa_id' => $siswaLain->id, 'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->toDateString(), 'status' => 'A',
        ]);

        $response = $this->sebagai($guruWali)->getJson(route('guru.wali.rekap-alpa'));

        $response->assertOk();
        $this->assertEmpty(collect($response->json('siswa'))->firstWhere('id', $siswaLain->id));
    }

    public function test_role_lain_ditolak_akses_rekap_alpa(): void
    {
        $guruMapel = Guru::create(['nama' => 'Guru Mapel Bukan Wali Uji', 'nip' => '9997103', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruMapel->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $this->sebagai($guruMapel)->get(route('guru.wali.rekap-alpa'))->assertRedirect(route('login'));
    }

    public function test_portal_guru_wali_merender_pane_rekap_presensi(): void
    {
        ['guruWali' => $guruWali] = $this->siapkanData();

        $this->sebagai($guruWali)->get(route('guru.portal'))
            ->assertOk()
            ->assertSee('Rekap Presensi per Mapel');
    }
}
