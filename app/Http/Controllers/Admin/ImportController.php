<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GurusImport;
use App\Imports\SiswasImport;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index', ['daftarKelas' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->pluck('nama_kelas')]);
    }

    public function students(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $import = new SiswasImport;
        Excel::import($import, $request->file('file'));

        return back()->with('success', "Import siswa selesai: {$import->berhasil} data ditambahkan, {$import->dilewati} dilewati (NIS/NISN/NIPD sudah ada atau nama kelas tidak cocok dengan data Kelas yang ada).");
    }

    public function templateSiswa()
    {
        $kelasContoh = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->value('nama_kelas') ?? 'X TKJ 1';

        return response()->streamDownload(function () use ($kelasContoh) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['nis', 'nisn', 'nipd', 'nama', 'kelas', 'jenis_kelamin', 'tanggal_lahir', 'no_hp_ortu']);
            fputcsv($out, ['1001', '0051234567', '', 'Contoh Nama Siswa', $kelasContoh, 'L', '2009-05-14', '081234567890']);
            fclose($out);
        }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv']);
    }

    public function teachers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $import = new GurusImport;
        Excel::import($import, $request->file('file'));

        return back()->with('success', "Import guru selesai: {$import->berhasil} data ditambahkan, {$import->dilewati} dilewati (NIP/email sudah ada).");
    }
}
