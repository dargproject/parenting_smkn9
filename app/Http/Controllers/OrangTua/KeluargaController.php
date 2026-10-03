<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDataKeluargaSiswaRequest;
use App\Models\DataKeluargaSiswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class KeluargaController extends Controller
{
    public function edit()
    {
        $siswa = Auth::guard('orangtua')->user()->siswa;
        $siswa->load('dataKeluarga');

        return view('ortu.keluarga', ['siswa' => $siswa, 'keluarga' => $siswa->dataKeluarga]);
    }

    public function update(StoreDataKeluargaSiswaRequest $request)
    {
        $siswa = Auth::guard('orangtua')->user()->siswa;

        DataKeluargaSiswa::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $request->validated() + [
                'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
                'punya_kamar_sendiri' => $request->boolean('punya_kamar_sendiri'),
            ]
        );

        return redirect()->route('ortu.keluarga.edit')->with('success', 'Data keluarga berhasil disimpan. Terima kasih sudah melengkapi data.');
    }
}
