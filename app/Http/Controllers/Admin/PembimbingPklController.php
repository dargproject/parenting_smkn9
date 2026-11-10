<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Role;
use Illuminate\Http\Request;

class PembimbingPklController extends Controller
{
    /**
     * Halaman khusus untuk menetapkan role "Pembimbing PKL" ke banyak guru sekaligus lewat
     * checklist -- supaya admin tidak perlu buka Edit Guru satu-satu di menu Guru & Staf.
     */
    public function edit()
    {
        return view('admin.pembimbing-pkl.edit', [
            'gurus' => Guru::where('is_active', true)->orderBy('nama')->get(),
            'selectedIds' => old('guru_ids', Role::firstOrCreate(['name' => 'pembimbing_pkl'])->gurus->pluck('id')->all()),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'guru_ids' => ['nullable', 'array'],
            'guru_ids.*' => ['exists:gurus,id'],
        ]);

        $role = Role::firstOrCreate(['name' => 'pembimbing_pkl']);
        $role->gurus()->sync($data['guru_ids'] ?? []);

        return redirect()->route('admin.pembimbing-pkl.edit')->with('success', 'Daftar pembimbing PKL berhasil diperbarui.');
    }
}
