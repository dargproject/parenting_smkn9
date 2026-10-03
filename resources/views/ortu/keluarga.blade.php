@extends('layouts.ortu')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('ortu.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard</a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Data Keluarga</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Lengkapi data keluarga <span class="font-semibold">{{ $siswa->nama }}</span>. Data ini juga digunakan oleh guru BK untuk keperluan bimbingan konseling.</p>
    </div>

    <form method="POST" action="{{ route('ortu.keluarga.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Nama Ayah</label>
                <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $keluarga->nama_ayah ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Nama Ibu</label>
                <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $keluarga->nama_ibu ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Pekerjaan Ayah</label>
                <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $keluarga->pekerjaan_ayah ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Pekerjaan Ibu</label>
                <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $keluarga->pekerjaan_ibu ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Pendidikan Ayah</label>
                <input type="text" name="pendidikan_ayah" value="{{ old('pendidikan_ayah', $keluarga->pendidikan_ayah ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Pendidikan Ibu</label>
                <input type="text" name="pendidikan_ibu" value="{{ old('pendidikan_ibu', $keluarga->pendidikan_ibu ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">No. Telp Orang Tua</label>
            <input type="text" name="telp_ortu" value="{{ old('telp_ortu', $keluarga->telp_ortu ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Alamat Ayah</label>
                <textarea name="alamat_ayah" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('alamat_ayah', $keluarga->alamat_ayah ?? '') }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Alamat Ibu</label>
                <textarea name="alamat_ibu" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('alamat_ibu', $keluarga->alamat_ibu ?? '') }}</textarea>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Status Rumah</label>
                <input type="text" name="status_rumah" value="{{ old('status_rumah', $keluarga->status_rumah ?? '') }}" placeholder="Milik sendiri / sewa / dll." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Jumlah Kamar</label>
                <input type="number" min="0" name="jml_kamar" value="{{ old('jml_kamar', $keluarga->jml_kamar ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="punya_kamar_sendiri" value="1" @checked(old('punya_kamar_sendiri', $keluarga->punya_kamar_sendiri ?? false)) class="rounded border-slate-300 dark:border-slate-600">
            Anak memiliki kamar sendiri
        </label>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Media Sosial Anak <span class="font-normal">(opsional)</span></label>
            <input type="text" name="media_sosial" value="{{ old('media_sosial', $keluarga->media_sosial ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        </div>

        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Data Keluarga</button>
    </form>
</div>
@endsection
