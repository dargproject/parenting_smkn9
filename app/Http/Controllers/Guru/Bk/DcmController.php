<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDcmRequest;
use App\Imports\DcmImport;
use App\Models\Dcm;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DcmController extends Controller
{
    public function index(Request $request)
    {
        $records = Dcm::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.asesmen.dcm.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreDcmRequest $request)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);

        $dcm = new Dcm(['jawaban' => $jawaban]);

        $dcm->fill([
            'siswa_id' => $data['siswa_id'],
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'masalah_teridentifikasi' => $dcm->masalahSummary(),
            'kesimpulan' => $data['kesimpulan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ])->save();

        return redirect()->route('guru.bk.asesmen.dcm.show', $dcm)->with('success', 'Data DCM berhasil ditambahkan.');
    }

    public function show(Dcm $dcm)
    {
        $dcm->load('siswa.kelas');

        return view('guru.bk.asesmen.dcm.show', ['dcm' => $dcm]);
    }

    public function update(StoreDcmRequest $request, Dcm $dcm)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);

        $dcm->jawaban = $jawaban;

        $dcm->update([
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'masalah_teridentifikasi' => $dcm->masalahSummary(),
            'kesimpulan' => $data['kesimpulan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()->route('guru.bk.asesmen.dcm.show', $dcm)->with('success', 'Data DCM berhasil diperbarui.');
    }

    public function destroy(Dcm $dcm)
    {
        $dcm->delete();

        return redirect()->route('guru.bk.asesmen.dcm.index')->with('success', 'Data DCM berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            $sample = ['30/07/2026 10:27:39', 'Test', '2026', 'X RPL 1'];
            foreach (Dcm::SECTIONS as $letter => $title) {
                $first = array_key_first(Dcm::QUESTION_GROUPS[$letter] ?? []);
                $sample[] = $first ? $first.' '.Dcm::QUESTION_GROUPS[$letter][$first] : '';
            }
            $sample[] = '';
            $sample[] = '';
            fputcsv($out, $sample);
            fclose($out);
        }, 'template-dcm.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new DcmImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('error', "{$import->berhasil} data berhasil diimport. ".count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return back()->with('success', "{$import->berhasil} data DCM berhasil diimport.");
    }

    public function export(Request $request)
    {
        $records = Dcm::with(['siswa.kelas', 'tahunAjaran'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            foreach ($records as $dcm) {
                $row = [
                    $dcm->tanggal?->format('Y-m-d') ?? '',
                    $dcm->siswa->nama ?? '',
                    $dcm->tahunAjaran->nama ?? '',
                    $dcm->siswa->kelas->nama_kelas ?? '',
                ];

                foreach (Dcm::SECTIONS as $letter => $title) {
                    $codes = collect($dcm->jawaban[$letter] ?? []);
                    $row[] = $codes->map(fn ($kode) => $kode.' '.(Dcm::QUESTION_GROUPS[$letter][$kode] ?? ''))->implode(', ');
                }

                $row[] = $dcm->kesimpulan ?? '';
                $row[] = $dcm->catatan ?? '';

                fputcsv($out, $row);
            }

            fclose($out);
        }, 'data-dcm-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function normalizeJawaban(array $jawaban): array
    {
        $result = [];
        foreach (array_keys(Dcm::SECTIONS) as $letter) {
            $validCodes = array_keys(Dcm::QUESTION_GROUPS[$letter] ?? []);
            $result[$letter] = array_values(array_intersect($jawaban[$letter] ?? [], $validCodes));
        }

        return $result;
    }

    private function headers(): array
    {
        $headers = ['Timestamp', 'NAMA', 'TAHUN PELAJARAN', 'KELAS'];

        foreach (Dcm::SECTIONS as $letter => $title) {
            $headers[] = $letter.'. '.$title;
        }

        $headers[] = 'Kesimpulan';
        $headers[] = 'Catatan';

        return $headers;
    }
}
