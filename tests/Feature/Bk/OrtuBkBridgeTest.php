<?php

namespace Tests\Feature\Bk;

use App\Models\DataKeluargaSiswa;
use App\Models\GayaBelajar;
use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\KonferensiKasus;
use App\Models\KunjunganRumah;
use App\Models\OrangTua;
use App\Models\PanggilanOrtu;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrtuBkBridgeTest extends TestCase
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

    private function orangTua(): OrangTua
    {
        return OrangTua::with('siswa.kelas')->firstOrFail();
    }

    public function test_dashboard_ortu_menampilkan_nama_konselor_bk_dari_alokasi_kelas(): void
    {
        $orangTua = $this->orangTua();
        $guruBk = $this->guruBk();
        $guruBk->kelasBk()->attach($orangTua->siswa->kelas_id);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee($guruBk->nama);
    }

    public function test_dashboard_ortu_hanya_menampilkan_layanan_bk_yang_di_flag_tampilkan_ke_ortu(): void
    {
        $orangTua = $this->orangTua();
        $siswa = $orangTua->siswa;
        $guruBk = $this->guruBk();

        $kasusTampil = KasusBk::create([
            'siswa_id' => $siswa->id, 'judul' => 'Kunjungan Tampil', 'kategori' => 'Kunjungan Rumah',
            'deskripsi' => 'Uraian rahasia yang tidak boleh terlihat ortu.', 'status' => 'antrean', 'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(), 'konselor_id' => $guruBk->id,
        ]);
        KunjunganRumah::create(['kasus_bk_id' => $kasusTampil->id, 'tanggal_kunjungan' => now()->toDateString(), 'status' => 'diproses', 'tampilkan_ke_ortu' => true]);

        $kasusSembunyi = KasusBk::create([
            'siswa_id' => $siswa->id, 'judul' => 'Konferensi Sembunyi', 'kategori' => 'Konferensi Kasus',
            'deskripsi' => 'Uraian rahasia lain.', 'status' => 'antrean', 'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(), 'konselor_id' => $guruBk->id,
        ]);
        KonferensiKasus::create(['kasus_bk_id' => $kasusSembunyi->id, 'tanggal_konferensi' => now()->toDateString(), 'tampilkan_ke_ortu' => false]);

        $response = $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))->assertOk();

        $response->assertDontSee('Uraian rahasia yang tidak boleh terlihat ortu.');
        $response->assertDontSee('Uraian rahasia lain.');
        $response->assertDontSee('Konferensi Sembunyi');
        $response->assertSee('Jadwal Kunjungan Rumah');
    }

    public function test_dashboard_ortu_menampilkan_panggilan_ortu_milik_anaknya(): void
    {
        $orangTua = $this->orangTua();
        $guruBk = $this->guruBk();

        PanggilanOrtu::create([
            'siswa_id' => $orangTua->siswa->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00',
            'ruang' => 'Ruang BK',
            'alasan' => 'Diskusi perkembangan anak.',
            'status' => 'Menunggu Konfirmasi',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertSee('Diskusi perkembangan anak.');
    }

    public function test_kunjungan_rumah_dan_konferensi_hilang_dari_dashboard_ortu_saat_kasus_selesai(): void
    {
        $orangTua = $this->orangTua();
        $siswa = $orangTua->siswa;
        $guruBk = $this->guruBk();

        $kasusKunjungan = KasusBk::create([
            'siswa_id' => $siswa->id, 'judul' => 'Kunjungan Kasus Selesai', 'kategori' => 'Kunjungan Rumah',
            'deskripsi' => 'Uraian rahasia kunjungan.', 'status' => 'selesai', 'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(), 'konselor_id' => $guruBk->id,
        ]);
        KunjunganRumah::create(['kasus_bk_id' => $kasusKunjungan->id, 'tanggal_kunjungan' => now()->toDateString(), 'status' => 'diproses', 'tampilkan_ke_ortu' => true]);

        $kasusKonferensi = KasusBk::create([
            'siswa_id' => $siswa->id, 'judul' => 'Konferensi Kasus Selesai', 'kategori' => 'Konferensi Kasus',
            'deskripsi' => 'Uraian rahasia konferensi.', 'status' => 'selesai', 'prioritas' => 'sedang',
            'tanggal_mulai' => now()->toDateString(), 'konselor_id' => $guruBk->id,
        ]);
        KonferensiKasus::create(['kasus_bk_id' => $kasusKonferensi->id, 'tanggal_konferensi' => now()->toDateString(), 'tampilkan_ke_ortu' => true]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertDontSee('Kunjungan Kasus Selesai')
            ->assertDontSee('Konferensi Kasus Selesai');
    }

    public function test_panggilan_ortu_hilang_dari_dashboard_ortu_saat_status_hadir(): void
    {
        $orangTua = $this->orangTua();
        $guruBk = $this->guruBk();

        PanggilanOrtu::create([
            'siswa_id' => $orangTua->siswa->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00',
            'ruang' => 'Ruang BK',
            'alasan' => 'Panggilan yang sudah selesai dimediasi.',
            'status' => 'Hadir / Mediasi Selesai',
            'pemanggil_id' => $guruBk->id,
        ]);

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertDontSee('Panggilan yang sudah selesai dimediasi.');
    }

    public function test_ortu_dapat_melengkapi_data_keluarga_anaknya_sendiri(): void
    {
        $orangTua = $this->orangTua();

        $this->actingAs($orangTua, 'orangtua')->get(route('ortu.keluarga.edit'))->assertOk();

        $this->actingAs($orangTua, 'orangtua')->put(route('ortu.keluarga.update'), [
            'nama_ayah' => 'Bapak Uji',
            'nama_ibu' => 'Ibu Uji',
            'punya_kamar_sendiri' => '1',
        ])->assertRedirect(route('ortu.keluarga.edit'));

        $this->assertDatabaseHas('data_keluarga_siswas', [
            'siswa_id' => $orangTua->siswa->id,
            'nama_ayah' => 'Bapak Uji',
            'punya_kamar_sendiri' => true,
        ]);
    }

    public function test_ortu_tidak_bisa_mengubah_data_keluarga_siswa_lain(): void
    {
        $orangTua = $this->orangTua();
        $siswaLain = Siswa::where('id', '!=', $orangTua->siswa_id)->firstOrFail();

        DataKeluargaSiswa::create(['siswa_id' => $siswaLain->id, 'nama_ayah' => 'Punya Orang Lain']);

        $this->actingAs($orangTua, 'orangtua')->put(route('ortu.keluarga.update'), [
            'nama_ayah' => 'Diubah Paksa',
        ]);

        $this->assertDatabaseHas('data_keluarga_siswas', ['siswa_id' => $siswaLain->id, 'nama_ayah' => 'Punya Orang Lain']);
    }

    public function test_guru_bk_dapat_membuat_panggilan_ortu(): void
    {
        $guruBk = $this->guruBk();
        $siswa = $this->orangTua()->siswa;

        $this->sebagai($guruBk)->post(route('guru.bk.panggilan-ortu.store'), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'Terkait kunjungan rumah.',
        ])->assertRedirect();

        $this->assertDatabaseHas('panggilan_ortus', [
            'siswa_id' => $siswa->id,
            'pemanggil_id' => $guruBk->id,
            'status' => 'Menunggu Konfirmasi',
        ]);
    }

    public function test_role_selain_guru_bk_ditolak_membuat_panggilan_ortu_lewat_jalur_bk(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'guru_bk'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('guru.bk.panggilan-ortu.store'), [
            'siswa_id' => $this->orangTua()->siswa->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'alasan' => 'X',
        ])->assertRedirect(route('login'));
    }

    public function test_guru_bk_dapat_mengubah_flag_tampilkan_ke_ortu_pada_gaya_belajar(): void
    {
        $guruBk = $this->guruBk();
        $siswa = $this->orangTua()->siswa;

        $gayaBelajar = GayaBelajar::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jawaban' => [],
            'tampilkan_ke_ortu' => false,
        ]);

        $this->sebagai($guruBk)->put(route('guru.bk.asesmen.gaya-belajar.update', $gayaBelajar), [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'tampilkan_ke_ortu' => '1',
        ])->assertRedirect(route('guru.bk.asesmen.gaya-belajar.show', $gayaBelajar));

        $this->assertDatabaseHas('gaya_belajars', ['id' => $gayaBelajar->id, 'tampilkan_ke_ortu' => true]);
    }
}
