@extends('layouts.ortu')

@section('content')
<div class="mx-auto max-w-sm">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white">
                <i class="fa-solid fa-user-plus text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Daftar Akun Orang Tua</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pilih anak Anda, lalu buat akun sendiri. Akun bisa dipakai setelah disetujui wali kelas.</p>
        </div>

        <form method="POST" action="{{ route('ortu.daftar.store') }}" class="space-y-4" x-data="{
                kelasId: '', siswaId: '', siswaList: [], memuat: false,
                pilihKelas() {
                    this.siswaId = ''; this.siswaList = []; this.memuat = true;
                    if (!this.kelasId) { this.memuat = false; return; }
                    fetch(`{{ url('/ortu/api/siswa-by-kelas') }}/${this.kelasId}`)
                        .then(r => r.json()).then(d => { this.siswaList = d.siswa; this.memuat = false; });
                }
             }">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Kelas</label>
                <select x-model="kelasId" @change="pilihKelas()" required class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Siswa (Anak Anda)</label>
                <select name="siswa_id" x-model="siswaId" :disabled="!kelasId" required class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white disabled:opacity-50">
                    <option value="">
                        <span x-text="memuat ? 'Memuat...' : '-- Pilih Siswa --'"></span>
                    </option>
                    <template x-for="s in siswaList" :key="s.id">
                        <option :value="s.id" x-text="s.nama"></option>
                    </template>
                </select>
                <p x-show="kelasId && !memuat && !siswaList.length" style="display: none;" class="mt-1 text-xs text-amber-600 dark:text-amber-400">Semua siswa di kelas ini sudah punya akun orang tua.</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Orang Tua/Wali</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">No. HP (jadi username login)</label>
                <input type="text" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Ulangi Password</label>
                <input type="password" name="password_confirmation" required minlength="6" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:text-white">
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Daftar</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500 dark:text-slate-400">
            Sudah punya akun? <a href="{{ route('ortu.login') }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
