<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;

trait KompresFoto
{
    /**
     * Pengaman di server kalau kompresi di browser gagal/dilewati (browser lama,
     * JS nonaktif) -- foto tetap diperkecil dimensinya supaya tidak boros storage.
     */
    private function kecilkanFotoJikaBesar(string $path, int $maxSisi = 1600): void
    {
        $fullPath = Storage::disk('public')->path($path);
        $ukuran = @getimagesize($fullPath);

        if (! $ukuran) {
            return;
        }

        [$lebar, $tinggi] = $ukuran;

        if ($lebar <= $maxSisi && $tinggi <= $maxSisi) {
            return;
        }

        $sumber = @imagecreatefromstring(file_get_contents($fullPath));
        if (! $sumber) {
            return;
        }

        $skala = $maxSisi / max($lebar, $tinggi);
        $lebarBaru = (int) round($lebar * $skala);
        $tinggiBaru = (int) round($tinggi * $skala);

        $tujuan = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
        imagejpeg($tujuan, $fullPath, 75);

        imagedestroy($sumber);
        imagedestroy($tujuan);
    }
}
