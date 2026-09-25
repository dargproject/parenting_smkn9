@extends('layouts.ortu')

@section('content')
<div class="mx-auto max-w-sm">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white">
                <i class="fa-solid fa-user-group text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Portal Orang Tua</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pantau perkembangan nilai putra/putri Anda</p>
        </div>

        <form method="POST" action="{{ route('ortu.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Username</label>
                <input type="text" name="username" required autofocus class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Masuk</button>
        </form>
    </div>
</div>
@endsection
