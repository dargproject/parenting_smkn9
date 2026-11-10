<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AsesmenTemplateExport;
use App\Exports\PenempatanPklExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePenempatanPklRequest;
use App\Imports\PenempatanPklImport;
use App\Models\Guru;
use App\Models\PenempatanPkl;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenempatanPklController extends Controller
{
    public function index()
    {
        return view('admin.penempatan-pkl.index', [
            'items' => PenempatanPkl::with(['siswa.kelas', 'tahunAjaran', 'guruPembimbing'])
                ->latest('tanggal_mulai')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.penempatan-pkl.create', [
            'item' => new PenempatanPkl,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'guruPembimbingOptions' => $this->guruPembimbingOptions(),
            'tahunAjaranOptions' => TahunAjaran::orderByDesc('id')->pluck('nama', 'id'),
            'tahunAjaranAktifId' => TahunAjaran::where('is_active', true)->value('id'),
        ]);
    }

    public function store(StorePenempatanPklRequest $request)
    {
        PenempatanPkl::create($request->validated());

        return redirect()->route('admin.penempatan-pkl.index')->with('success', 'Penempatan PKL berhasil ditambahkan.');
    }

    public function edit(PenempatanPkl $penempatanPkl)
    {
        return view('admin.penempatan-pkl.edit', [
            'item' => $penempatanPkl,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'guruPembimbingOptions' => $this->guruPembimbingOptions(),
            'tahunAjaranOptions' => TahunAjaran::orderByDesc('id')->pluck('nama', 'id'),
            'tahunAjaranAktifId' => TahunAjaran::where('is_active', true)->value('id'),
        ]);
    }

    public function update(StorePenempatanPklRequest $request, PenempatanPkl $penempatanPkl)
    {
        $penempatanPkl->update($request->validated());

        return redirect()->route('admin.penempatan-pkl.index')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function destroy(PenempatanPkl $penempatanPkl)
    {
        $penempatanPkl->delete();

        return back()->with('success', 'Penempatan PKL berhasil dihapus.');
    }

    public function template()
    {
        $headings = ['Nama Siswa', 'Kelas', 'Nama IDUKA', 'Alamat', 'Nama CP Industri', 'CP Industri', 'Pembimbing Sekolah'];
        $sample = ['Muhammad Adrian Fahri', 'XI ANM 2', 'Alfa Percetakan', 'Bonangan, Pakis, Kabupaten Malang', 'Moh. Fadillah', '08213821501', 'Nadzif Ulfiah, S.Pd'];

        return Excel::download(new AsesmenTemplateExport($headings, $sample), 'template-penempatan-pkl.xlsx');
    }

    public function export()
    {
        return Excel::download(new PenempatanPklExport, 'data-penempatan-pkl-'.now()->format('Ymd-His').'.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new PenempatanPklImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()
                ->with('error', "{$import->berhasil} ditambahkan, {$import->diperbarui} diperbarui, ".count($import->errors).' baris gagal. Rincian baris yang gagal:')
                ->with('import_errors', $import->errors);
        }

        return back()->with('success', "Import selesai: {$import->berhasil} data baru ditambahkan, {$import->diperbarui} data diperbarui.");
    }

    private function guruPembimbingOptions()
    {
        return Guru::whereHas('roles', fn ($q) => $q->where('name', 'pembimbing_pkl'))
            ->orderBy('nama')
            ->pluck('nama', 'id');
    }
}
