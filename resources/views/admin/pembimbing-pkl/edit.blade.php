@extends('layouts.app')

@section('content')
<div class="admin-dashboard mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Pembimbing PKL</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Centang guru yang menjadi pembimbing PKL -- guru yang dicentang akan muncul di pilihan "Pembimbing Sekolah" saat admin menambah Penempatan PKL, dan dapat mengisi periode PKL siswa bimbingannya lewat menunya sendiri.</p>
    </div>
    @include('admin.partials.flash')

    <form method="POST" action="{{ route('admin.pembimbing-pkl.update') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 md:p-6"
        x-data="{ query: '' }">
        @csrf
        @method('PUT')

        <input type="text" x-model="query" placeholder="Cari nama guru..." autocomplete="off"
            class="mb-4 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500">

        @if($gurus->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada data guru aktif.</p>
        @else
            <div class="max-h-96 space-y-1.5 overflow-y-auto rounded-lg border border-slate-300 p-3 dark:border-slate-600">
                @foreach($gurus as $guru)
                    <label x-show="!query.trim() || '{{ \Illuminate\Support\Js::from(strtolower($guru->nama)) }}'.includes(query.toLowerCase())" class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="guru_ids[]" value="{{ $guru->id }}" @checked(in_array($guru->id, $selectedIds))>
                        {{ $guru->nama }}
                        @if($guru->nip)
                            <span class="text-xs text-slate-400">&middot; NIP {{ $guru->nip }}</span>
                        @endif
                    </label>
                @endforeach
            </div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.penempatan-pkl.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm dark:border-slate-600">Batal</a>
            <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan</button>
        </div>
    </form>
</div>
@endsection
