<?php

namespace Tests\Feature\Bk;

use App\Models\Guru;
use App\Models\KasusBk;
use App\Models\Role;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

class WordExportTest extends TestCase
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

    private function buatKasus(Guru $konselor, string $judul = 'Kasus Uji Export'): KasusBk
    {
        return KasusBk::create([
            'siswa_id' => Siswa::firstOrFail()->id,
            'judul' => $judul,
            'kategori' => 'Normal',
            'deskripsi' => 'Deskripsi uji.',
            'status' => 'antrean',
            'prioritas' => 'rendah',
            'tanggal_mulai' => now()->toDateString(),
            'konselor_id' => $konselor->id,
        ]);
    }

    private function unduhKeFileSementara($response): string
    {
        $path = storage_path('framework/testing/'.uniqid('export_', true).'.docx');
        file_put_contents($path, $response->streamedContent());

        return $path;
    }

    #[DataProvider('templateProvider')]
    public function test_setiap_template_menghasilkan_docx_yang_valid(string $template): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = $this->buatKasus($guruBk);

        $response = $this->sebagai($guruBk)->get(route('guru.bk.kasus.export', [$kasusBk, $template]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $downloaded = $this->unduhKeFileSementara($response);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($downloaded) === true, "File .docx hasil export template [{$template}] tidak valid/korup.");
        $xml = $zip->getFromName('word/document.xml');
        $this->assertNotFalse($xml, "document.xml tidak ditemukan di hasil export template [{$template}].");
        $zip->close();
        unlink($downloaded);
    }

    public static function templateProvider(): array
    {
        return [
            'form-penanganan-siswa' => ['form-penanganan-siswa'],
            'komulatif-record' => ['komulatif-record'],
            'lembar-sosiometri' => ['lembar-sosiometri'],
        ];
    }

    public function test_data_siswa_terisi_di_dalam_dokumen(): void
    {
        $guruBk = $this->guruBk();
        $siswa = Siswa::firstOrFail();
        $kasusBk = $this->buatKasus($guruBk, 'Kasus Uji Isi Dokumen');

        $response = $this->sebagai($guruBk)->get(route('guru.bk.kasus.export', [$kasusBk, 'form-penanganan-siswa']));
        $downloaded = $this->unduhKeFileSementara($response);

        $zip = new ZipArchive;
        $zip->open($downloaded);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($downloaded);

        $this->assertStringContainsString($siswa->nama, $xml);
        $this->assertStringContainsString($siswa->kelas->nama_kelas, $xml);
    }

    public function test_template_tidak_dikenal_mengembalikan_404(): void
    {
        $guruBk = $this->guruBk();
        $kasusBk = $this->buatKasus($guruBk);

        $this->sebagai($guruBk)
            ->get(route('guru.bk.kasus.export', [$kasusBk, 'template-tidak-ada']))
            ->assertNotFound();
    }

    public function test_guru_bk_lain_tidak_bisa_export_kasus_orang_lain(): void
    {
        $guruBkA = $this->guruBk();
        $kasusMilikA = $this->buatKasus($guruBkA);

        $guruBkB = Guru::create(['nama' => 'Konselor Export', 'nip' => '111222333', 'password' => bcrypt('password')]);
        $guruBkB->roles()->attach(Role::where('name', 'guru_bk')->first());

        $this->sebagai($guruBkB)
            ->get(route('guru.bk.kasus.export', [$kasusMilikA, 'form-penanganan-siswa']))
            ->assertForbidden();
    }
}
