<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    private function validasiJadwal(Request $request): array
    {
        return $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruang' => 'nullable|string|max:100',
        ]);
    }

    private function cariBentrok(array $data, int $guruId, ?int $abaikanId = null): ?string
    {
        $bentrok = JadwalPelajaran::where('hari', $data['hari'])
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
            ->where(fn ($q) => $q->where('kelas_id', $data['kelas_id'])->orWhere('guru_id', $guruId))
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->with(['kelas', 'guru'])
            ->first();

        if (!$bentrok) {
            return null;
        }

        $pihak = $bentrok->kelas_id == $data['kelas_id'] ? "Kelas {$bentrok->kelas->nama_kelas}" : "Guru {$bentrok->guru->nama}";

        return "Jadwal bentrok: {$pihak} sudah punya jadwal lain pada {$data['hari']} jam {$bentrok->jam_mulai}-{$bentrok->jam_selesai}.";
    }

    public function storeJadwal(Request $request)
    {
        $data = $this->validasiJadwal($request);
        $mapel = MataPelajaran::findOrFail($data['mata_pelajaran_id']);

        if ($pesan = $this->cariBentrok($data, $mapel->guru_id)) {
            return back()->withInput()->with('error', $pesan);
        }

        JadwalPelajaran::create($data + [
            'guru_id' => $mapel->guru_id,
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function updateJadwal(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $data = $this->validasiJadwal($request);
        $mapel = MataPelajaran::findOrFail($data['mata_pelajaran_id']);

        if ($pesan = $this->cariBentrok($data, $mapel->guru_id, $jadwalPelajaran->id)) {
            return back()->withInput()->with('error', $pesan);
        }

        $jadwalPelajaran->update($data + ['guru_id' => $mapel->guru_id]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function updateWali(Request $request)
    {
        $data = $request->validate([
            'wali' => 'required|array',
            'wali.*.wali_kelas_id' => 'nullable|exists:gurus,id',
            'wali.*.guru_wali_id' => 'nullable|exists:gurus,id',
        ]);

        $roleIds = Role::whereIn('name', ['wali_kelas', 'guru_wali'])->pluck('id', 'name');

        DB::transaction(function () use ($data, $roleIds) {
            foreach ($data['wali'] as $kelasId => $row) {
                $kelas = Kelas::find($kelasId);
                if (!$kelas) {
                    continue;
                }

                $kelas->update([
                    'wali_kelas_id' => $row['wali_kelas_id'] ?: null,
                    'guru_wali_id' => $row['guru_wali_id'] ?: null,
                ]);

                foreach (['wali_kelas_id' => 'wali_kelas', 'guru_wali_id' => 'guru_wali'] as $kolom => $role) {
                    if (!empty($row[$kolom])) {
                        Guru::find($row[$kolom])?->roles()->syncWithoutDetaching([$roleIds[$role]]);
                    }
                }
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Penetapan Wali Kelas & Guru Wali berhasil disimpan.');
    }

    public function destroyJadwal(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
