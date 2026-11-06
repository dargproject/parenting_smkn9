<div class="space-y-5">
    <div>
        <label for="nama_ekskul" class="mb-1.5 block text-sm font-medium">Nama Ekstrakurikuler</label>
        <input id="nama_ekskul" name="nama_ekskul" type="text" required value="{{ old('nama_ekskul', $item->nama_ekskul) }}" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900">
        @error('nama_ekskul')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium">Pembina</label>
        <p class="mb-2 text-xs text-slate-500 dark:text-slate-400">Bisa pilih lebih dari satu; semua pembina yang dicentang punya akses yang sama untuk kelola peserta &amp; nilai ekskul ini.</p>
        <div class="max-h-56 space-y-1.5 overflow-y-auto rounded-lg border border-slate-300 p-3 dark:border-slate-600">
            @forelse($pembinaOptions as $id => $nama)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="pembina_ids[]" value="{{ $id }}" @checked(in_array($id, $selectedPembinaIds))>
                    {{ $nama }}
                </label>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada guru dengan role Pembina Ekskul.</p>
            @endforelse
        </div>
        @error('pembina_ids')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="flex items-center gap-2 py-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Status Aktif</label>
    </div>
</div>
