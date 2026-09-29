@extends('layouts.app')
@section('content')
<div class="admin-dashboard mx-auto max-w-3xl space-y-6">
    <div><h1 class="text-2xl font-bold">Import Data</h1><p class="text-sm text-slate-500 dark:text-slate-400">Unggah file Excel/CSV dengan header kolom sesuai data.</p></div>
    @include('admin.partials.flash')
    <div class="grid gap-6 md:grid-cols-2">
        <form method="POST" action="{{ route('admin.import.siswa') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
            @csrf
            <h2 class="font-bold">Import Siswa</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kolom: <code>nis, nisn, nipd, nama, kelas, jenis_kelamin, tanggal_lahir, no_hp_ortu</code>. Kolom <code>kelas</code> diisi nama kelas persis seperti di data Kelas (lihat daftar di bawah).</p>
            <a href="{{ route('admin.import.template.siswa') }}" class="mt-2 inline-block text-xs font-semibold text-blue-600 dark:text-blue-400"><i class="fa-solid fa-download mr-1"></i>Unduh Template Contoh</a>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="mt-4 w-full text-sm">
            <button class="mt-4 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Upload & Import</button>
        </form>
        <form method="POST" action="{{ route('admin.import.guru') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
            @csrf
            <h2 class="font-bold">Import Guru</h2>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="mt-4 w-full text-sm">
            <button class="mt-4 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Upload & Import</button>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
        <h3 class="font-bold text-sm mb-2">Nama Kelas yang Tersedia (untuk kolom "kelas" di file Import Siswa)</h3>
        @if($daftarKelas->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada data Kelas. Tambahkan Kelas terlebih dahulu di menu Master Kelas sebelum import siswa.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($daftarKelas as $namaKelas)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $namaKelas }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
