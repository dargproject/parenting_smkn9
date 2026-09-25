@extends('layouts.app')

@section('content')
<div class="admin-dashboard mx-auto max-w-3xl space-y-6"><div><h1 class="text-2xl font-bold">Tambah Tahun Ajaran</h1><p class="text-sm text-slate-500 dark:text-slate-400">Buat periode akademik baru.</p></div><form method="POST" action="{{ route('admin.tahun-ajaran.store') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 md:p-6">@include('admin.tahun-ajaran._form')</form></div>
@endsection
