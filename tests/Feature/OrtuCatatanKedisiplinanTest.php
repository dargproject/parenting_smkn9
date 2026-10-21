<?php

namespace Tests\Feature;

use App\Models\JenisPelanggaran;
use App\Models\MasterPelanggaran;
use App\Models\OrangTua;
use App\Models\Pasal;
use App\Models\Pelanggaran;
use App\Models\PenguranganPoin;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrtuCatatanKedisiplinanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_tanpa_pelanggaran_menampilkan_status_aman(): void
    {
        $orangTua = OrangTua::with('siswa')->firstOrFail();

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('Catatan Kedisiplinan')
            ->assertSee('Aman')
            ->assertSee('Tidak ada pelanggaran tercatat pada semester ini.');
    }

    public function test_dashboard_menampilkan_poin_bersih_dan_tindakan(): void
    {
        $orangTua = OrangTua::with('siswa')->firstOrFail();
        $siswa = $orangTua->siswa;
        $ta = TahunAjaran::where('is_active', true)->first();

        $jenis = JenisPelanggaran::create(['kode' => 'J-ortu-uji', 'nama' => 'Jenis Uji Ortu', 'poin' => 15]);
        $pasal = Pasal::create(['kode' => 'P-ortu-uji', 'nama' => 'Pasal Uji Ortu']);
        $master = MasterPelanggaran::create(['nama_pelanggaran' => 'Pelanggaran Uji Ortu', 'pasal_id' => $pasal->id, 'jenis_id' => $jenis->id, 'is_active' => true]);

        Pelanggaran::create([
            'siswa_id' => $siswa->id, 'master_pelanggaran_id' => $master->id, 'tanggal' => now()->toDateString(),
            'tahun_ajaran_id' => $ta->id, 'kategori' => 'Uji', 'judul' => 'Pelanggaran Uji Ortu', 'deskripsi' => 'Uji',
            'poin' => 15, 'tindakan' => 'Menulis istighfar 25 kali',
        ]);
        PenguranganPoin::create([
            'siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ta->id, 'tanggal' => now()->toDateString(),
            'kegiatan' => 'Petugas Upacara', 'poin' => 3,
        ]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('Catatan Kedisiplinan')
            ->assertSee('Surat Peringatan 1')
            ->assertSee('12 poin')
            ->assertSee('Pelanggaran Uji Ortu')
            ->assertSee('Menulis istighfar 25 kali')
            ->assertDontSee('Tindakan Langsung');
    }
}
