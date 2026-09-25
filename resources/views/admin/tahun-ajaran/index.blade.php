@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Tahun Ajaran</h1><p class="text-sm text-slate-500 dark:text-slate-400">Kelola periode akademik sekolah.</p></div>
        <a href="{{ route('admin.tahun-ajaran.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah</a>
    </div>
    @include('admin.partials.flash')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"><tr><th class="px-5 py-3">Tahun</th><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Semester</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($tahunAjarans as $item)
                    <tr><td class="px-5 py-4 font-semibold">{{ $item->nama }}</td><td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $item->kode }}</td><td class="px-5 py-4 capitalize">{{ $item->semester }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-500' }}">{{ $item->is_active ? 'Aktif' : 'Inactive' }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.tahun-ajaran.edit', $item) }}" class="text-blue-500 hover:text-blue-600">Edit</a>@if(!$item->is_active)<form method="POST" action="{{ route('admin.tahun-ajaran.activate', $item) }}">@csrf @method('PATCH')<button class="text-emerald-500 hover:text-emerald-600">Set Aktif</button></form><form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $item) }}" onsubmit="return confirm('Hapus tahun ajaran ini?')">@csrf @method('DELETE')<button class="text-rose-500 hover:text-rose-600">Hapus</button></form>@endif</div></td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada tahun ajaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tahunAjarans->links() }}
</div>
@endsection
