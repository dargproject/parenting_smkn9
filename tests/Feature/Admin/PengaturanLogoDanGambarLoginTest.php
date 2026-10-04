<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaturanLogoDanGambarLoginTest extends TestCase
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

    private function admin(): Guru
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    private function dataSettingWajib(): array
    {
        return [
            'nama_sekolah' => 'SMKN 9 Malang',
            'kktp_threshold' => 75,
            'kktp_margin' => 5,
            'alpa_mingguan_threshold' => 3,
        ];
    }

    public function test_admin_dapat_mengunggah_logo_sekolah_dan_gambar_login(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->sebagai($admin)->put(route('admin.settings.update'), $this->dataSettingWajib() + [
            'logo_sekolah' => UploadedFile::fake()->image('logo.png', 200, 200),
            'gambar_login' => UploadedFile::fake()->image('hero.jpg', 1200, 500),
        ])->assertRedirect();

        $logoPath = Setting::where('key', 'logo_sekolah')->value('value');
        $gambarPath = Setting::where('key', 'gambar_login')->value('value');

        $this->assertNotNull($logoPath);
        $this->assertNotNull($gambarPath);
        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists($gambarPath);
    }

    public function test_mengunggah_ulang_gambar_login_menghapus_file_lama(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->sebagai($admin)->put(route('admin.settings.update'), $this->dataSettingWajib() + [
            'gambar_login' => UploadedFile::fake()->image('hero-lama.jpg', 1200, 500),
        ]);
        $pathLama = Setting::where('key', 'gambar_login')->value('value');
        Storage::disk('public')->assertExists($pathLama);

        $this->sebagai($admin)->put(route('admin.settings.update'), $this->dataSettingWajib() + [
            'gambar_login' => UploadedFile::fake()->image('hero-baru.jpg', 1200, 500),
        ]);
        $pathBaru = Setting::where('key', 'gambar_login')->value('value');

        $this->assertNotSame($pathLama, $pathBaru);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($pathBaru);
    }

    public function test_tidak_mengunggah_ulang_gambar_login_tidak_menghapus_yang_sudah_ada(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->sebagai($admin)->put(route('admin.settings.update'), $this->dataSettingWajib() + [
            'gambar_login' => UploadedFile::fake()->image('hero.jpg', 1200, 500),
        ]);
        $path = Setting::where('key', 'gambar_login')->value('value');

        $this->sebagai($admin)->put(route('admin.settings.update'), $this->dataSettingWajib());

        $this->assertSame($path, Setting::where('key', 'gambar_login')->value('value'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_halaman_login_menampilkan_gambar_login_kustom_jika_ada(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('hero.jpg', 1200, 500)->store('settings', 'public');
        Setting::updateOrCreate(['key' => 'gambar_login'], ['value' => $path]);

        $this->get(route('login'))->assertOk()->assertSee(asset('storage/'.$path), false);
    }

    public function test_halaman_login_pakai_gambar_bawaan_jika_belum_diatur(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('grid-image/image-01.png', false);
    }
}
