<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\NilaiLm;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsiKosongNilaiLmSebagaiNolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function siapkanData(): array
    {
        $guru = Guru::create(['nama' => 'Guru Backfill Uji', 'nip' => '9997501', 'password' => bcrypt('password'), 'is_active' => true]);
        $mapel = MataPelajaran::firstOrFail();
        $kelas = Kelas::create(['nama_kelas' => 'X Backfill Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $tp1 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N1', 'deskripsi' => 'TP 1 Backfill Uji', 'urutan' => 1]);
        $tp2 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N2', 'deskripsi' => 'TP 2 Backfill Uji', 'urutan' => 2]);

        $siswaA = Siswa::create(['nis' => '997501', 'nama' => 'Siswa Backfill A', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $siswaB = Siswa::create(['nis' => '997502', 'nama' => 'Siswa Backfill B', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        // Data lama (sebelum aturan otomatis ada): N1 cuma punya nilai untuk siswa A,
        // siswa B belum pernah tersimpan sama sekali. N2 belum digarap sama sekali.
        NilaiLm::create(['siswa_id' => $siswaA->id, 'tujuan_pembelajaran_id' => $tp1->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 70]);

        return compact('guru', 'mapel', 'kelas', 'tp1', 'tp2', 'siswaA', 'siswaB');
    }

    public function test_dry_run_tidak_menyimpan_perubahan(): void
    {
        ['tp1' => $tp1, 'siswaB' => $siswaB] = $this->siapkanData();

        $this->artisan('nilai:isi-kosong-nol', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseMissing('nilai_lms', ['siswa_id' => $siswaB->id, 'tujuan_pembelajaran_id' => $tp1->id]);
    }

    public function test_backfill_mengisi_nol_untuk_n_yang_sudah_mulai_tapi_membiarkan_n_yang_kosong_semua(): void
    {
        ['mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'tp2' => $tp2, 'siswaA' => $siswaA, 'siswaB' => $siswaB] = $this->siapkanData();
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $this->artisan('nilai:isi-kosong-nol')
            ->expectsConfirmation('Lanjutkan menyimpan nilai 0 ini?', 'yes')
            ->assertSuccessful();

        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswaA->id, 'tujuan_pembelajaran_id' => $tp1->id, 'nilai' => 70]);
        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswaB->id, 'tujuan_pembelajaran_id' => $tp1->id, 'nilai' => 0]);
        $this->assertDatabaseMissing('nilai_lms', ['siswa_id' => $siswaA->id, 'tujuan_pembelajaran_id' => $tp2->id]);
        $this->assertDatabaseMissing('nilai_lms', ['siswa_id' => $siswaB->id, 'tujuan_pembelajaran_id' => $tp2->id]);

        // NA Sumatif siswa B ikut terbentuk dari nilai 0 yang baru dibuat.
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswaB->id, 'mata_pelajaran_id' => $mapel->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 0]);
    }

    public function test_menolak_konfirmasi_tidak_menyimpan_perubahan(): void
    {
        ['tp1' => $tp1, 'siswaB' => $siswaB] = $this->siapkanData();

        $this->artisan('nilai:isi-kosong-nol')
            ->expectsConfirmation('Lanjutkan menyimpan nilai 0 ini?', 'no')
            ->assertSuccessful();

        $this->assertDatabaseMissing('nilai_lms', ['siswa_id' => $siswaB->id, 'tujuan_pembelajaran_id' => $tp1->id]);
    }
}
