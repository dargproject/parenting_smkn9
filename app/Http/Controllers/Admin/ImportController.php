<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GurusImport;
use App\Imports\SiswasImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index');
    }

    public function students(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $import = new SiswasImport;
        Excel::import($import, $request->file('file'));

        return back()->with('success', "Import siswa selesai: {$import->berhasil} data ditambahkan, {$import->dilewati} dilewati (NIS/NISN/NIPD sudah ada atau kelas tidak valid).");
    }

    public function teachers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $import = new GurusImport;
        Excel::import($import, $request->file('file'));

        return back()->with('success', "Import guru selesai: {$import->berhasil} data ditambahkan, {$import->dilewati} dilewati (NIP/email sudah ada).");
    }
}
