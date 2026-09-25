@csrf
@if($tahunAjaran->exists) @method('PUT') @endif
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium">Kode</label>
        <input name="kode" value="{{ old('kode', $tahunAjaran->kode) }}" placeholder="2025/2026-1" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 dark:border-slate-600 dark:bg-slate-900">
        @error('kode')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium">Nama</label>
        <input name="nama" value="{{ old('nama', $tahunAjaran->nama) }}" placeholder="2025/2026 Ganjil" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 dark:border-slate-600 dark:bg-slate-900">
        @error('nama')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium">Semester</label>
        <select name="semester" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 dark:border-slate-600 dark:bg-slate-900">
            <option value="ganjil" @selected(old('semester', $tahunAjaran->semester) === 'ganjil')>Ganjil</option>
            <option value="genap" @selected(old('semester', $tahunAjaran->semester) === 'genap')>Genap</option>
        </select>
    </div>
    <label class="flex items-center gap-2 self-end pb-3 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tahunAjaran->is_active))>
        Jadikan aktif
    </label>
</div>
<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.tahun-ajaran.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">Batal</a>
    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan</button>
</div>
