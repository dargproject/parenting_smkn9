@extends('layouts.app')

@section('content')
<div class="admin-dashboard mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Pengaturan Sekolah</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola identitas sekolah dan periode akademik aktif.</p>
    </div>

    @include('admin.partials.flash')

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 md:p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach([
                ['name' => 'nama_sekolah', 'label' => 'Nama Sekolah', 'type' => 'text'],
                ['name' => 'npsn', 'label' => 'NPSN', 'type' => 'text'],
                ['name' => 'nama_kepsek', 'label' => 'Nama Kepala Sekolah', 'type' => 'text'],
                ['name' => 'nip_kepsek', 'label' => 'NIP Kepala Sekolah', 'type' => 'text'],
                ['name' => 'bobot_lm', 'label' => 'Bobot Sumatif LM (%)', 'type' => 'number'],
                ['name' => 'bobot_sas', 'label' => 'Bobot SAS (%)', 'type' => 'number'],
                ['name' => 'kktp_threshold', 'label' => 'Ambang KKTP', 'type' => 'number'],
                ['name' => 'kktp_margin', 'label' => 'Toleransi "Cukup" di bawah KKTP (poin)', 'type' => 'number'],
                ['name' => 'alpa_mingguan_threshold', 'label' => 'Ambang Peringatan Alpa Mingguan (hari)', 'type' => 'number'],
            ] as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                    <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] }}" value="{{ old($field['name'], $settings[$field['name']] ?? '') }}" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-100">
                    @error($field['name'])<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div class="md:col-span-2">
                <label for="alamat_sekolah" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Sekolah</label>
                <textarea id="alamat_sekolah" name="alamat_sekolah" rows="3" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-100">{{ old('alamat_sekolah', $settings['alamat_sekolah'] ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                Tahun ajaran dan semester aktif diatur di menu <a href="{{ route('admin.tahun-ajaran.index') }}" class="font-semibold text-blue-600 dark:text-blue-400 underline">Tahun Ajaran</a> (tombol Aktifkan), agar tidak ada dua sumber yang berbeda.
            </div>
            <div class="md:col-span-2">
                <label for="logo_sekolah" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Logo Sekolah</label>
                @if(!empty($settings['logo_sekolah']))
                    <img src="{{ asset('storage/' . $settings['logo_sekolah']) }}" alt="Logo sekolah" class="mb-3 h-16 w-16 rounded-lg object-contain">
                @endif
                <input id="logo_sekolah" name="logo_sekolah" type="file" accept="image/*" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm dark:border-slate-600 dark:text-slate-100">
                @error('logo_sekolah')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Pengaturan</button>
        </div>
    </form>
</div>
@endsection
