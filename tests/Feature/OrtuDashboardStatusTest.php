<?php

namespace Tests\Feature;

use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\NilaiSas;
use App\Models\OrangTua;
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

        // Pastikan tabel Rincian Nilai Sumatif Lingkup Materi tetap ada
        $detailResponse->assertSee('Rincian Nilai Sumatif Lingkup Materi');
    }
}
