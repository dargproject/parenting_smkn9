@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div><h1 class="text-2xl font-bold">{{ $title }}</h1><p class="text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p></div>
        <a href="{{ $createRoute }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah</a>
    </div>
    @include('admin.partials.flash')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"><tr>@foreach($columns as $column)<th class="px-5 py-3">{{ $column['label'] }}</th>@endforeach<th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($items as $item)
                    <tr>@foreach($columns as $column)<td class="px-5 py-4 {{ $loop->first ? 'font-semibold' : 'text-slate-600 dark:text-slate-300' }}">{{ $column['key'] === 'roles.*.name' ? $item->roles->pluck('name')->map(fn ($role) => str_replace('_', ' ', $role))->implode(', ') : (data_get($item, $column['key'], '-') ?: '-') }}</td>@endforeach<td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route($resource.'.edit', $item) }}" class="text-blue-500">Edit</a>@if(in_array($resource, ['admin.guru', 'admin.siswa']))<form method="POST" action="{{ route($resource.'.reset-password', $item) }}">@csrf<button class="text-amber-500">Reset Password</button></form>@endif<form method="POST" action="{{ route($resource.'.destroy', $item) }}" onsubmit="return confirm('Hapus data ini?')">@csrf @method('DELETE')<button class="text-rose-500">Hapus</button></form></div></td></tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 1 }}" class="px-5 py-8 text-center text-slate-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
