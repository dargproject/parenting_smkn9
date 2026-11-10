@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Penempatan PKL</h1><p class="text-sm text-slate-500 dark:text-slate-400">Kelola data IDUKA, CP industri, dan pembimbing sekolah untuk siswa PKL. Periode PKL diisi sendiri oleh pembimbing yang ditugaskan.</p></div>
        <a href="{{ route('admin.penempatan-pkl.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 whitespace-nowrap">Tambah</a>
    </div>
    @include('admin.partials.flash')

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
        <h3 class="font-bold text-sm mb-2">Import / Export Excel</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Kolom: <code>nama_siswa, kelas, nama_iduka, alamat, nama_cp_industri, cp_industri, pembimbing_sekolah</code>. Nama siswa &amp; kelas harus persis sama dengan data Siswa; nama pembimbing harus persis sama dengan guru berrole "Pembimbing PKL". Data yang diimport otomatis masuk ke tahun ajaran aktif; siswa yang sudah punya data akan diperbarui, bukan diduplikasi.</p>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.penempatan-pkl.template') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400"><i class="fa-solid fa-download mr-1"></i>Unduh Template</a>
            <a href="{{ route('admin.penempatan-pkl.export') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400"><i class="fa-solid fa-file-export mr-1"></i>Export Data</a>
            <form method="POST" action="{{ route('admin.penempatan-pkl.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-xs">
                <button class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-500 whitespace-nowrap">Upload &amp; Import</button>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                <tr>
                    <th class="px-5 py-3">Nama Siswa</th>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3">Nama IDUKA</th>
                    <th class="px-5 py-3">Pembimbing Sekolah</th>
                    <th class="px-5 py-3">Periode</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($items as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $item->siswa->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->nama_iduka }}</td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $item->guruPembimbing->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
                            @if($item->tanggal_mulai || $item->tanggal_selesai)
                                {{ $item->tanggal_mulai?->translatedFormat('d M Y') ?? '?' }} &ndash; {{ $item->tanggal_selesai?->translatedFormat('d M Y') ?? '?' }}
                            @else
                                <span class="italic">Belum diisi pembimbing</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.penempatan-pkl.edit', $item) }}" class="text-blue-500 hover:text-blue-600">Edit</a>
                                <form method="POST" action="{{ route('admin.penempatan-pkl.destroy', $item) }}" onsubmit="return confirm('Hapus penempatan PKL ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500 hover:text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada data penempatan PKL.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
