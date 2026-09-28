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
}
