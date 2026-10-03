@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h4 class="font-bold text-slate-100 m-0 text-xl">Konferensi Kasus</h4>
            <p class="text-slate-400 text-sm m-0">Riwayat konferensi kasus yang Anda catat. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <button type="button" onclick="document.getElementById('modal-konferensi-baru').style.display='flex'" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-plus mr-1"></i> Konferensi Baru</button>
    </div>

    @include('admin.partials.flash')

    <form method="GET" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa / judul kasus..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Cari</button>
            @if(request()->query())
                <a href="{{ route('guru.bk.konferensi.index') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-700/60 bg-slate-800/80">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Siswa &amp; Kasus</th>
                    <th class="px-4 py-3">Tempat</th>
                    <th class="px-4 py-3">Peserta</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($records as $record)
                    <tr>
                        <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ $record->tanggal_konferensi->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-100">{{ $record->kasusBk->siswa->nama ?? '-' }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">{{ $record->kasusBk->judul ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-slate-400">{{ $record->tempat_pertemuan ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $record->pesertas->count() }} orang</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('guru.bk.konferensi.show', $record) }}" class="text-blue-400 hover:text-blue-300 text-sm font-semibold">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">{{ request()->query() ? 'Tidak ada catatan yang cocok dengan pencarian.' : 'Belum ada konferensi kasus yang dicatat.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400 m-0">@if($records->total()) Menampilkan {{ $records->firstItem() }}–{{ $records->lastItem() }} dari {{ $records->total() }} catatan @endif</p>
        {{ $records->withQueryString()->links() }}
    </div>
</div>

@include('guru.bk.konferensi._form-modal', ['formId' => 'modal-konferensi-baru', 'kasusOptions' => $kasusOptions])
@endsection
