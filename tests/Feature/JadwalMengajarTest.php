<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalMengajarTest extends TestCase
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

    private function guruMapel(string $nama = 'Guru Mapel Uji', string $nip = '9998801'): Guru
    {
        $guru = Guru::create(['nama' => $nama, 'nip' => $nip, 'password' => bcrypt('password'), 'is_active' => true]);
        $guru->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        return $guru;
    }

    private function kelasMapelUntuk(Guru $guru): KelasMataPelajaran
    {
        $kelas = Kelas::create(['nama_kelas' => 'X Uji Jadwal', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $mapel = MataPelajaran::firstOrFail();

        return KelasMataPelajaran::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id]);
    }

    public function test_guru_mapel_dapat_mengisi_sebaran_jadwal_untuk_mapel_miliknya(): void
    {
        $guru = $this->guruMapel();
        $km = $this->kelasMapelUntuk($guru);

        $this->sebagai($guru)->get(route('guru.portal'))->assertOk()->assertSee('Sebaran Jadwal Saya');

        $this->sebagai($guru)->post(route('guru.jadwal-mengajar.store'), [
            'kelas_id' => $km->kelas_id,
            'mata_pelajaran_id' => $km->mata_pelajaran_id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'ruang' => 'R.Uji',
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('jadwal_pelajarans', [
            'kelas_id' => $km->kelas_id,
            'mata_pelajaran_id' => $km->mata_pelajaran_id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
        ]);

        $jadwal = JadwalPelajaran::where('guru_id', $guru->id)->firstOrFail();

        $this->sebagai($guru)->put(route('guru.jadwal-mengajar.update', $jadwal), [
            'kelas_id' => $km->kelas_id,
            'mata_pelajaran_id' => $km->mata_pelajaran_id,
            'hari' => 'Selasa',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ])->assertRedirect(route('guru.portal'));

        $jadwal->refresh();
        $this->assertSame('Selasa', $jadwal->hari);

        $this->sebagai($guru)->delete(route('guru.jadwal-mengajar.destroy', $jadwal))->assertRedirect(route('guru.portal'));
        $this->assertDatabaseMissing('jadwal_pelajarans', ['id' => $jadwal->id]);
    }

    public function test_guru_mapel_tidak_bisa_isi_jadwal_untuk_mapel_yang_bukan_miliknya(): void
    {
        $guruA = $this->guruMapel('Guru A', '9998802');
        $guruB = $this->guruMapel('Guru B', '9998803');
        $km = $this->kelasMapelUntuk($guruB);

        $this->sebagai($guruA)->post(route('guru.jadwal-mengajar.store'), [
            'kelas_id' => $km->kelas_id,
            'mata_pelajaran_id' => $km->mata_pelajaran_id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('jadwal_pelajarans', ['kelas_id' => $km->kelas_id, 'guru_id' => $guruA->id]);
    }

    public function test_guru_mapel_tidak_bisa_edit_atau_hapus_jadwal_guru_lain(): void
    {
        $guruA = $this->guruMapel('Guru A2', '9998804');
        $guruB = $this->guruMapel('Guru B2', '9998805');
        $kmB = $this->kelasMapelUntuk($guruB);

        $jadwalB = JadwalPelajaran::create([
            'kelas_id' => $kmB->kelas_id, 'mata_pelajaran_id' => $kmB->mata_pelajaran_id, 'guru_id' => $guruB->id,
            'hari' => 'Rabu', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00', 'ruang' => '-',
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        $this->sebagai($guruA)->put(route('guru.jadwal-mengajar.update', $jadwalB), [
            'kelas_id' => $kmB->kelas_id, 'mata_pelajaran_id' => $kmB->mata_pelajaran_id,
            'hari' => 'Kamis', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00',
        ])->assertStatus(403);

        $this->sebagai($guruA)->delete(route('guru.jadwal-mengajar.destroy', $jadwalB))->assertStatus(403);
    }

    public function test_bentrok_guru_tetap_terdeteksi_saat_guru_mapel_input_sendiri(): void
    {
        $guru = $this->guruMapel('Guru Bentrok', '9998806');
        $km = $this->kelasMapelUntuk($guru);

        $kelasLain = Kelas::create(['nama_kelas' => 'X Uji Jadwal 2', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $mapelLain = MataPelajaran::skip(1)->first() ?? MataPelajaran::firstOrFail();
        KelasMataPelajaran::create(['kelas_id' => $kelasLain->id, 'mata_pelajaran_id' => $mapelLain->id, 'guru_id' => $guru->id]);

        $this->sebagai($guru)->post(route('guru.jadwal-mengajar.store'), [
            'kelas_id' => $km->kelas_id, 'mata_pelajaran_id' => $km->mata_pelajaran_id,
            'hari' => 'Jumat', 'jam_mulai' => '08:00', 'jam_selesai' => '09:00',
        ])->assertRedirect();

        // Jam tumpang tindih di kelas lain, guru yang sama -- harus ditolak sebagai bentrok.
        $this->sebagai($guru)->post(route('guru.jadwal-mengajar.store'), [
            'kelas_id' => $kelasLain->id, 'mata_pelajaran_id' => $mapelLain->id,
            'hari' => 'Jumat', 'jam_mulai' => '08:30', 'jam_selesai' => '09:30',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('jadwal_pelajarans', ['kelas_id' => $kelasLain->id, 'guru_id' => $guru->id]);
    }

    public function test_role_lain_ditolak_mengakses_jadwal_mengajar(): void
    {
        $guruBk = Guru::create(['nama' => 'Guru BK Uji Jadwal', 'nip' => '9998807', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruBk->roles()->attach(Role::firstOrCreate(['name' => 'guru_bk'])->id);

        $this->sebagai($guruBk)->post(route('guru.jadwal-mengajar.store'), [
            'kelas_id' => Kelas::firstOrFail()->id,
            'mata_pelajaran_id' => MataPelajaran::firstOrFail()->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:00',
        ])->assertRedirect(route('login'));
    }
}
