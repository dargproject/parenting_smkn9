<?php

namespace Tests\Feature\Bk;

use App\Exports\AsesmenTemplateExport;
use App\Models\GayaBelajar;
use App\Models\Guru;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class GayaBelajarTest extends TestCase
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

    public function test_guru_bk_dapat_membuat_data_gaya_belajar_baru(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $response = $this->sebagai($guruBk)->post(route('guru.bk.asesmen.gaya-belajar.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [
                'Visual' => [0, 1, 2],
                'Auditorial' => [0],
                'Kinestetik' => [],
            ],
            'hasil' => 'Visual',
        ]);

        $gb = GayaBelajar::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($gb);
        $response->assertRedirect(route('guru.bk.asesmen.gaya-belajar.show', $gb));
        $this->assertSame(3, $gb->visual);
        $this->assertSame(1, $gb->auditori);
        $this->assertSame(0, $gb->kinestetik);
        $this->assertSame('Visual', $gb->hasil);

        // Modal "Gaya Belajar Baru" (mode create) tetap mulai dari langkah 1.
        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.gaya-belajar.index'))
            ->assertOk()
            ->assertSee($siswa->nama)
            ->assertSee('step: 1,', false);
    }

    public function test_guru_bk_dapat_melihat_dan_mengubah_data_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();

        $gb = GayaBelajar::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Visual' => [], 'Auditorial' => [], 'Kinestetik' => []],
        ]);

        // Modal edit langsung dibuka di langkah terakhir (hasil & checkbox tampilkan ke ortu).
        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.gaya-belajar.show', $gb))
            ->assertOk()
            ->assertSee($siswa->nama)
            ->assertSee('step: 5,', false);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.gaya-belajar.update', $gb), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => ['Visual' => [], 'Auditorial' => [0, 1], 'Kinestetik' => []],
            'hasil' => 'Auditorial',
        ])->assertRedirect(route('guru.bk.asesmen.gaya-belajar.show', $gb));

        $gb->refresh();
        $this->assertSame(2, $gb->auditori);
        $this->assertSame('Auditorial', $gb->hasil);
    }

    public function test_toggle_tampilkan_ortu_dari_daftar_tanpa_buka_modal(): void
    {
        $guruBk = $this->guruBk();
        $gb = GayaBelajar::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
            'tampilkan_ke_ortu' => false,
        ]);

        $this->sebagai($guruBk)->patch(route('guru.bk.asesmen.gaya-belajar.tampilkan-ortu', $gb))
            ->assertRedirect();
        $this->assertTrue($gb->refresh()->tampilkan_ke_ortu);

        // Klik lagi -- kembali tersembunyi.
        $this->sebagai($guruBk)->patch(route('guru.bk.asesmen.gaya-belajar.tampilkan-ortu', $gb))
            ->assertRedirect();
        $this->assertFalse($gb->refresh()->tampilkan_ke_ortu);
    }

    public function test_bulk_toggle_tampilkan_ortu_untuk_siswa_yang_dicentang(): void
    {
        $guruBk = $this->guruBk();
        $siswas = Siswa::take(3)->get();
        $gbSatu = GayaBelajar::create(['siswa_id' => $siswas[0]->id, 'tanggal' => now()->toDateString(), 'jawaban' => [], 'tampilkan_ke_ortu' => false]);
        $gbDua = GayaBelajar::create(['siswa_id' => $siswas[1]->id, 'tanggal' => now()->toDateString(), 'jawaban' => [], 'tampilkan_ke_ortu' => false]);
        $gbTiga = GayaBelajar::create(['siswa_id' => $siswas[2]->id, 'tanggal' => now()->toDateString(), 'jawaban' => [], 'tampilkan_ke_ortu' => false]);

        // Hanya dua yang dicentang & dikirim -- yang ketiga tidak boleh ikut berubah.
        $this->sebagai($guruBk)->post(route('guru.bk.asesmen.gaya-belajar.bulk-tampilkan-ortu'), [
            'ids' => [$gbSatu->id, $gbDua->id],
            'tampilkan' => '1',
        ])->assertRedirect();

        $this->assertTrue($gbSatu->refresh()->tampilkan_ke_ortu);
        $this->assertTrue($gbDua->refresh()->tampilkan_ke_ortu);
        $this->assertFalse($gbTiga->refresh()->tampilkan_ke_ortu);

        // Sembunyikan lagi keduanya sekaligus.
        $this->sebagai($guruBk)->post(route('guru.bk.asesmen.gaya-belajar.bulk-tampilkan-ortu'), [
            'ids' => [$gbSatu->id, $gbDua->id],
            'tampilkan' => '0',
        ])->assertRedirect();

        $this->assertFalse($gbSatu->refresh()->tampilkan_ke_ortu);
        $this->assertFalse($gbDua->refresh()->tampilkan_ke_ortu);
    }

    public function test_bulk_toggle_tampilkan_ortu_ditolak_tanpa_pilihan_siswa(): void
    {
        $guruBk = $this->guruBk();

        $this->sebagai($guruBk)->post(route('guru.bk.asesmen.gaya-belajar.bulk-tampilkan-ortu'), [
            'ids' => [],
            'tampilkan' => '1',
        ])->assertSessionHasErrors('ids');
    }

    public function test_guru_bk_dapat_menghapus_data_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        $gb = GayaBelajar::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
        ]);

        $this->sebagai($guruBk)->delete(route('guru.bk.asesmen.gaya-belajar.destroy', $gb))
            ->assertRedirect(route('guru.bk.asesmen.gaya-belajar.index'));

        $this->assertDatabaseMissing('gaya_belajars', ['id' => $gb->id]);
    }

    public function test_guru_bk_dapat_unduh_template_xlsx_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        Excel::fake();

        $this->sebagai($guruBk)->get(route('guru.bk.asesmen.gaya-belajar.template'))->assertOk();

        Excel::assertDownloaded('template-gaya-belajar.xlsx', function (AsesmenTemplateExport $export) {
            $this->assertSame('Timestamp', $export->headings()[0]);
            $this->assertCount(count($export->headings()), $export->array()[0]);

            return true;
        });
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_halaman_gaya_belajar(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->get(route('guru.bk.asesmen.gaya-belajar.index'))->assertRedirect(route('login'));
        $this->sebagai($guruMapel)->post(route('guru.bk.asesmen.gaya-belajar.store'), [])->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.asesmen.gaya-belajar.index'))->assertRedirect(route('login'));
    }
}
