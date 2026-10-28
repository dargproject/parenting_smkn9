<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Services\PenilaianService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiLmRemedialPengayaanTest extends TestCase
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

    private function siapkanAmpuan(): array
    {
        $guru = Guru::create(['nama' => 'Guru Remedial Uji', 'nip' => '9997401', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);
        $mapel = MataPelajaran::firstOrFail();
        $kelas = Kelas::create(['nama_kelas' => 'X Remedial Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $tp1 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N1', 'deskripsi' => 'TP 1 Uji', 'urutan' => 1]);
        $tp2 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N2', 'deskripsi' => 'TP 2 Uji', 'urutan' => 2]);

        $siswa = Siswa::create(['nis' => '997401', 'nama' => 'Siswa Remedial Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        return compact('guru', 'mapel', 'kelas', 'tp1', 'tp2', 'siswa');
    }

    public function test_nilai_remedial_di_bawah_kktp_mengubah_rata_rata_dan_na_sumatif(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'tp2' => $tp2, 'siswa' => $siswa] = $this->siapkanAmpuan();
        $kktp = app(PenilaianService::class)->kktpThreshold();

        // Nilai awal: N1 = kktp-20 (belum tuntas), N2 = 90 (tuntas) -> rata awal = (kktp-20+90)/2
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => $kktp - 20, $tp2->id => 90]],
        ])->assertRedirect(route('guru.portal'));

        $rataAwal = round((($kktp - 20) + 90) / 2);
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataAwal]);

        // Siswa diremedial, nilai_remedial N1 naik jadi 85 -> nilai_efektif N1 jadi 85, bukan lagi kktp-20
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => $kktp - 20, $tp2->id => 90]],
            'remedial' => [$siswa->id => [$tp1->id => 85]],
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('nilai_lms', [
            'siswa_id' => $siswa->id,
            'tujuan_pembelajaran_id' => $tp1->id,
            'nilai' => $kktp - 20,
            'nilai_remedial' => 85,
        ]);

        $rataSetelahRemedial = round((85 + 90) / 2);
        $this->assertNotEquals($rataAwal, $rataSetelahRemedial, 'Nilai remedial harus mengubah rata-rata dibanding nilai awal.');
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataSetelahRemedial]);
        $this->assertDatabaseMissing('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataAwal]);
    }

    public function test_nilai_pengayaan_pada_tp_yang_sudah_tuntas_mengubah_rata_rata_dan_na_sumatif(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'tp2' => $tp2, 'siswa' => $siswa] = $this->siapkanAmpuan();

        // Nilai awal: N1 = 90 (tuntas), N2 = 95 (tuntas) -> rata awal = 92.5 -> round = 93 (round half away from zero di PHP)
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => 90, $tp2->id => 95]],
        ])->assertRedirect(route('guru.portal'));

        $rataAwal = round((90 + 95) / 2);
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataAwal]);

        // Siswa ditandai pengayaan untuk N1 dengan nilai pengayaan 98 -> nilai_efektif N1 jadi 98, bukan lagi 90
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => 90, $tp2->id => 95]],
            'pengayaan' => [$siswa->id => [$tp1->id => '1']],
            'nilai_pengayaan' => [$siswa->id => [$tp1->id => 98]],
            'catatan_pengayaan' => [$siswa->id => [$tp1->id => 'Mengerjakan soal pengayaan tambahan']],
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('nilai_lms', [
            'siswa_id' => $siswa->id,
            'tujuan_pembelajaran_id' => $tp1->id,
            'nilai' => 90,
            'nilai_remedial' => null,
            'sudah_pengayaan' => 1,
            'nilai_pengayaan' => 98,
        ]);

        $rataSetelahPengayaan = round((98 + 95) / 2);
        $this->assertNotEquals($rataAwal, $rataSetelahPengayaan, 'Nilai pengayaan harus mengubah rata-rata dibanding nilai awal.');
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataSetelahPengayaan]);
        $this->assertDatabaseMissing('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataAwal]);
    }

    public function test_pengayaan_dicentang_tanpa_nilai_hanya_catatan_tidak_mengubah_rata_rata(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'tp2' => $tp2, 'siswa' => $siswa] = $this->siapkanAmpuan();

        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => 90, $tp2->id => 95]],
        ])->assertRedirect(route('guru.portal'));

        $rataAwal = round((90 + 95) / 2);

        // Dicentang pengayaan tapi kolom nilai pengayaan dikosongkan -- cuma catatan, tanpa nilai baru
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'nilai' => [$siswa->id => [$tp1->id => 90, $tp2->id => 95]],
            'pengayaan' => [$siswa->id => [$tp1->id => '1']],
            'catatan_pengayaan' => [$siswa->id => [$tp1->id => 'Mengerjakan soal pengayaan tambahan']],
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('nilai_lms', [
            'siswa_id' => $siswa->id,
            'tujuan_pembelajaran_id' => $tp1->id,
            'nilai' => 90,
            'nilai_remedial' => null,
            'sudah_pengayaan' => 1,
            'nilai_pengayaan' => null,
        ]);

        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => $rataAwal]);
    }
}
