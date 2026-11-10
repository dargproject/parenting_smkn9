@extends('layouts.app')

@section('content')
<div class="admin-dashboard mx-auto max-w-4xl space-y-6">
    <div><h1 class="text-2xl font-bold">Tambah Penempatan PKL</h1></div>
    @include('admin.partials.flash')
    <form method="POST" action="{{ route('admin.penempatan-pkl.store') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 md:p-6">
        @csrf
        @include('admin.penempatan-pkl._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.penempatan-pkl.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm dark:border-slate-600">Batal</a>
            <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan</button>
        </div>
    </form>
</div>
@endsection
