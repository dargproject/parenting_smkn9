@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.asesmen.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Asesmen BK</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">AKPD <span class="text-slate-400 font-normal text-base">(Asesmen Kebutuhan Peserta Didik)</span></h4>
            <p class="text-slate-400 text-sm m-0">50 butir Ya/Tidak dalam 5 aspek: Pribadi, Sosial, Belajar, Karir, Kesimpulan. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('guru.bk.asesmen.akpd.template') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-file-arrow-down mr-1"></i> Template</a>
            <button type="button" onclick="document.getElementById('modal-import-akpd').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-file-import mr-1"></i> Import</button>
            <a href="{{ route('guru.bk.asesmen.akpd.export', request()->query()) }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-file-export mr-1"></i> Export</a>
            <button type="button" onclick="document.getElementById('modal-akpd-baru').style.display='flex'" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-plus mr-1"></i> Tambah</button>
        </div>
    </div>

    @include('admin.partials.flash')

    <form method="GET" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
            <select name="tingkat" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Tingkat</option>
                @foreach(['X', 'XI', 'XII'] as $t)
                    <option value="{{ $t }}" @selected(request('tingkat') === $t)>{{ $t }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $id => $nama)
                    <option value="{{ $id }}" @selected((string) request('kelas_id') === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>
            <select name="jurusan" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Jurusan</option>
                @foreach($jurusanOptions as $jurusan)
                    <option value="{{ $jurusan }}" @selected(request('jurusan') === $jurusan)>{{ $jurusan }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Terapkan Filter</button>
            @if(request()->query())
                <a href="{{ route('guru.bk.asesmen.akpd.index') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-700/60 bg-slate-800/80">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($records as $akpd)
                    <tr>
                        <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ $akpd->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-100">{{ $akpd->siswa->nama ?? '-' }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">NIS {{ $akpd->siswa->nis ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-slate-400">{{ $akpd->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('guru.bk.asesmen.akpd.show', $akpd) }}" class="text-blue-400 hover:text-blue-300 text-sm font-semibold">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">{{ request()->query() ? 'Tidak ada data yang cocok dengan filter.' : 'Belum ada data AKPD.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400 m-0">@if($records->total()) Menampilkan {{ $records->firstItem() }}–{{ $records->lastItem() }} dari {{ $records->total() }} data @endif</p>
        {{ $records->withQueryString()->links() }}
    </div>
</div>

{{-- Modal Import --}}
<div id="modal-import-akpd" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-md rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl">
        <form method="POST" action="{{ route('guru.bk.asesmen.akpd.import') }}" enctype="multipart/form-data" class="p-5">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">Import Data AKPD</h5>
                <button type="button" onclick="document.getElementById('modal-import-akpd').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-3 text-xs text-blue-300 mb-4">
                Format kolom: <code>Timestamp | Nama Siswa | Tahun Pelajaran | Kelas | 1. &lt;soal&gt; .. 50. &lt;soal&gt;</code>. Nilai tiap soal: Ya / Tidak. Siswa dicocokkan lewat nama + kelas; baris yang tidak cocok dilaporkan sebagai error, bukan membuat siswa baru. Download template untuk contoh format lengkap.
            </div>
            <input type="file" name="file" required accept=".csv,.xlsx,.xls" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-import-akpd').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Proses Import</button>
            </div>
        </form>
    </div>
</div>

@include('guru.bk.asesmen.akpd._form-modal', ['formId' => 'modal-akpd-baru', 'siswas' => $siswas])
@endsection
