<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KategoriKasus;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BkFondasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_kasus_bk_punya_relasi_baru_dan_tetap_kompatibel_dengan_kanban(): void
    {
        $guruBk = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
        $siswa = Siswa::firstOrFail();
        $kategori = KategoriKasus::firstOrFail();
        $ta = TahunAjaran::where('is_active', true)->firstOrFail();

        $kasus = KasusBk::create([
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $ta->id,
            'judul' => 'Uji Fondasi BK',
            'kategori' => 'Normal',
            'kategori_id' => $kategori->id,
            'deskripsi' => 'Deskripsi uji.',
            'status' => 'antrean',
            'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $guruBk->id,
        ]);

        $this->assertTrue($kasus->tahunAjaran->is($ta));
        $this->assertTrue($kasus->kategoriKasus->is($kategori));
        $this->assertTrue($kasus->siswa->is($siswa));
        $this->assertCount(0, $kasus->lampiran);

        // Kanban di portal guru membaca kasusBks->where('status', ...) dan properti lama
        // (kategori, judul, siswa->nama): pastikan tetap tidak error setelah kolom baru ditambahkan.
        $roles = $guruBk->roles->pluck('name')->all();
        $response = $this->actingAs($guruBk)->withSession([
            'guru_id' => $guruBk->id,
            'roles' => $roles,
            'role' => $roles[0] ?? '',
        ])->get(route('guru.portal'));

        $response->assertOk();
        $response->assertSee('Uji Fondasi BK');
    }

    public function test_siswa_punya_relasi_profil_dan_data_keluarga(): void
    {
        $siswa = Siswa::firstOrFail();

        $this->assertNull($siswa->profilSiswa);
        $this->assertNull($siswa->dataKeluarga);

        $siswa->profilSiswa()->create(['alamat' => 'Jl. Uji No. 1']);
        $siswa->dataKeluarga()->create(['nama_ayah' => 'Bapak Uji']);

        $siswa->refresh();
        $this->assertSame('Jl. Uji No. 1', $siswa->profilSiswa->alamat);
        $this->assertSame('Bapak Uji', $siswa->dataKeluarga->nama_ayah);
    }
}
