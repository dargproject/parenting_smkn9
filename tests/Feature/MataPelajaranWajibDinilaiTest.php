<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\NilaiSas;
use App\Models\RaporFinal;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MataPelajaranWajibDinilaiTest extends TestCase
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

    public function test_mapel_baru_default_wajib_dinilai(): void
    {
        // Form create pre-mencentang checkbox ini (lihat MataPelajaran::$attributes), jadi
        // submit tanpa menyentuhnya berarti ikut terkirim tercentang seperti ini.
        $this->sebagai($this->admin())->post(route('admin.mata-pelajaran.store'), [
            'nama_mapel' => 'Mapel Default Uji',
            'kategori' => 'Nasional',
            'beban_jp' => 2,
            'wajib_dinilai' => '1',
        ])->assertRedirect(route('admin.mata-pelajaran.index'));

        $this->assertDatabaseHas('mata_pelajarans', ['nama_mapel' => 'Mapel Default Uji', 'wajib_dinilai' => 1]);
    }

    public function test_admin_bisa_menandai_mapel_tidak_wajib_dinilai(): void
    {
        $this->sebagai($this->admin())->post(route('admin.mata-pelajaran.store'), [
            'nama_mapel' => 'Bimbingan Konseling',
            'kategori' => 'Nasional',
            'beban_jp' => 1,
            // checkbox tidak dicentang -> tidak dikirim sama sekali
        ])->assertRedirect(route('admin.mata-pelajaran.index'));

        $this->assertDatabaseHas('mata_pelajarans', ['nama_mapel' => 'Bimbingan Konseling', 'wajib_dinilai' => 0]);
    }

    public function test_admin_bisa_melepas_wajib_dinilai_lewat_edit(): void
    {
        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Edit Uji', 'kategori' => 'Nasional', 'beban_jp' => 2]);
        $this->assertTrue($mapel->fresh()->wajib_dinilai);

        $this->sebagai($this->admin())->put(route('admin.mata-pelajaran.update', $mapel), [
            'nama_mapel' => 'Mapel Edit Uji',
            'kategori' => 'Nasional',
            'beban_jp' => 2,
            // checkbox dilepas
        ])->assertRedirect(route('admin.mata-pelajaran.index'));

        $this->assertFalse($mapel->fresh()->wajib_dinilai);
    }

    public function test_rilis_tidak_terblokir_oleh_mapel_yang_tidak_wajib_dinilai(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Rilis Uji', 'nip' => '9997601', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rilis Uji', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997601', 'nama' => 'Siswa Rilis Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $mapelAkademik = MataPelajaran::create(['nama_mapel' => 'Mapel Akademik Uji', 'kategori' => 'Nasional', 'beban_jp' => 2]);
        $mapelBk = MataPelajaran::create(['nama_mapel' => 'Bimbingan Konseling Uji', 'kategori' => 'Nasional', 'beban_jp' => 1, 'wajib_dinilai' => false]);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapelAkademik->id, 'guru_id' => $guru->id]);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapelBk->id, 'guru_id' => $guru->id]);

        // Mapel akademik sudah ada NilaiSas, BK sengaja tidak pernah diisi.
        NilaiSas::create(['siswa_id' => $siswa->id, 'mata_pelajaran_id' => $mapelAkademik->id, 'tahun_ajaran_id' => $taAktifId, 'nilai' => 85]);

        $this->sebagai($guru)->post(route('guru.wali.rapor.rilis'), [
            'siswa_ids' => [$siswa->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('rapor_finals', ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $taAktifId, 'status' => 'final']);
    }

    public function test_rilis_tetap_terblokir_jika_mapel_wajib_dinilai_belum_ada_nilai(): void
    {
        $guru = Guru::create(['nama' => 'Guru Wali Rilis Blokir Uji', 'nip' => '9997602', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_wali'])->id);
        $kelas = Kelas::create(['nama_kelas' => 'X Rilis Blokir Uji', 'tingkat' => 'X', 'jurusan' => 'Uji', 'guru_wali_id' => $guru->id]);
        $siswa = Siswa::create(['nis' => '997602', 'nama' => 'Siswa Rilis Blokir Uji', 'kelas_id' => $kelas->id, 'password' => bcrypt('password'), 'status_aktif' => true]);

        $mapelAkademik = MataPelajaran::create(['nama_mapel' => 'Mapel Akademik Blokir Uji', 'kategori' => 'Nasional', 'beban_jp' => 2]);
        KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapelAkademik->id, 'guru_id' => $guru->id]);
        // Belum ada NilaiSas sama sekali untuk mapel yang wajib_dinilai -- rilis harus tetap ditolak.

        $this->sebagai($guru)->post(route('guru.wali.rapor.rilis'), [
            'siswa_ids' => [$siswa->id],
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('rapor_finals', ['siswa_id' => $siswa->id]);
        $this->assertFalse((bool) RaporFinal::where('siswa_id', $siswa->id)->exists());
    }
}
