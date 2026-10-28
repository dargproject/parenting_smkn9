<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template unduhan generik untuk instrumen asesmen BK (AKPD, Gaya Belajar, DCM, Sosiometri,
 * Tes Bakat Minat) -- semuanya berbentuk header + satu baris contoh, jadi dipakai bersama
 * agar tidak ada export class yang didobel-tulis per instrumen.
 */
class AsesmenTemplateExport implements FromArray, WithHeadings
{
    public function __construct(private array $headings, private array $sampleRow) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return [$this->sampleRow];
    }
}
