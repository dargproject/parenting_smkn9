<?php

namespace App\Http\Controllers\Guru\Bk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSosiometriRequest;
use App\Imports\SosiometriImport;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Sosiometri;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SosiometriController extends Controller
{
    public function index(Request $request)
    {
        $records = Sosiometri::with(['siswa.kelas'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('guru.bk.asesmen.sosiometri.index', [
            'records' => $records,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
            'kelasOptions' => Kelas::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'jurusanOptions' => Kelas::whereNotNull('jurusan')->orderBy('jurusan')->distinct()->pluck('jurusan', 'jurusan'),
        ]);
    }

    public function store(StoreSosiometriRequest $request)
    {
        $data = $request->validated();

        $sosiometri = DB::transaction(function () use ($data) {
            $sosiometri = Sosiometri::create([
                'siswa_id' => $data['siswa_id'],
                'tahun_ajaran_id' => TahunAjaran::where('is_active', true)->value('id'),
                'tanggal' => $data['tanggal'],
                'jumlah_pilihan' => 3,
            ]);

            $this->syncRespons($sosiometri, $data['pilihan'] ?? []);

            return $sosiometri;
        });

        return redirect()->route('guru.bk.asesmen.sosiometri.show', $sosiometri)->with('success', 'Data Sosiometri berhasil ditambahkan.');
    }

    public function show(Sosiometri $sosiometri)
    {
        $sosiometri->load(['siswa.kelas', 'respons.siswaDipilih']);

        return view('guru.bk.asesmen.sosiometri.show', [
            'sosiometri' => $sosiometri,
            'siswas' => Siswa::with('kelas')->orderBy('nama')->get(),
        ]);
    }

    public function update(StoreSosiometriRequest $request, Sosiometri $sosiometri)
    {
        $data = $request->validated();

        DB::transaction(function () use ($sosiometri, $data) {
            $sosiometri->update(['tanggal' => $data['tanggal']]);
            $this->syncRespons($sosiometri, $data['pilihan'] ?? []);
        });

        return redirect()->route('guru.bk.asesmen.sosiometri.show', $sosiometri)->with('success', 'Data Sosiometri berhasil diperbarui.');
    }

    public function destroy(Sosiometri $sosiometri)
    {
        $sosiometri->delete();

        return redirect()->route('guru.bk.asesmen.sosiometri.index')->with('success', 'Data Sosiometri berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());
            $sample = ['01/08/2026 23:44:58', 'AHMAD FAUZI', 'XII RPL'];
            foreach (Sosiometri::PERTANYAAN as $key => $pertanyaan) {
                $sample[] = $key === 'Q1' ? 'Ahmad, Budi, Charlie' : '';
            }
            fputcsv($out, $sample);
            fclose($out);
        }, 'template-sosiometri.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new SosiometriImport;
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return back()->with('error', "{$import->berhasil} data berhasil diimport. ".count($import->errors).' baris gagal: '.implode(' | ', array_slice($import->errors, 0, 5)));
        }

        return back()->with('success', "{$import->berhasil} data Sosiometri berhasil diimport.");
    }

    public function export(Request $request)
    {
        $records = Sosiometri::with(['siswa.kelas', 'respons.siswaDipilih'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('nama', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('tingkat', $request->tingkat)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('siswa', fn ($qs) => $qs->where('kelas_id', $request->kelas_id)))
            ->when($request->filled('jurusan'), fn ($q) => $q->whereHas('siswa.kelas', fn ($qk) => $qk->where('jurusan', $request->jurusan)))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headers());

            foreach ($records as $s) {
                $row = [$s->tanggal?->format('Y-m-d') ?? '', $s->siswa->nama ?? '', $s->siswa->kelas->nama_kelas ?? ''];

                foreach (Sosiometri::PERTANYAAN as $key => $pertanyaan) {
                    $names = $s->respons->where('pertanyaan', $key)->sortBy('urutan')
                        ->map(fn ($r) => $r->siswaDipilih->nama ?? $r->nama_dipilih ?? '')->filter()->implode(', ');
                    $row[] = $names;
                }

                fputcsv($out, $row);
            }

            fclose($out);
        }, 'data-sosiometri-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function syncRespons(Sosiometri $sosiometri, array $pilihan): void
    {
        $sosiometri->respons()->delete();

        foreach (Sosiometri::PERTANYAAN as $key => $pertanyaan) {
            $ids = array_values(array_filter($pilihan[$key] ?? []));

            foreach (array_slice($ids, 0, 3) as $urutan => $siswaId) {
                $sosiometri->respons()->create([
                    'siswa_dipilih_id' => $siswaId,
                    'urutan' => $urutan + 1,
                    'pertanyaan' => $key,
                ]);
            }
        }
    }

    private function headers(): array
    {
        $headers = ['Timestamp', 'Nama Lengkap', 'Kelas'];

        foreach (Sosiometri::PERTANYAAN as $key => $pertanyaan) {
            $num = preg_replace('/[^0-9]/', '', $key);
            $headers[] = $num.'. '.$pertanyaan;
        }

        return $headers;
    }
}
