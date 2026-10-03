<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeminatanRequest;
use App\Imports\PeminatanImport;
use App\Models\Kelas;
use App\Models\Peminatan;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PeminatanController extends Controller
{
    public function index(Request $request)
    {
        $records = Peminatan::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.asesmen.peminatan.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StorePeminatanRequest $request)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);
        $top3 = Peminatan::dominantIntelligencesFrom($jawaban);

        $peminatan = Peminatan::create([
            'siswa_id' => $data['siswa_id'],
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'pilihan1' => ($data['pilihan1'] ?? '') !== '' ? $data['pilihan1'] : ($top3[0] ?: null),
            'pilihan2' => ($data['pilihan2'] ?? '') !== '' ? $data['pilihan2'] : ($top3[1] ?: null),
            'pilihan3' => ($data['pilihan3'] ?? '') !== '' ? $data['pilihan3'] : ($top3[2] ?: null),
            'hasil' => $top3[0] ?: null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()->route('guru.bk.asesmen.peminatan.show', $peminatan)->with('success', 'Data Tes Bakat Minat berhasil ditambahkan.');
    }

    public function show(Peminatan $peminatan)
    {
        $peminatan->load('siswa.kelas');

        return view('guru.bk.asesmen.peminatan.show', ['peminatan' => $peminatan]);
    }

    public function update(StorePeminatanRequest $request, Peminatan $peminatan)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);
        $top3 = Peminatan::dominantIntelligencesFrom($jawaban);

        $peminatan->update([
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'pilihan1' => ($data['pilihan1'] ?? '') !== '' ? $data['pilihan1'] : ($top3[0] ?: null),
            'pilihan2' => ($data['pilihan2'] ?? '') !== '' ? $data['pilihan2'] : ($top3[1] ?: null),
            'pilihan3' => ($data['pilihan3'] ?? '') !== '' ? $data['pilihan3'] : ($top3[2] ?: null),
            'hasil' => $top3[0] ?: null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()->route('guru.bk.asesmen.peminatan.show', $peminatan)->with('success', 'Data Tes Bakat Minat berhasil diperbarui.');
    }

    public function destroy(Peminatan $peminatan)
    {
        $peminatan->delete();

        return redirect()->route('guru.bk.asesmen.peminatan.index')->with('success', 'Data Tes Bakat Minat berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            $sample = ['01/08/2026 23:44:58', 'Test', '2026', 'X TKJ 1'];
            foreach (Peminatan::SECTIONS as $section) {
                $first = array_key_first(Peminatan::QUESTION_GROUPS[$section]);
                $sample[] = $first.' '.Peminatan::QUESTION_GROUPS[$section][$first];
            }
            $sample[] = '';
            fputcsv($out, $sample);
            fclose($out);
        }, 'template-tes-bakat-minat.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new PeminatanImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('error', "{$import->berhasil} data berhasil diimport. ".count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return back()->with('success', "{$import->berhasil} data Tes Bakat Minat berhasil diimport.");
    }

    public function export(Request $request)
    {
        $records = Peminatan::with(['siswa.kelas', 'tahunAjaran'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            foreach ($records as $p) {
                $row = [
                    $p->tanggal?->format('Y-m-d') ?? '',
                    $p->siswa->nama ?? '',
                    $p->tahunAjaran->nama ?? '',
                    $p->siswa->kelas->nama_kelas ?? '',
                ];

                foreach (Peminatan::SECTIONS as $section) {
                    $codes = collect($p->jawaban[$section] ?? []);
                    $row[] = $codes->map(fn ($kode) => $kode.' '.(Peminatan::QUESTION_GROUPS[$section][$kode] ?? ''))->implode(', ');
                }

                $row[] = $p->hasil ?? '';

                fputcsv($out, $row);
            }

            fclose($out);
        }, 'data-tes-bakat-minat-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function normalizeJawaban(array $jawaban): array
    {
        $result = [];
        foreach (Peminatan::SECTIONS as $section) {
            $validCodes = array_keys(Peminatan::QUESTION_GROUPS[$section] ?? []);
            $result[$section] = array_values(array_intersect($jawaban[$section] ?? [], $validCodes));
        }

        return $result;
    }

    private function headers(): array
    {
        $headers = ['Timestamp', 'Nama Siswa', 'Tahun Pelajaran', 'Kelas'];

        foreach (Peminatan::SECTIONS as $section) {
            $headers[] = $section;
        }

        $headers[] = 'Hasil';

        return $headers;
    }
}
