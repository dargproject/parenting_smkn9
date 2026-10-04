<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\PanggilanOrtu;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanggilanOrtuManajemenTest extends TestCase
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

    private function guruBk(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
    }

    private function wakaKesiswaan(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'waka_kesiswaan'))->firstOrFail();
    }

    public function test_portal_waka_kesiswaan_menampilkan_badge_status_panggilan_tanpa_error(): void
    {
        $waka = $this->wakaKesiswaan();
        PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Uji tampilan badge.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $waka->id,
        ]);

        $this->sebagai($waka)->get(route('guru.portal'))
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi');
    }

    public function test_pembuat_panggilan_dapat_mengedit_panggilannya_sendiri(): void
    {
        $guruBk = $this->guruBk();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Alasan awal.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->put(route('panggilan-ortu.update', $panggilan), [
            'tanggal' => now()->toDateString(),
            'waktu' => '11:00',
            'ruang' => 'Ruang BK Baru',
            'alasan' => 'Alasan sudah diperbarui.',
        ])->assertRedirect();

        $this->assertDatabaseHas('panggilan_ortus', [
            'id' => $panggilan->id,
            'alasan' => 'Alasan sudah diperbarui.',
            'ruang' => 'Ruang BK Baru',
        ]);
    }

    public function test_reschedule_setelah_status_hadir_otomatis_kembali_ke_menunggu_konfirmasi(): void
    {
        $guruBk = $this->guruBk();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => '2026-10-03',
            'waktu' => '09:00',
            'alasan' => 'Alasan awal.',
            'status' => 'Hadir / Mediasi Selesai',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->put(route('panggilan-ortu.update', $panggilan), [
            'tanggal' => '2026-10-10',
            'waktu' => '09:00',
            'alasan' => 'Alasan awal.',
        ])->assertRedirect();

        $this->assertDatabaseHas('panggilan_ortus', ['id' => $panggilan->id, 'status' => 'Menunggu Konfirmasi']);
    }

    public function test_edit_tanpa_mengubah_jadwal_tidak_mereset_status_hadir(): void
    {
        $guruBk = $this->guruBk();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => '2026-10-03',
            'waktu' => '09:00',
            'alasan' => 'Alasan awal.',
            'status' => 'Hadir / Mediasi Selesai',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->put(route('panggilan-ortu.update', $panggilan), [
            'tanggal' => '2026-10-03',
            'waktu' => '09:00',
            'alasan' => 'Alasan diperjelas, jadwal tetap sama.',
        ])->assertRedirect();

        $this->assertDatabaseHas('panggilan_ortus', ['id' => $panggilan->id, 'status' => 'Hadir / Mediasi Selesai']);
    }

    public function test_bukan_pembuat_tidak_bisa_mengedit_atau_menghapus_panggilan_orang_lain(): void
    {
        $guruBk = $this->guruBk();
        $wakaLain = $this->wakaKesiswaan();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Dibuat oleh BK.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($wakaLain)->put(route('panggilan-ortu.update', $panggilan), [
            'tanggal' => now()->toDateString(),
            'waktu' => '12:00',
            'alasan' => 'Mencoba mengubah milik orang lain.',
        ])->assertForbidden();

        $this->sebagai($wakaLain)->delete(route('panggilan-ortu.destroy', $panggilan))->assertForbidden();

        $this->assertDatabaseHas('panggilan_ortus', ['id' => $panggilan->id, 'alasan' => 'Dibuat oleh BK.']);
    }

    public function test_pembuat_dapat_menghapus_panggilannya_sendiri(): void
    {
        $guruBk = $this->guruBk();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Akan dihapus.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->delete(route('panggilan-ortu.destroy', $panggilan))->assertRedirect();

        $this->assertDatabaseMissing('panggilan_ortus', ['id' => $panggilan->id]);
    }

    public function test_status_panggilan_dapat_diubah_menjadi_hadir_oleh_siapapun_yang_berwenang(): void
    {
        $guruBk = $this->guruBk();
        $waka = $this->wakaKesiswaan();
        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Dibuat oleh BK.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->sebagai($waka)->patch(route('panggilan-ortu.update-status', $panggilan), [
            'status' => 'Hadir / Mediasi Selesai',
        ])->assertRedirect();

        $this->assertDatabaseHas('panggilan_ortus', ['id' => $panggilan->id, 'status' => 'Hadir / Mediasi Selesai']);
    }

    public function test_role_lain_ditolak_mengakses_manajemen_panggilan_ortu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['guru_bk', 'waka_kesiswaan']))
            ->firstOrFail();

        $panggilan = PanggilanOrtu::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'X',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $this->guruBk()->id,
        ]);

        $this->sebagai($guruMapel)->delete(route('panggilan-ortu.destroy', $panggilan))->assertRedirect(route('login'));
    }
}
