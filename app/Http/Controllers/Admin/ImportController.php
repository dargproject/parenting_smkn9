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
        Excel::import(new SiswasImport, $request->file('file'));

        return back()->with('success', 'Data siswa berhasil diimport.');
    }

    public function teachers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        Excel::import(new GurusImport, $request->file('file'));

        return back()->with('success', 'Data guru berhasil diimport.');
    }
}
