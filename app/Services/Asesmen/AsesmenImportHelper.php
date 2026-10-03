<?php

namespace App\Services\Asesmen;

use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Dipakai bersama oleh semua instrumen asesmen BK (AKPD, Gaya Belajar, DCM, Sosiometri, Tes Bakat Minat)
 * untuk mencocokkan baris import ke data SIKNINE yang sudah ada. Berbeda dari aplikasi BK sumber: di sini
 * TIDAK PERNAH membuat siswa baru secara otomatis -- baris yang tidak cocok dilaporkan sebagai error.
 */
class AsesmenImportHelper
{
    public static function resolveSiswa(?string $nis, ?string $nama, ?string $kelas): ?Siswa
    {
        $nis = trim((string) $nis);
        if ($nis !== '') {
            $siswa = Siswa::where('nis', $nis)->first();
            if ($siswa) {
                return $siswa;
            }
        }

        $nama = trim((string) $nama);
        $kelas = trim((string) $kelas);
        if ($nama === '' || $kelas === '') {
            return null;
        }

        $siswa = Siswa::query()
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->whereHas('kelas', fn ($q) => $q->whereRaw('LOWER(nama_kelas) = ?', [mb_strtolower($kelas)]))
            ->first();

        if ($siswa) {
            return $siswa;
        }

        return Siswa::query()
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->whereHas('kelas', fn ($q) => $q->whereRaw('LOWER(nama_kelas) LIKE ?', ['%'.mb_strtolower($kelas).'%']))
            ->first();
    }

    public static function resolveTahunAjaranId(mixed $label): ?int
    {
        $label = trim((string) ($label ?? ''));

        if ($label !== '') {
            $found = TahunAjaran::whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($label).'%'])->value('id');
            if ($found) {
                return $found;
            }
        }

        return TahunAjaran::where('is_active', true)->value('id');
    }

    public static function parseTanggal(mixed $value, ?string $fallback = null): string
    {
        if ($value === null || trim((string) $value) === '') {
            return $fallback ?? now()->format('Y-m-d');
        }

        $value = trim((string) $value);

        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'Y-m-d H:i:s', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // coba format berikutnya
            }
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                // lanjut ke Carbon::parse
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $fallback ?? now()->format('Y-m-d');
        }
    }
}
