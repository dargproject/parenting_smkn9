<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\OrangTuaSiswa;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SiswaAkunOrtuTest extends TestCase
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

    private function buatSiswa(string $nis, ?string $noHpOrtu): Siswa
    {
        $kelas = Kelas::firstOrCreate(['nama_kelas' => 'X Generate Ortu Uji'], ['tingkat' => 'X', 'jurusan' => 'Uji']);

        return Siswa::create([
            'nis' => $nis, 'nama' => 'Siswa Generate Ortu '.$nis, 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true, 'no_hp_ortu' => $noHpOrtu,
        ]);
    }

    private function buatAkunOrtu(Siswa $siswa, string $username, bool $aktif = true): OrangTua
    {
        $orangTua = OrangTua::create(['nama' => 'Ortu '.$siswa->nama, 'username' => $username, 'password' => bcrypt('password'), 'is_active' => $aktif]);
        $orangTua->links()->create(['siswa_id' => $siswa->id, 'is_active' => $aktif]);

        return $orangTua;
    }

    public function test_generate_semua_membuat_akun_untuk_siswa_yang_punya_no_hp_dan_belum_punya_akun(): void
    {
        $siswa = $this->buatSiswa('998901', '081234567891');

        $response = $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orang_tuas', ['username' => '081234567891']);
        $this->assertDatabaseHas('orang_tua_siswa', ['siswa_id' => $siswa->id]);
    }

    public function test_generate_semua_melewati_siswa_yang_belum_diisi_no_hp_ortu(): void
    {
        $siswa = $this->buatSiswa('998902', null);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $this->assertDatabaseMissing('orang_tua_siswa', ['siswa_id' => $siswa->id]);
    }

    public function test_generate_semua_melewati_siswa_yang_sudah_punya_akun_tanpa_mengubahnya(): void
    {
        $siswa = $this->buatSiswa('998903', '081234567892');
        $akunLama = $this->buatAkunOrtu($siswa, '081234567892');
        $akunLama->update(['nama' => 'Nama Akun Lama']);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        $this->assertSame(1, OrangTuaSiswa::where('siswa_id', $siswa->id)->count());
        $this->assertSame('Nama Akun Lama', $akunLama->refresh()->nama);
    }

    public function test_generate_semua_menautkan_nomor_hp_yang_sama_ke_akun_yang_sama_untuk_kakak_adik(): void
    {
        $siswaSatu = $this->buatSiswa('998904', '081234567893');
        $siswaDua = $this->buatSiswa('998905', '081234567893');

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.generate-all'));

        // Satu nomor HP yang sama dipakai dua anak -- harus jadi satu akun, bukan dua akun
        // terpisah atau salah satunya dilewati.
        $this->assertSame(1, OrangTua::where('username', '081234567893')->count());
        $this->assertTrue(OrangTuaSiswa::where('siswa_id', $siswaSatu->id)->exists());
        $this->assertTrue(OrangTuaSiswa::where('siswa_id', $siswaDua->id)->exists());
        $this->assertSame(
            OrangTuaSiswa::where('siswa_id', $siswaSatu->id)->value('orang_tua_id'),
            OrangTuaSiswa::where('siswa_id', $siswaDua->id)->value('orang_tua_id'),
        );
    }

    public function test_bukan_admin_ditolak_generate_semua_akun_ortu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('admin.siswa.akun-ortu.generate-all'))->assertRedirect(route('login'));
    }

    public function test_admin_bisa_menyetujui_satu_akun_ortu_lintas_kelas(): void
    {
        $siswa = $this->buatSiswa('998906', null);
        $this->buatAkunOrtu($siswa, '081234567894', aktif: false);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.setujui', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $siswa->linkOrtu->fresh()->is_active);
        $this->assertTrue((bool) $siswa->orangTua->fresh()->is_active);
    }

    public function test_admin_bisa_menyetujui_semua_akun_ortu_pending_sekaligus(): void
    {
        $siswaSatu = $this->buatSiswa('998907', null);
        $siswaDua = $this->buatSiswa('998908', null);
        $akunSudahAktif = $this->buatSiswa('998909', '081234567897');
        $this->buatAkunOrtu($siswaSatu, '081234567895', aktif: false);
        $this->buatAkunOrtu($siswaDua, '081234567896', aktif: false);
        $akunAktif = $this->buatAkunOrtu($akunSudahAktif, '081234567897', aktif: true);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.setujui-semua'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, OrangTuaSiswa::where('is_active', false)->count());
        $this->assertTrue((bool) $akunAktif->fresh()->is_active);
    }

    public function test_menyetujui_satu_anak_tidak_ikut_menyetujui_anak_lain_di_akun_yang_sama(): void
    {
        $siswaSatu = $this->buatSiswa('998912', null);
        $siswaDua = $this->buatSiswa('998913', null);
        $orangTua = $this->buatAkunOrtu($siswaSatu, '081234567900', aktif: true);
        $orangTua->links()->create(['siswa_id' => $siswaDua->id, 'is_active' => false]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.setujui', $siswaDua));

        $this->assertTrue((bool) $siswaDua->linkOrtu->fresh()->is_active);
    }

    public function test_hapus_akun_ortu_satu_anak_tidak_menghapus_tautan_anak_lain(): void
    {
        $siswaSatu = $this->buatSiswa('998914', null);
        $siswaDua = $this->buatSiswa('998915', null);
        $orangTua = $this->buatAkunOrtu($siswaSatu, '081234567901', aktif: true);
        $orangTua->links()->create(['siswa_id' => $siswaDua->id, 'is_active' => true]);

        $this->sebagai($this->admin())->delete(route('admin.siswa.akun-ortu.destroy', $siswaSatu))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('orang_tua_siswa', ['siswa_id' => $siswaSatu->id]);
        $this->assertDatabaseHas('orang_tua_siswa', ['siswa_id' => $siswaDua->id]);
        $this->assertDatabaseHas('orang_tuas', ['id' => $orangTua->id]);
    }

    public function test_halaman_daftar_siswa_anak_kedua_tetap_tampil_menunggu_walau_akun_sudah_aktif(): void
    {
        // Anak pertama sudah aktif (akunnya sendiri jadi is_active=true), tapi anak kedua yang
        // baru ditautkan ke akun yang sama masih menunggu persetujuan -- statusnya per anak,
        // bukan ikut status akun yang sudah aktif lebih dulu.
        $siswaSatu = $this->buatSiswa('998916', null);
        $siswaDua = $this->buatSiswa('998917', null);
        $orangTua = $this->buatAkunOrtu($siswaSatu, '081234567902', aktif: true);
        $orangTua->links()->create(['siswa_id' => $siswaDua->id, 'is_active' => false]);

        $this->sebagai($this->admin())->get(route('admin.siswa.index', ['q' => $siswaDua->nis]))
            ->assertOk()
            ->assertSee('Menunggu Persetujuan', false)
            ->assertDontSee('Akun Ortu &#10003;', false);
    }

    public function test_halaman_daftar_siswa_merender_baris_akun_ortu_pending(): void
    {
        $siswa = $this->buatSiswa('998910', null);
        $this->buatAkunOrtu($siswa, '081234567898', aktif: false);

        // Filter ke siswa ini saja -- daftar siswa dipaginasi, siswa baru bisa saja tidak
        // masuk 15 teratas kalau data seeder lebih banyak/lebih baru.
        $this->sebagai($this->admin())->get(route('admin.siswa.index', ['q' => $siswa->nis]))
            ->assertOk()
            ->assertSee('Menunggu Persetujuan', false)
            ->assertSee('Setujui Semua Akun Ortu', false);
    }

    public function test_admin_bisa_reset_password_akun_ortu_yang_lupa_password(): void
    {
        $siswa = $this->buatSiswa('998911', null);
        $akun = $this->buatAkunOrtu($siswa, '081234567899', aktif: true);
        $akun->update(['harus_lengkapi_profil' => false]);

        $this->sebagai($this->admin())->post(route('admin.siswa.akun-ortu.reset-password', $siswa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $akun->refresh();
        $this->assertTrue(Hash::check('password', $akun->password));
        $this->assertTrue((bool) $akun->harus_lengkapi_profil);
    }

    public function test_bukan_admin_ditolak_setujui_semua_akun_ortu(): void
    {
        $guruMapel = Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();

        $this->sebagai($guruMapel)->post(route('admin.siswa.akun-ortu.setujui-semua'))->assertRedirect(route('login'));
    }
}
