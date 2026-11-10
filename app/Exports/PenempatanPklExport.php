<?php

namespace App\Exports;

use App\Models\PenempatanPkl;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenempatanPklExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return PenempatanPkl::with(['siswa.kelas', 'guruPembimbing'])->latest('id')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Siswa', 'Kelas', 'Nama IDUKA', 'Alamat', 'Nama CP Industri', 'CP Industri',
            'Pembimbing Sekolah', 'Tanggal Mulai', 'Tanggal Selesai', 'Catatan',
        ];
    }

    public function map($item): array
    {
        return [
            $item->siswa->nama ?? '-',
            $item->siswa->kelas->nama_kelas ?? '-',
            $item->nama_iduka,
            $item->alamat,
            $item->nama_cp_industri,
            $item->no_cp_industri,
            $item->guruPembimbing->nama ?? '-',
            $item->tanggal_mulai?->format('Y-m-d'),
            $item->tanggal_selesai?->format('Y-m-d'),
            $item->catatan,
        ];
    }
}
