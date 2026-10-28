<?php

namespace Tests\Feature;

use App\Exports\NilaiLmTemplateExport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class NilaiLmImportTest extends TestCase
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
        $guru = Guru::create(['nama' => 'Guru Import Nilai Uji', 'nip' => '9997301', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);
        $mapel = MataPelajaran::firstOrFail();
        $kelas = Kelas::create(['nama_kelas' => 'X Import Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        $taAktifId = TahunAjaran::where('is_active', true)->value('id');
        $tp1 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N1', 'deskripsi' => 'TP 1 Uji', 'urutan' => 1]);
        $tp2 = TujuanPembelajaran::create(['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $taAktifId, 'kode' => 'N2', 'deskripsi' => 'TP 2 Uji', 'urutan' => 2]);

        $siswa1 = Siswa::create(['nis' => '997301', 'nama' => 'Siswa Import Satu', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $siswa2 = Siswa::create(['nis' => '997302', 'nama' => 'Siswa Import Dua', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        return compact('guru', 'mapel', 'kelas', 'tp1', 'tp2', 'siswa1', 'siswa2');
    }

    public function test_guru_pengampu_bisa_unduh_template_xlsx_berisi_kolom_n(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'siswa1' => $siswa1] = $this->siapkanAmpuan();

        Excel::fake();

        $this->sebagai($guru)->get(route('guru.penilaian.nilai-lm.template', ['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id]))
            ->assertOk();

        Excel::assertDownloaded(
            'template-nilai-'.Str::slug($mapel->nama_mapel).'-'.Str::slug($kelas->nama_kelas).'.xlsx',
            function (NilaiLmTemplateExport $export) use ($siswa1) {
                $this->assertSame(['NIS', 'Nama Siswa', 'N1', 'N2'], $export->headings());
                $this->assertTrue($export->collection()->contains(fn ($row) => $row[0] === $siswa1->nis && $row[1] === $siswa1->nama));

                return true;
            }
        );
    }

    public function test_guru_bukan_pengampu_ditolak_unduh_template(): void
    {
        ['mapel' => $mapel, 'kelas' => $kelas] = $this->siapkanAmpuan();
        $guruLain = Guru::create(['nama' => 'Guru Lain Import Uji', 'nip' => '9997303', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruLain->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $this->sebagai($guruLain)->get(route('guru.penilaian.nilai-lm.template', ['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelas->id]))
            ->assertStatus(403);
    }

    public function test_import_csv_berhasil_menyimpan_nilai_dan_rekalkulasi_na_sumatif(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'tp2' => $tp2, 'siswa1' => $siswa1, 'siswa2' => $siswa2] = $this->siapkanAmpuan();

        $csv = "NIS,Nama Siswa,N1,N2\n{$siswa1->nis},Siswa Import Satu,80,90\n{$siswa2->nis},Siswa Import Dua,60,70\n";
        $file = UploadedFile::fake()->createWithContent('nilai.csv', $csv);

        $response = $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.import'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'file' => $file,
        ]);

        $response->assertRedirect(route('guru.portal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswa1->id, 'tujuan_pembelajaran_id' => $tp1->id, 'nilai' => 80]);
        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswa1->id, 'tujuan_pembelajaran_id' => $tp2->id, 'nilai' => 90]);
        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswa2->id, 'tujuan_pembelajaran_id' => $tp1->id, 'nilai' => 60]);
        $this->assertDatabaseHas('nilai_sas', ['siswa_id' => $siswa1->id, 'mata_pelajaran_id' => $mapel->id, 'nilai' => 85]);
    }

    public function test_import_csv_dengan_nis_tidak_dikenal_dilaporkan_sebagai_error_tanpa_menghentikan_baris_lain(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelas' => $kelas, 'tp1' => $tp1, 'siswa1' => $siswa1] = $this->siapkanAmpuan();

        $csv = "NIS,Nama Siswa,N1,N2\n{$siswa1->nis},Siswa Import Satu,80,90\n999999,Siswa Tidak Ada,70,70\n";
        $file = UploadedFile::fake()->createWithContent('nilai.csv', $csv);

        $response = $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.import'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'file' => $file,
        ]);

        $response->assertRedirect(route('guru.portal'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswa1->id, 'tujuan_pembelajaran_id' => $tp1->id, 'nilai' => 80]);
        $this->assertDatabaseMissing('siswas', ['nis' => '999999']);
    }

    public function test_import_ditolak_jika_belum_ada_kolom_n(): void
    {
        $guru = Guru::create(['nama' => 'Guru Belum Ada N Uji', 'nip' => '9997304', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);
        $mapel = MataPelajaran::firstOrFail();
        $kelas = Kelas::create(['nama_kelas' => 'X Import Kosong Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        $file = UploadedFile::fake()->createWithContent('nilai.csv', "NIS,Nama Siswa\n");

        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.import'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'file' => $file,
        ])->assertStatus(422);
    }

    public function test_guru_bukan_pengampu_ditolak_import(): void
    {
        ['mapel' => $mapel, 'kelas' => $kelas] = $this->siapkanAmpuan();
        $guruLain = Guru::create(['nama' => 'Guru Lain Import Dua Uji', 'nip' => '9997305', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruLain->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $file = UploadedFile::fake()->createWithContent('nilai.csv', "NIS,Nama Siswa,N1,N2\n");

        $this->sebagai($guruLain)->post(route('guru.penilaian.nilai-lm.import'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'file' => $file,
        ])->assertStatus(403);
    }
}
