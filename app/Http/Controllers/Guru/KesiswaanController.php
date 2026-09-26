<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Role;
use Illuminate\Http\Request;

class KesiswaanController extends Controller
{
    /** Menetapkan siapa saja yang berperan sebagai petugas tatib (hanya role `tatib` yang diubah). */
    public function updatePetugasTatib(Request $request)
    {
        $data = $request->validate([
            'guru_ids' => 'nullable|array',
            'guru_ids.*' => 'exists:gurus,id',
        ]);

        $roleId = Role::firstOrCreate(['name' => 'tatib'])->id;

        // Admin dan kepala sekolah tidak ditampilkan di daftar, jadi tidak boleh ikut diubah.
        $terlarang = Guru::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'kepsek']))->pluck('id');
        $dipilih = collect($data['guru_ids'] ?? [])->map(fn ($id) => (int) $id)->diff($terlarang);

        $petugasSekarang = Guru::whereHas('roles', fn ($q) => $q->where('name', 'tatib'))->pluck('id');

        Role::find($roleId)->gurus()->detach($petugasSekarang->diff($dipilih)->all());
        Role::find($roleId)->gurus()->syncWithoutDetaching($dipilih->all());

        return redirect()->route('guru.portal')->with('success', 'Petugas tatib diperbarui: '.$dipilih->count().' guru bertugas.');
    }
}