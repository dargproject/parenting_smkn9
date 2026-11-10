<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PenempatanPkl;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PenempatanPklTest extends TestCase
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

    private function admin(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    private function pembimbingPkl(string $nama = 'Pembimbing PKL Uji', string $nip = '9999201'): Guru
    {
        $guru = Guru::create(['nama' => $nama, 'nip' => $nip, 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'pembimbing_pkl'])->id);

        return $guru;
    }

    public function test_admin_dapat_mengelola_penempatan_pkl(): void
    {
        $admin = $this->admin();
        $pembimbing = $this->pembimbingPkl();
        $siswa = Siswa::firstOrFail();
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->firstOrFail();

        $this->sebagai($admin)->post(route('admin.penempatan-pkl.store'), [
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $tahunAjaranAktif->id,
            'nama_iduka' => 'PT Maju Jaya',
            'alamat' => 'Jl. Industri No. 1, Malang',
            'nama_cp_industri' => 'Budi',
            'no_cp_industri' => '08123456789',
            'guru_pembimbing_id' => $pembimbing->id,
        ])->assertRedirect(route('admin.penempatan-pkl.index'));

        $penempatan = PenempatanPkl::where('siswa_id', $siswa->id)->firstOrFail();
        $this->assertSame('PT Maju Jaya', $penempatan->nama_iduka);
        $this->assertSame($pembimbing->id, $penempatan->guru_pembimbing_id);

        $this->sebagai($admin)->put(route('admin.penempatan-pkl.update', $penempatan), [
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $tahunAjaranAktif->id,
            'nama_iduka' => 'PT Maju Jaya Sentosa',
            'guru_pembimbing_id' => $pembimbing->id,
        ])->assertRedirect(route('admin.penempatan-pkl.index'));

        $this->assertSame('PT Maju Jaya Sentosa', $penempatan->refresh()->nama_iduka);

        $this->sebagai($admin)->delete(route('admin.penempatan-pkl.destroy', $penempatan))->assertRedirect();
        $this->assertDatabaseMissing('penempatan_pkls', ['id' => $penempatan->id]);
    }

    public function test_hanya_guru_dengan_role_pembimbing_pkl_yang_muncul_di_pilihan_pembimbing(): void
    {
        $admin = $this->admin();
        $this->pembimbingPkl('Calon Pembimbing', '9999202');
        $guruLain = Guru::create(['nama' => 'Guru Mapel Bukan Pembimbing', 'nip' => '9999203', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruLain->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $this->sebagai($admin)->get(route('admin.penempatan-pkl.create'))
            ->assertOk()
            ->assertSee('Calon Pembimbing')
            ->assertDontSee('Guru Mapel Bukan Pembimbing');
    }

    public function test_role_selain_admin_ditolak_mengakses_penempatan_pkl(): void
    {
        $pembimbing = $this->pembimbingPkl();

        $this->sebagai($pembimbing)->get(route('admin.penempatan-pkl.index'))->assertRedirect(route('login'));
    }

    public function test_admin_dapat_unduh_template_xlsx_penempatan_pkl(): void
    {
        Excel::fake();

        $this->sebagai($this->admin())->get(route('admin.penempatan-pkl.template'))->assertOk();

        Excel::assertDownloaded('template-penempatan-pkl.xlsx', function ($export) {
            return $export->headings()[0] === 'Nama Siswa';
        });
    }

    public function test_admin_dapat_export_data_penempatan_pkl(): void
    {
        Excel::fake();

        $this->sebagai($this->admin())->get(route('admin.penempatan-pkl.export'))->assertOk();

        Excel::assertDownloaded('data-penempatan-pkl-'.now()->format('Ymd-His').'.xlsx', function ($export) {
            return $export->headings()[0] === 'Nama Siswa';
        });
    }

    public function test_import_xlsx_menambah_penempatan_pkl_baru(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'XI Import PKL Uji', 'tingkat' => 'XI', 'jurusan' => 'Uji']);
        $siswa = Siswa::create(['nis' => '998901', 'nama' => 'Siswa Import PKL Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $pembimbing = $this->pembimbingPkl('Pembimbing Import Uji', '9999204');

        $csv = "nama_siswa,kelas,nama_iduka,alamat,nama_cp_industri,cp_industri,pembimbing_sekolah\n".
            "{$siswa->nama},{$kelas->nama_kelas},Alfa Percetakan,\"Bonangan, Pakis\",Moh. Fadillah,08213821501,{$pembimbing->nama}\n";
        $file = UploadedFile::fake()->createWithContent('penempatan-pkl.csv', $csv);

        $response = $this->sebagai($this->admin())->post(route('admin.penempatan-pkl.import'), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penempatan_pkls', [
            'siswa_id' => $siswa->id,
            'nama_iduka' => 'Alfa Percetakan',
            'guru_pembimbing_id' => $pembimbing->id,
        ]);
    }

    public function test_import_gagal_menampilkan_semua_baris_gagal_tanpa_dipotong(): void
    {
        $csv = "nama_siswa,kelas,nama_iduka,alamat,nama_cp_industri,cp_industri,pembimbing_sekolah\n";
        $namaGagal = [];
        for ($i = 1; $i <= 7; $i++) {
            $namaGagal[] = "Siswa Tidak Ada Uji {$i}";
            $csv .= "Siswa Tidak Ada Uji {$i},XII Tidak Ada Uji,IDUKA Uji,,,,\n";
        }
        $file = UploadedFile::fake()->createWithContent('penempatan-pkl.csv', $csv);

        $response = $this->sebagai($this->admin())->post(route('admin.penempatan-pkl.import'), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
        $errors = session('import_errors');
        $this->assertCount(7, $errors);
        foreach ($namaGagal as $nama) {
            $this->assertStringContainsString($nama, implode(' ', $errors));
        }
    }

    public function test_import_ulang_siswa_yang_sama_memperbarui_bukan_menduplikasi(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'XI Import PKL Uji B', 'tingkat' => 'XI', 'jurusan' => 'Uji']);
        $siswa = Siswa::create(['nis' => '998902', 'nama' => 'Siswa Import PKL Dua Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->firstOrFail();
        PenempatanPkl::create(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranAktif->id, 'nama_iduka' => 'IDUKA Lama']);

        $csv = "nama_siswa,kelas,nama_iduka,alamat,nama_cp_industri,cp_industri,pembimbing_sekolah\n".
            "{$siswa->nama},{$kelas->nama_kelas},IDUKA Baru,,,,\n";
        $file = UploadedFile::fake()->createWithContent('penempatan-pkl.csv', $csv);

        $this->sebagai($this->admin())->post(route('admin.penempatan-pkl.import'), ['file' => $file]);

        $this->assertSame(1, PenempatanPkl::where('siswa_id', $siswa->id)->count());
        $this->assertDatabaseHas('penempatan_pkls', ['siswa_id' => $siswa->id, 'nama_iduka' => 'IDUKA Baru']);
    }
}
