@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Katalog Pelanggaran</h1><p class="text-sm text-slate-500 dark:text-slate-400">Kelola jenis dan bobot pelanggaran tata tertib.</p></div>
        <a href="{{ route('admin.master-pelanggaran.create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah</a>
    </div>
    @include('admin.partials.flash')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                <tr>
                    <th class="px-5 py-3">Nama Pelanggaran</th>
                    <th class="px-5 py-3">Pasal</th>
                    <th class="px-5 py-3">Jenis</th>
                    <th class="px-5 py-3 text-center">Poin</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($items as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $item->nama_pelanggaran }}</td>
                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $item->pasal->nama ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->jenisPelanggaran->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-center font-semibold">{{ $item->jenisPelanggaran->poin ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-500' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.master-pelanggaran.edit', $item) }}" class="text-blue-500 hover:text-blue-600">Edit</a>
                                <form method="POST" action="{{ route('admin.master-pelanggaran.destroy', $item) }}" onsubmit="return confirm('Hapus jenis pelanggaran ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500 hover:text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada jenis pelanggaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
