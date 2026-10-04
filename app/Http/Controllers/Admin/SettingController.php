<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'settings' => Setting::pluck('value', 'key'),
            'tahunAjarans' => TahunAjaran::orderByDesc('is_active')->orderByDesc('kode')->get(),
        ]);
    }

    public function update(UpdateSettingRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo_sekolah')) {
            $oldLogo = setting('logo_sekolah');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $data['logo_sekolah'] = $request->file('logo_sekolah')->store('settings', 'public');
        } else {
            unset($data['logo_sekolah']);
        }

        if ($request->hasFile('gambar_login')) {
            $oldGambar = setting('gambar_login');
            if ($oldGambar) {
                Storage::disk('public')->delete($oldGambar);
            }
            $data['gambar_login'] = $request->file('gambar_login')->store('settings', 'public');
        } else {
            unset($data['gambar_login']);
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }
}
