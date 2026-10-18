<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JurnalMengajarTest extends TestCase
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

    private function guruMapel(string $nama = 'Guru Jurnal Uji', string $nip = '9996601'): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'guru_mapel'))->firstOrFail();
    }

    private function buatJurnalDenganPresensi(Guru $guru, bool $denganFoto = false): array
    {
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][now()->dayOfWeek];
        $kelas = Kelas::create(['nama_kelas' => 'X Uji Jurnal', 'tingkat' => 'X', 'jurusan' => 'Uji']);
        $siswa = Siswa::create([
            'nis' => '996601', 'nama' => 'Siswa Uji Jurnal', 'kelas_id' => $kelas->id,
            'password' => bcrypt('password'), 'status_aktif' => true,
        ]);
        $mapel = MataPelajaran::firstOrFail();
        $taAktifId = TahunAjaran::where('is_active', true)->value('id');

        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $namaHari, 'jam_mulai' => '07:00', 'jam_selesai' => '08:00', 'ruang' => '-',
            'tahun_ajaran_id' => $taAktifId,
        ]);

        $foto = $denganFoto ? UploadedFile::fake()->image('jurnal.jpg')->store('jurnal-mengajar', 'public') : null;

        $jurnal = JurnalMengajar::create([
            'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(),
            'materi' => 'Materi uji', 'guru_id' => $guru->id, 'foto' => $foto,
        ]);

        Presensi::create([
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(),
            'status' => 'H', 'tahun_ajaran_id' => $taAktifId,
        ]);

        return compact('jadwal', 'jurnal', 'siswa', 'foto');
    }

    public function test_guru_mapel_dapat_menghapus_jurnal_miliknya_beserta_presensi(): void
    {
        Storage::fake('public');
        $guru = $this->guruMapel();
        ['jurnal' => $jurnal, 'foto' => $foto] = $this->buatJurnalDenganPresensi($guru, denganFoto: true);
        Storage::disk('public')->assertExists($foto);

        $this->sebagai($guru)->delete(route('guru.jurnal.destroy', $jurnal))->assertRedirect(route('guru.portal'));

        $this->assertDatabaseMissing('jurnal_mengajars', ['id' => $jurnal->id]);
        $this->assertDatabaseMissing('presensis', ['jadwal_pelajaran_id' => $jurnal->jadwal_pelajaran_id, 'tanggal' => $jurnal->tanggal]);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_guru_mapel_tidak_bisa_menghapus_jurnal_guru_lain(): void
    {
        $guruA = $this->guruMapel();
        $guruB = Guru::create(['nama' => 'Guru Lain Jurnal', 'nip' => '9996602', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruB->roles()->attach(Role::firstOrCreate(['name' => 'guru_mapel'])->id);

        ['jurnal' => $jurnalB] = $this->buatJurnalDenganPresensi($guruB);

        $this->sebagai($guruA)->delete(route('guru.jurnal.destroy', $jurnalB))->assertStatus(403);
        $this->assertDatabaseHas('jurnal_mengajars', ['id' => $jurnalB->id]);
    }

    public function test_jurnal_bisa_diedit_dengan_submit_ulang_tanggal_dan_jadwal_yang_sama(): void
    {
        $guru = $this->guruMapel();
        ['jadwal' => $jadwal, 'siswa' => $siswa] = $this->buatJurnalDenganPresensi($guru);

        $this->sebagai($guru)->post(route('guru.jurnal.store'), [
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal' => now()->toDateString(),
            'materi' => 'Materi sudah diperbarui',
            'kehadiran' => [$siswa->id => ['status' => 'A', 'keterangan' => 'Diubah jadi alpa']],
        ])->assertRedirect(route('guru.portal'));

        $this->assertDatabaseHas('jurnal_mengajars', [
            'jadwal_pelajaran_id' => $jadwal->id, 'tanggal' => now()->toDateString(), 'materi' => 'Materi sudah diperbarui',
        ]);
        $this->assertDatabaseHas('presensis', [
            'siswa_id' => $siswa->id, 'jadwal_pelajaran_id' => $jadwal->id, 'status' => 'A',
        ]);
        $this->assertSame(1, JurnalMengajar::where('jadwal_pelajaran_id', $jadwal->id)->where('tanggal', now()->toDateString())->count());
    }

    public function test_role_lain_ditolak_menghapus_jurnal(): void
    {
        $guru = $this->guruMapel();
        ['jurnal' => $jurnal] = $this->buatJurnalDenganPresensi($guru);

        $guruBk = Guru::create(['nama' => 'Guru BK Uji Jurnal', 'nip' => '9996603', 'password' => bcrypt('password'), 'is_active' => true]);
        $guruBk->roles()->attach(Role::firstOrCreate(['name' => 'guru_bk'])->id);

        $this->sebagai($guruBk)->delete(route('guru.jurnal.destroy', $jurnal))->assertRedirect(route('login'));
    }
}
