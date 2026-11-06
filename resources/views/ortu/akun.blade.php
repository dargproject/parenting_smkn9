@extends('layouts.ortu')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('ortu.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard</a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Akun Saya</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Ubah nama orang tua/wali dan password kapan saja.</p>
    </div>

    <form method="POST" action="{{ route('ortu.akun.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Nama Orang Tua / Wali</label>
            <input type="text" name="nama" value="{{ old('nama', $orangTua->nama) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Password Baru</label>
            <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Ulangi Password Baru</label>
            <input type="password" name="password_confirmation" minlength="6" placeholder="Ulangi password baru" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        </div>
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Perubahan</button>
    </form>
</div>
@endsection
