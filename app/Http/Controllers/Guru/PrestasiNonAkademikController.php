<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PrestasiNonAkademik;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PrestasiNonAkademikController extends Controller
{
    private function siswaWaliIds()
    {
        $kelasWaliIds = Kelas::where('wali_kelas_id', Auth::id())->pluck('id');

        return Kelas::whereIn('id', $kelasWaliIds)->with('siswas:id,kelas_id')->get()->flatMap->siswas->pluck('id');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', Rule::in($this->siswaWaliIds())],
            'nama_prestasi' => 'required|string|max:255',
            'tingkat' => 'required|string|max:100',
            'peringkat' => 'nullable|string|max:100',
            'tanggal' => 'required|date',
            'penyelenggara' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        PrestasiNonAkademik::create($data + [
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'guru_id' => Auth::id(),
        ]);

        return redirect()->route('guru.portal')->with('success', 'Prestasi non-akademik berhasil ditambahkan.');
    }

    public function update(Request $request, PrestasiNonAkademik $prestasi)
    {
        abort_unless(in_array($prestasi->siswa_id, $this->siswaWaliIds()->all()), 403, 'Anda tidak memiliki akses ke data ini.');

        $data = $request->validate([
            'nama_prestasi' => 'required|string|max:255',
            'tingkat' => 'required|string|max:100',
            'peringkat' => 'nullable|string|max:100',
            'tanggal' => 'required|date',
            'penyelenggara' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $prestasi->update($data);

        return redirect()->route('guru.portal')->with('success', 'Prestasi non-akademik berhasil diperbarui.');
    }

    public function destroy(PrestasiNonAkademik $prestasi)
    {
        abort_unless(in_array($prestasi->siswa_id, $this->siswaWaliIds()->all()), 403, 'Anda tidak memiliki akses ke data ini.');

        $prestasi->delete();

        return redirect()->route('guru.portal')->with('success', 'Prestasi non-akademik berhasil dihapus.');
    }
}
