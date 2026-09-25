<div id="pane-kesiswaan-catat" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-1">Catat Pelanggaran</h4>
    <p class="text-slate-400 text-sm mb-4">Catat kejadian pelanggaran tata tertib yang dilakukan siswa.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 max-w-2xl">
        <form method="POST" action="{{ route('guru.pelanggaran.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Siswa</label>
                @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas])
                @error('siswa_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Pasal</label>
                <select name="master_pelanggaran_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih jenis pelanggaran...</option>
                    @foreach($masterPelanggaranAktif->groupBy(fn($mp) => $mp->pasal->nama ?? '-') as $pasalNama => $group)
                        <optgroup label="{{ $pasalNama }}">
                            @foreach($group as $mp)
                                <option value="{{ $mp->id }}" @selected(old('master_pelanggaran_id') == $mp->id)>{{ $mp->nama_pelanggaran }} — {{ $mp->jenisPelanggaran->nama ?? '-' }} ({{ $mp->jenisPelanggaran->poin ?? '-' }} poin)</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('master_pelanggaran_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                @error('tanggal')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Keterangan</label>
                <textarea name="keterangan" rows="4" placeholder="Detail Kejadian" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Pelanggaran</button>
            </div>
        </form>
    </div>
</div>
