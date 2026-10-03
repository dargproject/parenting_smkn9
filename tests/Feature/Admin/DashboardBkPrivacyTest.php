<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardBkPrivacyTest extends TestCase
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

    public function test_dashboard_admin_menampilkan_statistik_bk_tanpa_detail_isi(): void
    {
        $admin = Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();

        KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Judul Kasus Rahasia',
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian rahasia.',
            'status' => 'antrean',
            'prioritas' => 'tinggi',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $response = $this->sebagai($admin)->get(route('admin.dashboard'))->assertOk();
        $response->assertDontSee('Judul Kasus Rahasia');
        $response->assertSee('Statistik Bimbingan Konseling');
        $response->assertSee('Prioritas Tinggi');
    }

    public function test_dashboard_kepsek_menampilkan_rekap_prioritas_kasus_bk(): void
    {
        $kepsek = Guru::whereHas('roles', fn ($q) => $q->where('name', 'kepsek'))->first();

        if (! $kepsek) {
            $this->markTestSkipped('Tidak ada akun kepsek pada data seed.');
        }

        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();

        KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => 'Judul Kasus Rahasia Kepsek',
            'kategori' => 'Normal',
            'deskripsi' => 'Uraian rahasia.',
            'status' => 'proses',
            'prioritas' => 'tinggi',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $response = $this->sebagai($kepsek)->get(route('kepsek.dashboard'))->assertOk();
        $response->assertDontSee('Judul Kasus Rahasia Kepsek');
        $response->assertSee('Kasus Aktif per Prioritas');
    }
}
