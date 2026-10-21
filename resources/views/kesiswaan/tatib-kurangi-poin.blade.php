<div id="pane-kesiswaan-kurangi-poin" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-1">Kurangi Poin (Kegiatan Positif)</h4>
    <p class="text-slate-400 text-sm mb-4">Siswa bisa mengurangi 3 poin pelanggaran jika mengikuti kegiatan positif (petugas upacara, juara lomba, atau kegiatan sosial). Maksimal 1x per bulan per siswa.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 max-w-2xl">
        <form method="POST" action="{{ route('guru.tatib.pengurangan-poin.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Siswa</label>
                @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas])
                @error('siswa_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Kegiatan</label>
                <select name="kegiatan" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">-- Pilih kegiatan --</option>
                    <option value="Petugas Upacara">Petugas Upacara</option>
                    <option value="Juara Lomba">Juara Lomba</option>
                    <option value="Kegiatan Sosial">Kegiatan Sosial</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                @error('kegiatan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                @error('tanggal')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Keterangan (opsional)</label>
                <textarea name="keterangan" rows="3" placeholder="Detail kegiatan" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Pengurangan Poin</button>
            </div>
        </form>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto max-w-2xl">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Siswa</th>
                    <th class="px-5 py-3">Kegiatan</th>
                    <th class="px-5 py-3 text-center">Poin</th>
                    <th class="px-5 py-3">Diberikan Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($penguranganPoinTerbaru as $p)
                    <tr>
                        <td class="px-5 py-3 text-slate-400">{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-100">{{ $p->siswa->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $p->kegiatan }}</td>
                        <td class="px-5 py-3 text-center font-bold text-emerald-400">-{{ $p->poin }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $p->diberikanOleh->nama ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada pengurangan poin tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
