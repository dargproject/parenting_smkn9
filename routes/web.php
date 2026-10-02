<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\KategoriKasusController;
use App\Http\Controllers\Admin\KategoriPengumumanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MasterPelanggaranController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\RombelController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guru\Bk\JejakRekamController;
use App\Http\Controllers\Guru\Bk\KasusBkController;
use App\Http\Controllers\Guru\BkController;
use App\Http\Controllers\Guru\JurnalController;
use App\Http\Controllers\Guru\KesiswaanController;
use App\Http\Controllers\Guru\KurikulumController;
use App\Http\Controllers\Guru\PenilaianController;
use App\Http\Controllers\Guru\PesanWaliKelasController;
use App\Http\Controllers\Guru\PortalController;
use App\Http\Controllers\KepsekController;
use App\Http\Controllers\OrangTua\DashboardController as OrangTuaDashboardController;
use App\Http\Controllers\OrangTuaAuthController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('profil')->name('profil.')->group(function () {
    Route::put('/', [ProfilController::class, 'update'])->name('update');
    Route::put('/password', [ProfilController::class, 'updatePassword'])->name('password');
});

Route::middleware('role:kepsek')->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepsekController::class, 'index'])->name('dashboard');
    Route::post('/pengumuman', [KepsekController::class, 'broadcastPengumuman'])->name('pengumuman.store');
    Route::get('/laporan/export/{type}', [KepsekController::class, 'exportLaporan'])->name('laporan.export');
});

Route::get('/guru/portal', [PortalController::class, 'index'])
    ->middleware('role:waka_kurikulum,waka_kesiswaan,guru_bk,wali_kelas,guru_wali,guru_mapel,tatib')
    ->name('guru.portal');

Route::get('/guru/api/siswa-by-jadwal/{jadwalId}', [PortalController::class, 'getSiswaByJadwal'])
    ->middleware('role:guru_mapel')
    ->name('guru.api.siswa_by_jadwal');

Route::post('/guru/pelanggaran', [PortalController::class, 'storePelanggaran'])
    ->middleware('role:waka_kesiswaan,guru_mapel,tatib')
    ->name('guru.pelanggaran.store');

Route::post('/guru/kesiswaan/petugas-tatib', [KesiswaanController::class, 'updatePetugasTatib'])
    ->middleware('role:waka_kesiswaan')
    ->name('guru.kesiswaan.petugas-tatib.update');

Route::post('/guru/panggilan-ortu', [PortalController::class, 'storePanggilanOrtu'])
    ->middleware('role:waka_kesiswaan')
    ->name('guru.panggilan-ortu.store');

Route::middleware('role:waka_kurikulum')->prefix('guru/kurikulum')->name('guru.kurikulum.')->group(function () {
    Route::post('/jadwal', [KurikulumController::class, 'storeJadwal'])->name('jadwal.store');
    Route::put('/jadwal/{jadwalPelajaran}', [KurikulumController::class, 'updateJadwal'])->name('jadwal.update');
    Route::post('/jadwal/salin', [KurikulumController::class, 'salinJadwal'])->name('jadwal.salin');
    Route::post('/kelas-mapel', [KurikulumController::class, 'storeKelasMapel'])->name('kelas-mapel.store');
    Route::put('/kelas-mapel/{kelasMataPelajaran}', [KurikulumController::class, 'updateKelasMapel'])->name('kelas-mapel.update');
    Route::delete('/kelas-mapel/{kelasMataPelajaran}', [KurikulumController::class, 'destroyKelasMapel'])->name('kelas-mapel.destroy');
    Route::put('/wali', [KurikulumController::class, 'updateWali'])->name('wali.update');
    Route::delete('/jadwal/{jadwalPelajaran}', [KurikulumController::class, 'destroyJadwal'])->name('jadwal.destroy');
});

Route::middleware('role:guru_mapel')->prefix('guru/penilaian')->name('guru.penilaian.')->group(function () {
    Route::post('/tujuan-pembelajaran', [PenilaianController::class, 'storeTujuanPembelajaran'])->name('tp.store');
    Route::put('/tujuan-pembelajaran/{tujuanPembelajaran}', [PenilaianController::class, 'updateTujuanPembelajaran'])->name('tp.update');
    Route::delete('/tujuan-pembelajaran/{tujuanPembelajaran}', [PenilaianController::class, 'destroyTujuanPembelajaran'])->name('tp.destroy');
    Route::post('/nilai-lm', [PenilaianController::class, 'storeNilaiLm'])->name('nilai-lm.store');
    Route::post('/nilai-sas', [PenilaianController::class, 'storeNilaiSas'])->name('nilai-sas.store');
    Route::post('/catatan-kompetensi', [PenilaianController::class, 'storeCatatanKompetensi'])->name('catatan-kompetensi.store');
    Route::post('/nilai-pkl-ukk', [PenilaianController::class, 'storeNilaiPklUkk'])->name('nilai-pkl-ukk.store');
});

Route::middleware('role:guru_mapel')->post('/guru/jurnal', [JurnalController::class, 'store'])->name('guru.jurnal.store');

Route::middleware('role:guru_bk')->post('/guru/bk/asesmen', [BkController::class, 'storeAsesmen'])->name('guru.bk.asesmen.store');

Route::middleware('role:guru_bk')->prefix('guru/bk')->name('guru.bk.')->group(function () {
    Route::get('/', [App\Http\Controllers\Guru\Bk\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat-siswa/{siswa}', [JejakRekamController::class, 'show'])->name('riwayat-siswa');
    Route::get('/kasus', [KasusBkController::class, 'index'])->name('kasus.index');
    Route::post('/kasus', [KasusBkController::class, 'store'])->name('kasus.store');
    Route::get('/kasus/{kasusBk}', [KasusBkController::class, 'show'])->name('kasus.show');
    Route::put('/kasus/{kasusBk}', [KasusBkController::class, 'update'])->name('kasus.update');
    Route::delete('/kasus/{kasusBk}', [KasusBkController::class, 'destroy'])->name('kasus.destroy');
    Route::patch('/kasus/{kasusBk}/status', [KasusBkController::class, 'updateStatus'])->name('kasus.status');
    Route::get('/kasus/{kasusBk}/export/{template}', [KasusBkController::class, 'export'])->name('kasus.export');
});

Route::middleware('role:wali_kelas')->post('/guru/wali-kelas/catatan', [PenilaianController::class, 'storeCatatanWaliKelas'])->name('guru.wali-kelas.catatan.store');

Route::middleware('role:wali_kelas')->post('/guru/wali-kelas/pesan', [PesanWaliKelasController::class, 'store'])->name('guru.wali-kelas.pesan.store');

Route::middleware('role:guru_wali')->prefix('guru/wali')->name('guru.wali.')->group(function () {
    Route::post('/catatan-akademik', [PenilaianController::class, 'storeCatatanAkademik'])->name('catatan-akademik.store');
    Route::post('/rapor/rilis', [PenilaianController::class, 'rilisRapor'])->name('rapor.rilis');
    Route::post('/rapor/batalkan', [PenilaianController::class, 'batalkanRilis'])->name('rapor.batalkan');
});

Route::prefix('ortu')->name('ortu.')->group(function () {
    Route::get('/login', [OrangTuaAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [OrangTuaAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [OrangTuaAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:orangtua')->group(function () {
        Route::get('/dashboard', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/mapel/{mataPelajaran}', [OrangTuaDashboardController::class, 'show'])->name('mapel.show');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::patch('/tahun-ajaran/{tahun_ajaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajaran.activate');
    Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['show']);
    Route::resource('guru', GuruController::class)->except(['show']);
    Route::post('guru/{guru}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset-password');
    Route::resource('siswa', SiswaController::class)->except(['show']);
    Route::post('siswa/{siswa}/reset-password', [SiswaController::class, 'resetPassword'])->name('siswa.reset-password');
    Route::post('siswa/{siswa}/akun-ortu', [SiswaController::class, 'buatAkunOrtu'])->name('siswa.akun-ortu.store');
    Route::delete('siswa/{siswa}/akun-ortu', [SiswaController::class, 'hapusAkunOrtu'])->name('siswa.akun-ortu.destroy');
    Route::resource('kelas', KelasController::class)->except(['show'])->parameters(['kelas' => 'kelas']);
    Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['show'])->parameters(['mata-pelajaran' => 'mataPelajaran']);
    Route::get('rombel', [RombelController::class, 'index'])->name('rombel.index');
    Route::post('rombel/assign', [RombelController::class, 'assign'])->name('rombel.assign');
    Route::post('rombel/promote', [RombelController::class, 'promote'])->name('rombel.promote');
    Route::resource('master-pelanggaran', MasterPelanggaranController::class)->except(['show'])->parameters(['master-pelanggaran' => 'masterPelanggaran']);
    Route::resource('kategori-pengumuman', KategoriPengumumanController::class)->except(['show'])->parameters(['kategori-pengumuman' => 'kategoriPengumuman']);
    Route::resource('kategori-kasus', KategoriKasusController::class)->except(['show'])->parameters(['kategori-kasus' => 'kategoriKasus']);
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::get('import/template/siswa', [ImportController::class, 'templateSiswa'])->name('import.template.siswa');
    Route::post('import/siswa', [ImportController::class, 'students'])->name('import.siswa');
    Route::post('import/guru', [ImportController::class, 'teachers'])->name('import.guru');
});
