<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Models\KasusBk;
use App\Models\RujukanBk;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RujukanBkController extends Controller
{
    public function index()
    {
        return view('guru.bk.rujukan.index', [
            'menunggu' => RujukanBk::with(['siswa.kelas', 'dirujukOleh'])->where('status', 'menunggu')->latest()->get(),
            'riwayat' => RujukanBk::with(['siswa.kelas', 'dirujukOleh', 'ditanganiOleh'])->where('status', '!=', 'menunggu')->latest()->limit(20)->get(),
        ]);
    }

    public function accept(RujukanBk $rujukan)
    {
        abort_unless($rujukan->status === 'menunggu', 422, 'Rujukan ini sudah ditangani.');

        DB::transaction(function () use ($rujukan) {
            $kasusBk = KasusBk::create([
                'siswa_id' => $rujukan->siswa_id,
                'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
                'judul' => 'Rujukan Wali Kelas: '.$rujukan->kategori,
                'kategori' => 'Rujukan Wali Kelas',
                'deskripsi' => $rujukan->alasan,
                'status' => 'antrean',
                'prioritas' => 'sedang',
                'tanggal_mulai' => now()->toDateString(),
                'konselor_id' => Auth::id(),
            ]);

            $rujukan->update([
                'status' => 'diterima',
                'kasus_bk_id' => $kasusBk->id,
                'ditangani_oleh' => Auth::id(),
            ]);
        });

        return redirect()->route('guru.bk.rujukan.index')->with('success', 'Rujukan diterima dan kasus BK baru berhasil dibuat.');
    }

    public function reject(Request $request, RujukanBk $rujukan)
    {
        abort_unless($rujukan->status === 'menunggu', 422, 'Rujukan ini sudah ditangani.');

        $data = $request->validate([
            'catatan_penolakan' => 'nullable|string|max:1000',
        ]);

        $rujukan->update([
            'status' => 'ditolak',
            'catatan_penolakan' => $data['catatan_penolakan'] ?? null,
            'ditangani_oleh' => Auth::id(),
        ]);

        return redirect()->route('guru.bk.rujukan.index')->with('success', 'Rujukan ditolak.');
    }
}
