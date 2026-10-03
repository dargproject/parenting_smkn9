<?php

namespace Tests\Feature;

use App\Models\Guru;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi: sidebar kepsek sebelumnya memakai gotoPane(paneId, this) tanpa target URL,
 * yang defaultnya selalu mengarah ke /guru/portal -- halaman yang TIDAK bisa diakses role
 * kepsek (role:waka_kurikulum,waka_kesiswaan,guru_bk,wali_kelas,guru_wali,guru_mapel,tatib saja).
 * Akibatnya klik menu sidebar kepsek selalu redirect ke /login ("tidak memiliki akses").
 * Panes kepsek sebenarnya ada di halaman /kepsek/dashboard itu sendiri, bukan di portal guru.
 */
class KepsekSidebarNavigationTest extends TestCase
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

    public function test_sidebar_kepsek_mengarahkan_gotopane_ke_dashboard_kepsek_bukan_ke_portal_guru(): void
    {
        $kepsek = Guru::whereHas('roles', fn ($q) => $q->where('name', 'kepsek'))->firstOrFail();

        $response = $this->sebagai($kepsek)->get(route('kepsek.dashboard'))->assertOk();

        $response->assertSee(
            "gotoPane('pane-kepsek-dashboard', this, '".route('kepsek.dashboard')."')",
            false
        );
        $response->assertDontSee("gotoPane('pane-kepsek-dashboard', this)", false);
    }

    public function test_kepsek_tidak_bisa_mengakses_portal_guru(): void
    {
        $kepsek = Guru::whereHas('roles', fn ($q) => $q->where('name', 'kepsek'))->firstOrFail();

        // Dokumentasi perilaku saat ini: kepsek memang tidak termasuk role portal guru,
        // jadi gotoPane untuk sidebar kepsek WAJIB diarahkan ke halamannya sendiri (lihat test di atas).
        $this->sebagai($kepsek)->get(route('guru.portal'))->assertRedirect(route('login'));
    }
}
