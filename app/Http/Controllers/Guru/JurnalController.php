<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JurnalController extends Controller
{
    private const HARI = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    public function store(Request $request)
    {
        $data = $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'materi' => 'required|string|max:1000',
            'foto' => 'nullable|image|max:2048',
            'kehadiran' => 'required|array|min:1',
            'kehadiran.*.status' => 'required|in:H,S,I,A',
            'kehadiran.*.keterangan' => 'nullable|string|max:255',
        ]);

        $jadwal = JadwalPelajaran::findOrFail($data['jadwal_pelajaran_id']);
        abort_unless($jadwal->guru_id === Auth::id(), 403, 'Jadwal ini bukan milik Anda.');

        $namaHariTanggal = self::HARI[Carbon::parse($data['tanggal'])->dayOfWeek];
        if ($namaHariTanggal !== $jadwal->hari) {
            return back()->withInput()->with('error', "Tanggal yang dipilih adalah hari {$namaHariTanggal}, sedangkan jadwal ini dijadwalkan pada hari {$jadwal->hari}.");
        }

        $siswaValidIds = Siswa::where('kelas_id', $jadwal->kelas_id)->pluck('id');
        $kehadiranValid = collect($data['kehadiran'])->only($siswaValidIds->map(fn ($id) => (string) $id));
        if ($kehadiranValid->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ada data presensi siswa yang valid untuk kelas jadwal ini.');
        }

        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        DB::transaction(function () use ($request, $data, $kehadiranValid, $tahunAjaranId) {
            $update = ['materi' => $data['materi'], 'guru_id' => Auth::id()];
            if ($request->hasFile('foto')) {
                $update['foto'] = $request->file('foto')->store('jurnal-mengajar', 'public');
                $this->kecilkanFotoJikaBesar($update['foto']);
            }

            JurnalMengajar::updateOrCreate(
                ['jadwal_pelajaran_id' => $data['jadwal_pelajaran_id'], 'tanggal' => $data['tanggal']],
                $update
            );

            foreach ($kehadiranValid as $siswaId => $row) {
                Presensi::updateOrCreate(
                    ['siswa_id' => $siswaId, 'jadwal_pelajaran_id' => $data['jadwal_pelajaran_id'], 'tanggal' => $data['tanggal']],
                    ['status' => $row['status'], 'keterangan' => $row['keterangan'] ?? null, 'tahun_ajaran_id' => $tahunAjaranId]
                );
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Jurnal mengajar dan presensi berhasil disimpan.');
    }

    /**
     * Pengaman di server kalau kompresi di browser gagal/dilewati (browser lama,
     * JS nonaktif) -- foto tetap diperkecil dimensinya supaya tidak boros storage.
     */
    private function kecilkanFotoJikaBesar(string $path): void
    {
        $fullPath = Storage::disk('public')->path($path);
        $ukuran = @getimagesize($fullPath);

        if (! $ukuran) {
            return;
        }

        [$lebar, $tinggi] = $ukuran;
        $maxSisi = 1600;

        if ($lebar <= $maxSisi && $tinggi <= $maxSisi) {
            return;
        }

        $sumber = @imagecreatefromstring(file_get_contents($fullPath));
        if (! $sumber) {
            return;
        }

        $skala = $maxSisi / max($lebar, $tinggi);
        $lebarBaru = (int) round($lebar * $skala);
        $tinggiBaru = (int) round($tinggi * $skala);

        $tujuan = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
        imagejpeg($tujuan, $fullPath, 75);

        imagedestroy($sumber);
        imagedestroy($tujuan);
    }

    public function destroy(JurnalMengajar $jurnal)
    {
        abort_unless($jurnal->guru_id === Auth::id(), 403, 'Jurnal ini bukan milik Anda.');

        DB::transaction(function () use ($jurnal) {
            // Presensi dibuat bersamaan dengan jurnal (lihat store()), terikat lewat jadwal_pelajaran_id + tanggal
            // yang sama -- jadi ikut dihapus supaya tidak menyisakan data kehadiran "yatim" tanpa jurnal.
            Presensi::where('jadwal_pelajaran_id', $jurnal->jadwal_pelajaran_id)
                ->where('tanggal', $jurnal->tanggal)
                ->delete();

            if ($jurnal->foto) {
                Storage::disk('public')->delete($jurnal->foto);
            }

            $jurnal->delete();
        });

        return redirect()->route('guru.portal')->with('success', 'Jurnal mengajar dan presensi siswa pada tanggal tersebut berhasil dihapus.');
    }
}
