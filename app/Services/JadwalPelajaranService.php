<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class JadwalPelajaranService
{
    public function validasi(Request $request): array
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruang' => 'nullable|string|max:100',
        ]);

        // Kolom ruang wajib terisi di database; tampilkan '-' bila tidak diisi.
        $data['ruang'] = $data['ruang'] ?? '-';

        return $data;
    }

    public function cariBentrok(array $data, int $guruId, ?int $abaikanId = null): ?string
    {
        // Bentrok kelas: hanya jika mapel yang SAMA dijadwalkan lagi pada jam yang tumpang tindih
        // (kelas boleh punya mapel berbeda di jam berbeda pada hari yang sama).
        // Bentrok guru: tetap diblokir untuk jam tumpang tindih di kelas manapun & mapel apapun,
        // karena satu guru tidak mungkin mengajar 2 kelas sekaligus.
        $bentrok = JadwalPelajaran::where('hari', $data['hari'])
            ->when(TahunAjaran::where('is_active', true)->value('id'), fn ($q, $ta) => $q->where('tahun_ajaran_id', $ta))
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->where(fn ($q) => $q->where('guru_id', $guruId)
                ->orWhere(fn ($q2) => $q2->where('kelas_id', $data['kelas_id'])->where('mata_pelajaran_id', $data['mata_pelajaran_id']))
            )
            ->with(['kelas', 'guru', 'mataPelajaran'])
            ->first();

        if (! $bentrok) {
            return null;
        }

        $pihak = $bentrok->kelas_id == $data['kelas_id'] && $bentrok->mata_pelajaran_id == $data['mata_pelajaran_id']
            ? "Kelas {$bentrok->kelas->nama_kelas} (mapel {$bentrok->mataPelajaran->nama_mapel})"
            : "Guru {$bentrok->guru->nama}";

        return "Jadwal bentrok: {$pihak} sudah punya jadwal lain pada {$data['hari']} jam {$bentrok->jam_mulai}-{$bentrok->jam_selesai}.";
    }
}
