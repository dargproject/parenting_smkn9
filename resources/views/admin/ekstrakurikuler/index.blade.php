@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Ekstrakurikuler</h1><p class="text-sm text-slate-500 dark:text-slate-400">Kelola daftar ekstrakurikuler dan pembinanya.</p></div>
        <a href="{{ route('admin.ekstrakurikuler.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah</a>
    </div>
    @include('admin.partials.flash')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                <tr>
                    <th class="px-5 py-3">Nama Ekstrakurikuler</th>
                    <th class="px-5 py-3">Pembina</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($items as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $item->nama_ekskul }}</td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $item->pembina->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-500' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.ekstrakurikuler.edit', $item) }}" class="text-blue-500 hover:text-blue-600">Edit</a>
                                <form method="POST" action="{{ route('admin.ekstrakurikuler.destroy', $item) }}" onsubmit="return confirm('Hapus ekstrakurikuler ini? Data peserta dan nilai yang terkait juga akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500 hover:text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada ekstrakurikuler.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
