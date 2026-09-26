<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AsesmenBk;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BkController extends Controller
{
    public function storeAsesmen(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkat_stres' => 'required|integer|min:1|max:10',
            'minat_karir' => 'nullable|string|max:100',
            'catatan' => 'nullable|string|max:2000',
        ]);

        $tahunAjaranId = TahunAjaran::where('is_active', true)->value('id');
        abort_unless($tahunAjaranId, 422, 'Belum ada tahun ajaran aktif.');

        AsesmenBk::updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'tahun_ajaran_id' => $tahunAjaranId],
            ['tingkat_stres' => $data['tingkat_stres'], 'minat_karir' => $data['minat_karir'] ?? null, 'catatan' => $data['catatan'] ?? null, 'konselor_id' => Auth::id()]
        );

        return redirect()->route('guru.portal')->with('success', 'Asesmen BK berhasil disimpan.');
    }
}