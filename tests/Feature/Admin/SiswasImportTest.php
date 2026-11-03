<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SiswasImportTest extends TestCase
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

    private function admin(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    public function test_import_menambah_siswa_baru_lengkap_dengan_no_hp_ortu(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Import Siswa Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);

        $csv = "nis,nisn,nipd,nama,kelas,jenis_kelamin,tanggal_lahir,no_hp_ortu\n998801,,,Siswa Import Baru Uji,{$kelas->nama_kelas},L,2009-05-14,081234567890\n";
        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $response = $this->sebagai($this->admin())->post(route('admin.import.siswa'), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('siswas', ['nis' => '998801', 'nama' => 'Siswa Import Baru Uji', 'no_hp_ortu' => '081234567890']);
    }

    public function test_import_ulang_dengan_nis_sama_memperbarui_no_hp_ortu_yang_tadinya_kosong(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Import Siswa Uji B', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '998802', 'nama' => 'Siswa Import Lama Uji', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true, 'no_hp_ortu' => null,
        ]);

        $csv = "nis,nisn,nipd,nama,kelas,jenis_kelamin,tanggal_lahir,no_hp_ortu\n998802,,,Siswa Import Lama Uji,{$kelas->nama_kelas},L,2009-05-14,089988776655\n";
        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $response = $this->sebagai($this->admin())->post(route('admin.import.siswa'), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('siswas', ['id' => $siswa->id, 'no_hp_ortu' => '089988776655']);
        $this->assertSame(1, Siswa::where('nis', '998802')->count(), 'Harus memperbarui baris yang sama, bukan membuat duplikat.');
    }

    public function test_import_ulang_dengan_kolom_kosong_tidak_menghapus_data_lama(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Import Siswa Uji C', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '998803', 'nama' => 'Siswa Import Parsial Uji', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true, 'no_hp_ortu' => '081200000000',
        ]);

        // File baru cuma berisi NIS & nama, kolom no_hp_ortu dikosongkan -- nilai lama harus tetap.
        $csv = "nis,nisn,nipd,nama,kelas,jenis_kelamin,tanggal_lahir,no_hp_ortu\n998803,,,Siswa Import Parsial Uji,{$kelas->nama_kelas},,,\n";
        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $this->sebagai($this->admin())->post(route('admin.import.siswa'), ['file' => $file]);

        $this->assertDatabaseHas('siswas', ['id' => $siswa->id, 'no_hp_ortu' => '081200000000']);
    }

    public function test_import_dengan_nama_kelas_tidak_cocok_dilewati_tanpa_mengubah_data_lama(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Import Siswa Uji D', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '998804', 'nama' => 'Siswa Import Kelas Salah Uji', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true, 'no_hp_ortu' => '081200000001',
        ]);

        $csv = "nis,nisn,nipd,nama,kelas,jenis_kelamin,tanggal_lahir,no_hp_ortu\n998804,,,Siswa Import Kelas Salah Uji,Kelas Tidak Ada,,,089900000000\n";
        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $this->sebagai($this->admin())->post(route('admin.import.siswa'), ['file' => $file]);

        $this->assertDatabaseHas('siswas', ['id' => $siswa->id, 'kelas_id' => $kelas->id, 'no_hp_ortu' => '081200000001']);
    }
}
