<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasMapelMultiKelasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function sebagaiWakaKurikulum(): Guru
    {
        $guru = Guru::create(['nama' => 'Waka Kurikulum Uji', 'nip' => '9996901', 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'waka_kurikulum'])->id);

        return $guru;
    }

    private function sebagai(Guru $guru): static
    {
        $roles = $guru->roles->pluck('name')->all();

        return $this->actingAs($guru)->withSession([
            'guru_id' => $guru->id, 'guru_nama' => $guru->nama, 'roles' => $roles, 'role' => $roles[0] ?? '',
        ]);
    }

    public function test_tetapkan_mapel_ke_beberapa_kelas_sekaligus(): void
    {
        $waka = $this->sebagaiWakaKurikulum();
        $guruPengampu = Guru::create(['nama' => 'Guru Pengampu Uji', 'nip' => '9996902', 'password' => bcrypt('password'), 'is_active' => true]);
        $kelasA = Kelas::create(['nama_kelas' => 'X Uji A', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $kelasB = Kelas::create(['nama_kelas' => 'X Uji B', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $mapel1 = MataPelajaran::create(['nama_mapel' => 'Mapel Uji 1', 'kategori' => 'Umum', 'kode_mapel' => 'MU1-'.uniqid(), 'beban_jp' => 2]);
        $mapel2 = MataPelajaran::create(['nama_mapel' => 'Mapel Uji 2', 'kategori' => 'Umum', 'kode_mapel' => 'MU2-'.uniqid(), 'beban_jp' => 2]);

        $this->sebagai($waka)->post(route('guru.kurikulum.kelas-mapel.store'), [
            'kelas_id' => [$kelasA->id, $kelasB->id],
            'mata_pelajaran_id' => [$mapel1->id, $mapel2->id],
            'guru_id' => $guruPengampu->id,
        ])->assertRedirect(route('guru.portal'));

        $this->assertSame(4, KelasMataPelajaran::where('guru_id', $guruPengampu->id)->count());
        foreach ([$kelasA, $kelasB] as $kelas) {
            foreach ([$mapel1, $mapel2] as $mapel) {
                $this->assertDatabaseHas('kelas_mata_pelajaran', [
                    'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guruPengampu->id,
                ]);
            }
        }

        // Guru pengampu otomatis dapat role guru_mapel.
        $this->assertTrue($guruPengampu->fresh()->roles->contains('name', 'guru_mapel'));
    }

    public function test_tetapkan_mapel_ke_satu_kelas_tetap_berfungsi(): void
    {
        $waka = $this->sebagaiWakaKurikulum();
        $guruPengampu = Guru::create(['nama' => 'Guru Pengampu Tunggal Uji', 'nip' => '9996903', 'password' => bcrypt('password'), 'is_active' => true]);
        $kelas = Kelas::create(['nama_kelas' => 'X Uji Tunggal', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Uji Tunggal', 'kategori' => 'Umum', 'kode_mapel' => 'MUT-'.uniqid(), 'beban_jp' => 2]);

        $this->sebagai($waka)->post(route('guru.kurikulum.kelas-mapel.store'), [
            'kelas_id' => [$kelas->id],
            'mata_pelajaran_id' => [$mapel->id],
            'guru_id' => $guruPengampu->id,
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('kelas_mata_pelajaran', [
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guruPengampu->id,
        ]);
    }

    public function test_tanpa_kelas_dipilih_ditolak_validasi(): void
    {
        $waka = $this->sebagaiWakaKurikulum();
        $guruPengampu = Guru::create(['nama' => 'Guru Tanpa Kelas Uji', 'nip' => '9996904', 'password' => bcrypt('password'), 'is_active' => true]);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Tanpa Kelas Uji', 'kategori' => 'Umum', 'kode_mapel' => 'MTK-'.uniqid(), 'beban_jp' => 2]);

        $this->sebagai($waka)->post(route('guru.kurikulum.kelas-mapel.store'), [
            'kelas_id' => [],
            'mata_pelajaran_id' => [$mapel->id],
            'guru_id' => $guruPengampu->id,
        ])->assertSessionHasErrors('kelas_id');
    }
}
