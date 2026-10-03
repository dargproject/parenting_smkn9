<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RujukanBk;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RujukanBkTest extends TestCase
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

    private function waliKelas(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'wali_kelas'))->firstOrFail();
    }

    private function guruBk(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_bk'))->firstOrFail();
    }

    public function test_wali_kelas_dapat_mengirim_rujukan_untuk_siswa_binaannya(): void
    {
        $wali = $this->waliKelas();
        $kelasWali = Kelas::where('wali_kelas_id', $wali->id)->firstOrFail();
        $siswa = Siswa::where('kelas_id', $kelasWali->id)->firstOrFail();

        $this->sebagai($wali)->post(route('guru.wali-kelas.rujukan-bk.store'), [
            'siswa_id' => $siswa->id,
            'kategori' => 'Perilaku',
            'alasan' => 'Sering terlambat dan murung belakangan ini.',
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('rujukan_bks', [
            'siswa_id' => $siswa->id,
            'dirujuk_oleh' => $wali->id,
            'kategori' => 'Perilaku',
            'status' => 'menunggu',
        ]);
    }

    public function test_wali_kelas_tidak_bisa_merujuk_siswa_di_luar_binaannya(): void
    {
        $wali = $this->waliKelas();
        $kelasLuar = Kelas::create(['nama_kelas' => 'X Uji Coba', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswaLuar = Siswa::create([
            'nis' => '999999', 'nama' => 'Siswa Luar Binaan', 'kelas_id' => $kelasLuar->id,
            'password' => bcrypt('password'), 'status_aktif' => true,
        ]);

        $this->sebagai($wali)->post(route('guru.wali-kelas.rujukan-bk.store'), [
            'siswa_id' => $siswaLuar->id,
            'kategori' => 'Perilaku',
            'alasan' => 'Mencoba merujuk siswa bukan binaan.',
        ])->assertSessionHasErrors('siswa_id');

        $this->assertDatabaseMissing('rujukan_bks', ['siswa_id' => $siswaLuar->id]);
    }

    public function test_guru_bk_dapat_menerima_rujukan_dan_kasus_bk_otomatis_terbuat(): void
    {
        $guruBk = $this->guruBk();
        $rujukan = RujukanBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'dirujuk_oleh' => $this->waliKelas()->id,
            'kategori' => 'Sosial',
            'alasan' => 'Konflik pertemanan di kelas.',
        ]);

        $this->sebagai($guruBk)->get(route('guru.bk.rujukan.index'))->assertOk()->assertSee('Konflik pertemanan di kelas.');

        $this->sebagai($guruBk)->post(route('guru.bk.rujukan.accept', $rujukan))
            ->assertRedirect(route('guru.bk.rujukan.index'));

        $rujukan->refresh();
        $this->assertSame('diterima', $rujukan->status);
        $this->assertSame($guruBk->id, $rujukan->ditangani_oleh);
        $this->assertNotNull($rujukan->kasus_bk_id);

        $this->assertDatabaseHas('kasus_bks', [
            'id' => $rujukan->kasus_bk_id,
            'siswa_id' => $rujukan->siswa_id,
            'konselor_id' => $guruBk->id,
            'kategori' => 'Rujukan Wali Kelas',
            'deskripsi' => 'Konflik pertemanan di kelas.',
        ]);
    }

    public function test_guru_bk_dapat_menolak_rujukan_dengan_catatan(): void
    {
        $guruBk = $this->guruBk();
        $rujukan = RujukanBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'dirujuk_oleh' => $this->waliKelas()->id,
            'kategori' => 'Akademik',
            'alasan' => 'Nilai menurun drastis.',
        ]);

        $this->sebagai($guruBk)->post(route('guru.bk.rujukan.reject', $rujukan), [
            'catatan_penolakan' => 'Sudah ditangani lewat program remedial, bukan isu BK.',
        ])->assertRedirect(route('guru.bk.rujukan.index'));

        $rujukan->refresh();
        $this->assertSame('ditolak', $rujukan->status);
        $this->assertNull($rujukan->kasus_bk_id);
        $this->assertSame('Sudah ditangani lewat program remedial, bukan isu BK.', $rujukan->catatan_penolakan);
    }

    public function test_rujukan_yang_sudah_ditangani_tidak_bisa_ditangani_ulang(): void
    {
        $guruBk = $this->guruBk();
        $rujukan = RujukanBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'dirujuk_oleh' => $this->waliKelas()->id,
            'kategori' => 'Akademik',
            'alasan' => 'X',
            'status' => 'diterima',
            'ditangani_oleh' => $guruBk->id,
        ]);

        $this->sebagai($guruBk)->post(route('guru.bk.rujukan.accept', $rujukan))->assertStatus(422);
    }

    public function test_role_selain_wali_kelas_ditolak_mengirim_rujukan(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'wali_kelas'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('guru.wali-kelas.rujukan-bk.store'), [
            'siswa_id' => Siswa::firstOrFail()->id,
            'kategori' => 'Akademik',
            'alasan' => 'X',
        ])->assertRedirect(route('login'));
    }

    public function test_role_selain_guru_bk_ditolak_mengakses_rujukan_masuk(): void
    {
        $wali = $this->waliKelas();

        $this->sebagai($wali)->get(route('guru.bk.rujukan.index'))->assertRedirect(route('login'));
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->get(route('guru.bk.rujukan.index'))->assertRedirect(route('login'));
    }
}
