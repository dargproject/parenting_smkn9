<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    public function storeJadwal(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruang' => 'nullable|string|max:100',
        ]);

        $mapel = MataPelajaran::findOrFail($data['mata_pelajaran_id']);
        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        $bentrok = JadwalPelajaran::where('hari', $data['hari'])
            ->where(fn ($q) => $q->where('kelas_id', $data['kelas_id'])->orWhere('guru_id', $mapel->guru_id))
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->with(['kelas', 'guru'])
            ->first();

        if ($bentrok) {
            $pihak = $bentrok->kelas_id == $data['kelas_id'] ? "Kelas {$bentrok->kelas->nama_kelas}" : "Guru {$bentrok->guru->nama}";
            return back()->withInput()->with('error', "Jadwal bentrok: {$pihak} sudah punya jadwal lain pada {$data['hari']} jam {$bentrok->jam_mulai}-{$bentrok->jam_selesai}.");
        }

        JadwalPelajaran::create($data + [
            'guru_id' => $mapel->guru_id,
            'tahun_ajaran_id' => $tahunAjaranId,
        ]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function destroyJadwal(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
