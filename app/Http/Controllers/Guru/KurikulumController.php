<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KurikulumController extends Controller
{
    private function validasiJadwal(Request $request): array
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

    private function cariBentrok(array $data, int $guruId, ?int $abaikanId = null): ?string
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

    public function storeJadwal(Request $request)
    {
        $data = $this->validasiJadwal($request);
        $penetapan = KelasMataPelajaran::where('kelas_id', $data['kelas_id'])->where('mata_pelajaran_id', $data['mata_pelajaran_id'])->first();

        if (! $penetapan || ! $penetapan->guru_id) {
            return back()->withInput()->with('error', 'Mata pelajaran ini belum ditetapkan (beserta gurunya) untuk kelas tersebut. Tetapkan dulu pada bagian "Mapel per Kelas".');
        }

        if ($pesan = $this->cariBentrok($data, $penetapan->guru_id)) {
            return back()->withInput()->with('error', $pesan);
        }

        JadwalPelajaran::create($data + [
            'guru_id' => $penetapan->guru_id,
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function updateJadwal(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $data = $this->validasiJadwal($request);
        $penetapan = KelasMataPelajaran::where('kelas_id', $data['kelas_id'])->where('mata_pelajaran_id', $data['mata_pelajaran_id'])->first();

        if (! $penetapan || ! $penetapan->guru_id) {
            return back()->withInput()->with('error', 'Mata pelajaran ini belum ditetapkan (beserta gurunya) untuk kelas tersebut. Tetapkan dulu pada bagian "Mapel per Kelas".');
        }

        if ($pesan = $this->cariBentrok($data, $penetapan->guru_id, $jadwalPelajaran->id)) {
            return back()->withInput()->with('error', $pesan);
        }

        $jadwalPelajaran->update($data + ['guru_id' => $penetapan->guru_id]);

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function storeKelasMapel(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|array|min:1',
            'mata_pelajaran_id.*' => 'exists:mata_pelajarans,id',
            'guru_id' => 'required|exists:gurus,id',
        ]);

        $guruRole = Role::where('name', 'guru_mapel')->value('id');

        DB::transaction(function () use ($data, $guruRole) {
            foreach ($data['mata_pelajaran_id'] as $mapelId) {
                $guruId = $data['guru_id'];

                KelasMataPelajaran::updateOrCreate(
                    ['kelas_id' => $data['kelas_id'], 'mata_pelajaran_id' => $mapelId],
                    ['guru_id' => $guruId]
                );

                JadwalPelajaran::where('kelas_id', $data['kelas_id'])->where('mata_pelajaran_id', $mapelId)->update(['guru_id' => $guruId]);
                if ($guruId && $guruRole) {
                    Guru::find($guruId)?->roles()->syncWithoutDetaching([$guruRole]);
                }
            }
        });

        return redirect()->route('guru.portal')->with('success', count($data['mata_pelajaran_id']).' mata pelajaran berhasil ditetapkan untuk kelas tersebut.');
    }

    public function updateKelasMapel(Request $request, KelasMataPelajaran $kelasMataPelajaran)
    {
        $data = $request->validate(['guru_id' => 'required|exists:gurus,id']);

        $kelasMataPelajaran->update($data);
        JadwalPelajaran::where('kelas_id', $kelasMataPelajaran->kelas_id)->where('mata_pelajaran_id', $kelasMataPelajaran->mata_pelajaran_id)->update(['guru_id' => $data['guru_id']]);

        return redirect()->route('guru.portal')->with('success', 'Guru pengampu berhasil diperbarui.');
    }

    public function destroyKelasMapel(KelasMataPelajaran $kelasMataPelajaran)
    {
        if (JadwalPelajaran::where('kelas_id', $kelasMataPelajaran->kelas_id)->where('mata_pelajaran_id', $kelasMataPelajaran->mata_pelajaran_id)->exists()) {
            return back()->with('error', 'Mata pelajaran ini masih punya jadwal di kelas tersebut. Hapus jadwalnya terlebih dahulu.');
        }

        $kelasMataPelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Mata pelajaran dihapus dari struktur kelas.');
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
                if (! $kelas) {
                    continue;
                }

                $kelas->update([
                    'wali_kelas_id' => $row['wali_kelas_id'] ?: null,
                    'guru_wali_id' => $row['guru_wali_id'] ?: null,
                ]);

                foreach (['wali_kelas_id' => 'wali_kelas', 'guru_wali_id' => 'guru_wali'] as $kolom => $role) {
                    if (! empty($row[$kolom])) {
                        Guru::find($row[$kolom])?->roles()->syncWithoutDetaching([$roleIds[$role]]);
                    }
                }
            }
        });

        return redirect()->route('guru.portal')->with('success', 'Penetapan Wali Kelas & Guru Wali berhasil disimpan.');
    }

    public function salinJadwal()
    {
        $aktifId = TahunAjaran::where('is_active', true)->value('id');
        abort_unless($aktifId, 422, 'Belum ada tahun ajaran aktif.');

        if (JadwalPelajaran::where('tahun_ajaran_id', $aktifId)->exists()) {
            return back()->with('error', 'Tahun ajaran aktif sudah memiliki jadwal, penyalinan dibatalkan agar tidak dobel.');
        }

        $sumberId = JadwalPelajaran::where('tahun_ajaran_id', '!=', $aktifId)->orderByDesc('tahun_ajaran_id')->value('tahun_ajaran_id');
        if (! $sumberId) {
            return back()->with('error', 'Tidak ada jadwal dari tahun ajaran sebelumnya untuk disalin.');
        }

        $penetapan = KelasMataPelajaran::all()->keyBy(fn ($km) => $km->kelas_id.'-'.$km->mata_pelajaran_id);
        $disalin = 0;
        $dilewati = 0;

        DB::transaction(function () use ($sumberId, $aktifId, $penetapan, &$disalin, &$dilewati) {
            foreach (JadwalPelajaran::where('tahun_ajaran_id', $sumberId)->get() as $j) {
                $guruId = $penetapan[$j->kelas_id.'-'.$j->mata_pelajaran_id]->guru_id ?? null;
                if (! $guruId) {
                    $dilewati++;
                    continue;
                }

                JadwalPelajaran::create([
                    'kelas_id' => $j->kelas_id, 'mata_pelajaran_id' => $j->mata_pelajaran_id, 'guru_id' => $guruId,
                    'hari' => $j->hari, 'jam_mulai' => $j->jam_mulai, 'jam_selesai' => $j->jam_selesai,
                    'ruang' => $j->ruang, 'tahun_ajaran_id' => $aktifId,
                ]);
                $disalin++;
            }
        });

        return redirect()->route('guru.portal')->with('success', "{$disalin} jadwal disalin dari tahun ajaran sebelumnya".($dilewati ? ", {$dilewati} dilewati karena mapel belum ditetapkan ke kelasnya." : '.'));
    }

    public function destroyJadwal(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->delete();

        return redirect()->route('guru.portal')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
