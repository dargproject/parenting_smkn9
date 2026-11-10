<div class="space-y-4">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Siswa</label>
        @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $item->siswa_id])
        @error('siswa_id')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Tahun Ajaran</label>
        <select name="tahun_ajaran_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            @foreach($tahunAjaranOptions as $id => $nama)
                <option value="{{ $id }}" @selected(old('tahun_ajaran_id', $item->tahun_ajaran_id ?? $tahunAjaranAktifId) == $id)>{{ $nama }}</option>
            @endforeach
        </select>
        @error('tahun_ajaran_id')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Nama IDUKA</label>
        <input type="text" name="nama_iduka" value="{{ old('nama_iduka', $item->nama_iduka) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
        @error('nama_iduka')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Alamat</label>
        <textarea name="alamat" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('alamat', $item->alamat) }}</textarea>
        @error('alamat')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Nama CP Industri</label>
            <input type="text" name="nama_cp_industri" value="{{ old('nama_cp_industri', $item->nama_cp_industri) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            @error('nama_cp_industri')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">No. CP Industri</label>
            <input type="text" name="no_cp_industri" value="{{ old('no_cp_industri', $item->no_cp_industri) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            @error('no_cp_industri')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Pembimbing Sekolah</label>
        <select name="guru_pembimbing_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            <option value="">&mdash; Belum ditentukan &mdash;</option>
            @foreach($guruPembimbingOptions as $id => $nama)
                <option value="{{ $id }}" @selected(old('guru_pembimbing_id', $item->guru_pembimbing_id) == $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-400">Hanya guru dengan peran "Pembimbing PKL" yang muncul di sini. Periode PKL diisi sendiri oleh pembimbing lewat menunya.</p>
        @error('guru_pembimbing_id')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>
</div>

