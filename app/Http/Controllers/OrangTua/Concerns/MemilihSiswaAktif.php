<?php

namespace App\Http\Controllers\OrangTua\Concerns;

use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;

/**
 * Satu akun ortu bisa tertaut ke beberapa anak (kembar/kakak-adik) -- trait ini satu tempat
 * untuk menentukan anak mana yang sedang dilihat, dipakai controller mana pun yang perlu tahu
 * "siswa yang sedang dipantau", bukan asumsi satu akun satu siswa seperti dulu.
 */
trait MemilihSiswaAktif
{
    /**
     * Anak-anak yang tautannya sudah disetujui -- yang tidak disetujui tidak ikut dipilih
     * ataupun ditampilkan di pemilih anak.
     */
    protected function daftarAnak(OrangTua $orangTua): Collection
    {
        return $orangTua->siswaAktif()->get();
    }

    /**
     * Anak yang sedang dilihat: ikuti pilihan tersimpan di sesi kalau masih valid (anak itu
     * memang tertaut & disetujui ke akun ini), kalau tidak ada/sudah tidak valid, pakai yang
     * pertama secara alfabetis. Null kalau akun ini belum punya anak yang disetujui sama sekali.
     */
    protected function siswaAktif(OrangTua $orangTua): ?Siswa
    {
        $anak = $this->daftarAnak($orangTua);

        if ($anak->isEmpty()) {
            return null;
        }

        $dipilihId = session('ortu_siswa_id');

        return ($dipilihId ? $anak->firstWhere('id', $dipilihId) : null) ?? $anak->first();
    }
}
