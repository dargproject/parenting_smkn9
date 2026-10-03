<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAkpdRequest;
use App\Imports\AkpdImport;
use App\Models\Akpd;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AkpdController extends Controller
{
    public function index(Request $request)
    {
        $records = Akpd::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.asesmen.akpd.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreAkpdRequest $request)
    {
        $data = $request->validated();

        $akpd = Akpd::create([
            'siswa_id' => $data['siswa_id'],
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'tanggal' => $data['tanggal'],
            'jawaban' => $data['jawaban'] ?? [],
        ]);

        return redirect()->route('guru.bk.asesmen.akpd.show', $akpd)->with('success', 'Data AKPD berhasil ditambahkan.');
    }

    public function show(Akpd $akpd)
    {
        $akpd->load('siswa.kelas');

        return view('guru.bk.asesmen.akpd.show', ['akpd' => $akpd]);
    }

    public function update(StoreAkpdRequest $request, Akpd $akpd)
    {
        $data = $request->validated();

        $akpd->update([
            'tanggal' => $data['tanggal'],
            'jawaban' => $data['jawaban'] ?? [],
        ]);

        return redirect()->route('guru.bk.asesmen.akpd.show', $akpd)->with('success', 'Data AKPD berhasil diperbarui.');
    }

    public function destroy(Akpd $akpd)
    {
        $akpd->delete();

        return redirect()->route('guru.bk.asesmen.akpd.index')->with('success', 'Data AKPD berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());
            $sample = ['01/08/2026 23:44:58', 'Test', '2026', 'X TKJ 1'];
            foreach (range(1, 50) as $no) {
                $sample[] = 'Ya';
            }
            fputcsv($out, $sample);
            fclose($out);
        }, 'template-akpd.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new AkpdImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('error', "{$import->berhasil} data berhasil diimport. ".count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return back()->with('success', "{$import->berhasil} data AKPD berhasil diimport.");
    }

    public function export(Request $request)
    {
        $records = Akpd::with(['siswa.kelas', 'tahunAjaran'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            foreach ($records as $akpd) {
                $row = [
                    $akpd->tanggal?->format('Y-m-d') ?? '',
                    $akpd->siswa->nama ?? '',
                    $akpd->tahunAjaran->nama ?? '',
                    $akpd->siswa->kelas->nama_kelas ?? '',
                ];
                foreach (range(1, 50) as $no) {
                    $row[] = $akpd->jawaban[$no] ?? '';
                }
                fputcsv($out, $row);
            }

            fclose($out);
        }, 'data-akpd-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function headers(): array
    {
        $headers = ['Timestamp', 'Nama Siswa', 'Tahun Pelajaran', 'Kelas'];

        foreach (range(1, 50) as $no) {
            $headers[] = $no.'. '.Akpd::QUESTIONS[$no];
        }

        return $headers;
    }
}
