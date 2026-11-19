<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiSas;
use App\Models\OrangTua;
use App\Models\Presensi;
use App\Models\RaporFinal;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrtuDashboardStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_penilaian_service_status_mapel_logic(): void
    {
        $penilaian = app(PenilaianService::class);

        // Kasus 1: Semua LM tuntas, SAS tuntas
        $lmsPass = collect([(object) ['nilai' => 75], (object) ['nilai' => 88]]);
        $this->assertSame('tuntas', $penilaian->statusMapel($lmsPass, 75.0));

        // Kasus 2: Ada satu LM belum tuntas (< 75)
        $lmsWithFail = collect([(object) ['nilai' => 75], (object) ['nilai' => 74]]);
        $this->assertSame('remedial', $penilaian->statusMapel($lmsWithFail, 75.0));

        // Kasus 3: Semua LM tuntas tapi SAS belum tuntas (< 75)
        $this->assertSame('remedial', $penilaian->statusMapel($lmsPass, 70.0));

        // Kasus 4: Belum ada nilai sama sekali
        $this->assertSame('belum ada nilai', $penilaian->statusMapel(collect(), null));
    }

    public function test_dashboard_displays_correct_status_and_detail_page_removes_blue_boxes(): void
    {
        $ta = TahunAjaran::where('is_active', true)->first();
        $orangTua = OrangTua::with('siswa')->first();

        $siswa = $orangTua->siswa;
        RaporFinal::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ta->id],
            ['status' => 'final', 'tanggal_final' => now()]
        );

        $mapel = MataPelajaran::first();

        // 1. Uji halaman dashboard
        $response = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'));
        $response->assertStatus(200);

        // 2. Uji jika ada 1 nilai LM belum tuntas (< 75)
        $lm = NilaiLm::where('siswa_id', $siswa->id)->first();
        if ($lm) {
            $lm->update(['nilai' => 70]);
            $responseRemedial = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'));
            $responseRemedial->assertStatus(200);
            $responseRemedial->assertSee('Belum Tuntas');

            // Kembalikan LM ke 75, tapi ubah SAS menjadi < 75
            $lm->update(['nilai' => 75]);
            NilaiSas::updateOrCreate(
                ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'tahun_ajaran_id' => $ta->id],
                ['nilai' => 65]
            );
            $responseSasRemedial = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'));
            $responseSasRemedial->assertStatus(200);
            $responseSasRemedial->assertSee('Belum Tuntas');
        }

        // 3. Uji halaman detail mapel
        $detailResponse = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.mapel.show', $mapel));
        $detailResponse->assertStatus(200);

        // Pastikan footer SAS di tabel sudah dihilangkan
        $detailResponse->assertDontSee('Nilai Sumatif Akhir Semester (SAS)');

        // Pastikan nama mapel ditampilkan di halaman detail
        $detailResponse->assertSee($mapel->nama_mapel);
    }

    public function test_kehadiran_tampil_di_dashboard_ortu_tanpa_menunggu_rapor_dirilis(): void
    {
        $ta = TahunAjaran::where('is_active', true)->first();
        $orangTua = OrangTua::with('siswa.kelas')->first();
        $siswa = $orangTua->siswa;
        $mapel = MataPelajaran::first();
        $guru = Guru::create(['nama' => 'Guru Jadwal Kehadiran Uji', 'nip' => '9997241', 'password' => bcrypt('password'), 'is_active' => true]);

        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $siswa->kelas_id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:00', 'ruang' => '-',
            'tahun_ajaran_id' => $ta->id,
        ]);

        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(), 'status' => 'H', 'tahun_ajaran_id' => $ta->id]);
        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->subDay()->toDateString(), 'status' => 'A', 'tahun_ajaran_id' => $ta->id]);

        // Rapor belum dirilis -- kehadiran tetap harus tampil (sama seperti Catatan Kedisiplinan).
        $response = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'));

        $response->assertOk()
            ->assertSee('Kehadiran')
            ->assertSee('50%', false)
            ->assertSee('Alpa:', false)
            ->assertSee(now()->subDay()->translatedFormat('d M'))
            ->assertDontSee('Hari Campur', false);
    }

    public function test_kehadiran_hari_campur_menampilkan_nama_mapel_yang_alpa(): void
    {
        $ta = TahunAjaran::where('is_active', true)->first();
        $orangTua = OrangTua::with('siswa.kelas')->first();
        $siswa = $orangTua->siswa;
        $mapelHadir = MataPelajaran::first();
        $mapelAlpa = MataPelajaran::skip(1)->first();
        $guru = Guru::create(['nama' => 'Guru Jadwal Kehadiran Campur Uji', 'nip' => '9997242', 'password' => bcrypt('password'), 'is_active' => true]);

        $jadwalHadir = JadwalPelajaran::create([
            'kelas_id' => $siswa->kelas_id, 'mata_pelajaran_id' => $mapelHadir->id, 'guru_id' => $guru->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:00', 'ruang' => '-', 'tahun_ajaran_id' => $ta->id,
        ]);
        $jadwalAlpa = JadwalPelajaran::create([
            'kelas_id' => $siswa->kelas_id, 'mata_pelajaran_id' => $mapelAlpa->id, 'guru_id' => $guru->id,
            'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'ruang' => '-', 'tahun_ajaran_id' => $ta->id,
        ]);

        // Satu hari yang sama, tapi cuma salah satu mapel yang alpa -- bukan sehari penuh.
        $tanggal = now()->subDay()->toDateString();
        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwalHadir->id, 'tanggal' => $tanggal, 'status' => 'H', 'tahun_ajaran_id' => $ta->id]);
        Presensi::create(['siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwalAlpa->id, 'tanggal' => $tanggal, 'status' => 'A', 'tahun_ajaran_id' => $ta->id]);

        $response = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'));

        $response->assertOk()
            ->assertSee('Hari Campur', false)
            ->assertSee(now()->subDay()->translatedFormat('d M'))
            ->assertSee($mapelAlpa->nama_mapel);
    }

    public function test_semester_aktif_tanpa_rapor_tetap_bisa_dipilih_dan_tidak_hilang_dari_dropdown(): void
    {
        $ganjil = TahunAjaran::where('is_active', true)->first();
        $orangTua = OrangTua::with('siswa')->firstOrFail();
        $siswa = $orangTua->siswa;

        RaporFinal::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ganjil->id],
            ['status' => 'final', 'tanggal_final' => now()]
        );

        $genap = TahunAjaran::create(['kode' => '2026/2027-genap-uji', 'nama' => '2026/2027 Genap (Uji)', 'semester' => 'genap', 'is_active' => false]);

        // Genap belum punya rapor dirilis, tapi harus tetap bisa diakses langsung via ?ta=, bukan diam-diam dialihkan ke Ganjil.
        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard', ['ta' => $genap->id]))
            ->assertOk()
            ->assertSee('Rapor semester ini belum dirilis')
            ->assertSee($ganjil->nama);

        // Genap jadi semester aktif di sistem; buka dashboard tanpa ?ta= harus default ke Genap dan tetap menampilkan Ganjil di dropdown.
        TahunAjaran::query()->update(['is_active' => false]);
        $genap->update(['is_active' => true]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('(berjalan)')
            ->assertSee($ganjil->nama);

        // Pindah ke Ganjil (dari riwayat) -- Genap (semester aktif sistem) harus tetap ada di dropdown, tidak hilang.
        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard', ['ta' => $ganjil->id]))
            ->assertOk()
            ->assertSee($genap->nama);
    }
}
