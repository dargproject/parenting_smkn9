@extends('layouts.app')

@section('content')
<div class="admin-dashboard space-y-6">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div><h1 class="text-2xl font-bold">{{ $title }}</h1><p class="text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p></div>
        <a href="{{ $createRoute }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah</a>
    </div>
    @include('admin.partials.flash')
    @if(!empty($searchPlaceholder) || !empty($filters))
        @php
            $filters = $filters ?? [];
            $namaFilter = array_merge(['q'], array_column($filters, 'name'));
            $aktif = collect($namaFilter)->contains(fn ($k) => filled(request($k)));
        @endphp
        <form method="GET" class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 sm:flex-row sm:items-center">
            @if(!empty($searchPlaceholder))
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $searchPlaceholder }}" class="w-full rounded-lg border border-slate-300 bg-transparent py-2 pl-9 pr-3 text-sm dark:border-slate-600">
                </div>
            @endif
            @foreach($filters as $filter)
                <select name="{{ $filter['name'] }}" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ $filter['label'] }}</option>
                    @foreach($filter['options'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request($filter['name']) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @endforeach
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Cari</button>
            @if($aktif)
                <a href="{{ url()->current() }}" class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm dark:border-slate-600">Reset</a>
            @endif
        </form>
    @endif
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"><tr>@foreach($columns as $column)<th class="px-5 py-3">{{ $column['label'] }}</th>@endforeach<th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($items as $item)
                    <tr>@foreach($columns as $column)<td class="px-5 py-4 {{ $loop->first ? 'font-semibold' : 'text-slate-600 dark:text-slate-300' }}">{{ $column['key'] === 'status_aktif' ? ($item->status_aktif ? 'Aktif' : 'Nonaktif') : ($column['key'] === 'roles.*.name' ? $item->roles->pluck('name')->map(fn ($role) => str_replace('_', ' ', $role))->implode(', ') : (data_get($item, $column['key'], '-') ?: '-')) }}</td>@endforeach<td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route($resource.'.edit', $item) }}" class="text-blue-500">Edit</a>@if(in_array($resource, ['admin.guru', 'admin.siswa']))<form method="POST" action="{{ route($resource.'.reset-password', $item) }}">@csrf<button class="text-amber-500">Reset Password</button></form>@endif @if($resource === 'admin.siswa') @if($item->orangTua)<span class="text-emerald-500" title="Username akun ortu: {{ $item->orangTua->username }}">Akun Ortu &#10003;</span> <form method="POST" action="{{ route('admin.siswa.akun-ortu.destroy', $item) }}" onsubmit="return confirm('Hapus akun orang tua ini? Data nilai, presensi, dan pesan siswa TIDAK akan ikut terhapus.')">@csrf @method('DELETE')<button class="text-rose-400" style="font-size:11px;">(Hapus Akun)</button></form>@elseif($item->no_hp_ortu)<form method="POST" action="{{ route('admin.siswa.akun-ortu.store', $item) }}">@csrf<button class="text-emerald-600">Buat Akun Ortu</button></form>@else<span class="text-slate-400" title="Isi No. HP Orang Tua dahulu">Akun Ortu -</span>@endif @endif <form method="POST" action="{{ route($resource.'.destroy', $item) }}" onsubmit="return confirm('Hapus data ini?')">@csrf @method('DELETE')<button class="text-rose-500">Hapus</button></form></div></td></tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 1 }}" class="px-5 py-8 text-center text-slate-500">{{ request()->query() ? 'Tidak ada data yang cocok dengan pencarian/filter.' : 'Belum ada data.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">@if($items->total()) Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }} data @endif</p>
        <div>{{ $items->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
