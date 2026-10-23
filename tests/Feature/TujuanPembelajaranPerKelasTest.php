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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TujuanPembelajaranPerKelasTest extends TestCase
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

    private function guruMengampuDuaKelas(): array
    {
        $guru = Guru::create(['nama' => 'Guru Dua Kelas Uji', 'nip' => '9997001', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        $mapel = MataPelajaran::firstOrFail();
        $kelasA = Kelas::create(['nama_kelas' => 'X TP Uji A', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $kelasB = Kelas::create(['nama_kelas' => 'X TP Uji B', 'tingkat' => 'X', 'jurusan' => 'Uji']);

        KelasMataPelajaran::create(['kelas_id' => $kelasA->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);
        KelasMataPelajaran::create(['kelas_id' => $kelasB->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);

        return compact('guru', 'mapel', 'kelasA', 'kelasB');
    }

    public function test_tambah_n_di_satu_kelas_tidak_muncul_di_kelas_lain(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelasA' => $kelasA, 'kelasB' => $kelasB] = $this->guruMengampuDuaKelas();

        $this->sebagai($guru)->post(route('guru.penilaian.tp.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelasA->id,
            'kode' => 'N1',
            'deskripsi' => 'Materi khusus kelas A',
        ])->assertRedirect(route('guru.portal'));

        $this->assertSame(1, TujuanPembelajaran::where('kelas_id', $kelasA->id)->count());
        $this->assertSame(0, TujuanPembelajaran::where('kelas_id', $kelasB->id)->count());
    }

    public function test_dua_kelas_boleh_punya_kode_dan_deskripsi_n_yang_sama(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelasA' => $kelasA, 'kelasB' => $kelasB] = $this->guruMengampuDuaKelas();

        $this->sebagai($guru)->post(route('guru.penilaian.tp.store'), [
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelasA->id, 'kode' => 'N1', 'deskripsi' => 'Materi sama persis',
        ])->assertRedirect(route('guru.portal'));

        $this->sebagai($guru)->post(route('guru.penilaian.tp.store'), [
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelasB->id, 'kode' => 'N1', 'deskripsi' => 'Materi sama persis',
        ])->assertRedirect(route('guru.portal'))->assertSessionDoesntHaveErrors();

        $this->assertSame(1, TujuanPembelajaran::where('kelas_id', $kelasA->id)->count());
        $this->assertSame(1, TujuanPembelajaran::where('kelas_id', $kelasB->id)->count());
    }

    public function test_guru_tidak_bisa_tambah_n_untuk_kelas_yang_bukan_ampuannya(): void
    {
        $guru = Guru::create(['nama' => 'Guru Satu Kelas Uji', 'nip' => '9997002', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);
        $mapel = MataPelajaran::firstOrFail();
        $kelasAmpuan = Kelas::create(['nama_kelas' => 'X TP Ampuan Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $kelasLain = Kelas::create(['nama_kelas' => 'X TP Bukan Ampuan Uji', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        KelasMataPelajaran::create(['kelas_id' => $kelasAmpuan->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);
        // Mapel yang sama diajar guru LAIN di kelasLain -- guru ini tidak mengampu mapel itu di kelasLain.

        $this->sebagai($guru)->post(route('guru.penilaian.tp.store'), [
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelasLain->id, 'kode' => 'N1', 'deskripsi' => 'Coba akses kelas lain',
        ])->assertStatus(403);

        $this->assertSame(0, TujuanPembelajaran::where('kelas_id', $kelasLain->id)->count());
        $this->assertSame(0, TujuanPembelajaran::where('kelas_id', $kelasAmpuan->id)->count());
    }

    public function test_nilai_lm_tersimpan_terpisah_per_kelas_untuk_mapel_yang_sama(): void
    {
        ['guru' => $guru, 'mapel' => $mapel, 'kelasA' => $kelasA, 'kelasB' => $kelasB] = $this->guruMengampuDuaKelas();

        $tpA = TujuanPembelajaran::create([
            'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kelasA->id,
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'kode' => 'N1', 'deskripsi' => 'TP Kelas A', 'urutan' => 1,
        ]);
        $siswaA = Siswa::create(['nis' => '997001', 'nama' => 'Siswa Kelas A Uji', 'kelas_id' => $kelasA->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelasA->id,
            'nilai' => [$siswaA->id => [$tpA->id => 88]],
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('nilai_lms', ['siswa_id' => $siswaA->id, 'tujuan_pembelajaran_id' => $tpA->id, 'nilai' => 88]);

        // TP milik kelas B belum ada -- mengirim ID milik kelas A ke kelas B harus ditolak.
        $siswaB = Siswa::create(['nis' => '997002', 'nama' => 'Siswa Kelas B Uji', 'kelas_id' => $kelasB->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $this->sebagai($guru)->post(route('guru.penilaian.nilai-lm.store'), [
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelasB->id,
            'nilai' => [$siswaB->id => [$tpA->id => 70]],
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseMissing('nilai_lms', ['siswa_id' => $siswaB->id]);
    }
}
