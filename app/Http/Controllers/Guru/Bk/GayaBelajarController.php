<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGayaBelajarRequest;
use App\Imports\GayaBelajarImport;
use App\Models\GayaBelajar;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GayaBelajarController extends Controller
{
    public function index(Request $request)
    {
        $records = GayaBelajar::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.asesmen.gaya-belajar.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreGayaBelajarRequest $request)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);
        $scores = GayaBelajar::scoresFromJawaban($jawaban);

        $gayaBelajar = GayaBelajar::create([
            'siswa_id' => $data['siswa_id'],
            'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'visual' => $scores['visual'],
            'auditori' => $scores['auditori'],
            'kinestetik' => $scores['kinestetik'],
            'hasil' => $data['hasil'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'faktor_penghambat' => $data['faktor_penghambat'] ?? null,
            'faktor_pendukung' => $data['faktor_pendukung'] ?? null,
            'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
        ]);

        return redirect()->route('guru.bk.asesmen.gaya-belajar.show', $gayaBelajar)->with('success', 'Data Gaya Belajar berhasil ditambahkan.');
    }

    public function show(GayaBelajar $gayaBelajar)
    {
        $gayaBelajar->load('siswa.kelas');

        return view('guru.bk.asesmen.gaya-belajar.show', ['gayaBelajar' => $gayaBelajar]);
    }

    public function update(StoreGayaBelajarRequest $request, GayaBelajar $gayaBelajar)
    {
        $data = $request->validated();
        $jawaban = $this->normalizeJawaban($data['jawaban'] ?? []);
        $scores = GayaBelajar::scoresFromJawaban($jawaban);

        $gayaBelajar->update([
            'tanggal' => $data['tanggal'],
            'jawaban' => $jawaban,
            'visual' => $scores['visual'],
            'auditori' => $scores['auditori'],
            'kinestetik' => $scores['kinestetik'],
            'hasil' => $data['hasil'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'faktor_penghambat' => $data['faktor_penghambat'] ?? null,
            'faktor_pendukung' => $data['faktor_pendukung'] ?? null,
            'tampilkan_ke_ortu' => $request->boolean('tampilkan_ke_ortu'),
        ]);

        return redirect()->route('guru.bk.asesmen.gaya-belajar.show', $gayaBelajar)->with('success', 'Data Gaya Belajar berhasil diperbarui.');
    }

    public function destroy(GayaBelajar $gayaBelajar)
    {
        $gayaBelajar->delete();

        return redirect()->route('guru.bk.asesmen.gaya-belajar.index')->with('success', 'Data Gaya Belajar berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());
            $sample = ['01/08/2026 23:44:58', 'Test', '2026', 'X TKJ 1', 'Visual', '', ''];
            foreach (GayaBelajar::flatQuestions() as $q) {
                $sample[] = 'Ya';
            }
            fputcsv($out, $sample);
            fclose($out);
        }, 'template-gaya-belajar.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new GayaBelajarImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('error', "{$import->berhasil} data berhasil diimport. ".count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return back()->with('success', "{$import->berhasil} data Gaya Belajar berhasil diimport.");
    }

    public function export(Request $request)
    {
        $records = GayaBelajar::with(['siswa.kelas', 'tahunAjaran'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            foreach ($records as $g) {
                $row = [
                    $g->tanggal?->format('Y-m-d') ?? '',
                    $g->siswa->nama ?? '',
                    $g->tahunAjaran->nama ?? '',
                    $g->siswa->kelas->nama_kelas ?? '',
                    $g->hasil ?? '',
                    $g->faktor_penghambat ?? '',
                    $g->faktor_pendukung ?? '',
                ];
                $jawaban = $g->jawaban ?? [];
                foreach (GayaBelajar::flatQuestions() as $q) {
                    $checked = collect($jawaban[$q['group']] ?? [])->contains($q['index']);
                    $row[] = $checked ? 'Ya' : 'Tidak';
                }
                fputcsv($out, $row);
            }

            fclose($out);
        }, 'data-gaya-belajar-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function normalizeJawaban(array $jawaban): array
    {
        return [
            'Visual' => array_values(array_map('intval', $jawaban['Visual'] ?? [])),
            'Auditorial' => array_values(array_map('intval', $jawaban['Auditorial'] ?? [])),
            'Kinestetik' => array_values(array_map('intval', $jawaban['Kinestetik'] ?? [])),
        ];
    }

    private function headers(): array
    {
        $headers = ['Timestamp', 'Nama Siswa', 'Tahun Pelajaran', 'Kelas', 'Hasil', 'Faktor Penghambat', 'Faktor Pendukung'];

        foreach (GayaBelajar::flatQuestions() as $no => $q) {
            $headers[] = $no.'. '.$q['text'];
        }

        return $headers;
    }
}
