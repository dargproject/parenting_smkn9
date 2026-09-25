<div id="pane-kurikulum-wali" class="pane-content hidden-pane fade-transition">
    @php
        $guruPilihan = \App\Models\Guru::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'kepsek']))->orderBy('nama')->get(['id', 'nama']);
        $kelasWali = $kelasList->sortBy('nama_kelas', SORT_NATURAL)->values();
    @endphp
    <div x-data="{ q: '' }">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-3">
            <div>
                <h4 class="font-bold text-slate-100 mb-1">Wali Kelas &amp; Guru Wali</h4>
                <p class="text-slate-400 text-sm mb-0">Pilih guru untuk tiap kelas, lalu simpan sekali. Guru terpilih otomatis mendapat akses menu perannya.</p>
            </div>
            <input type="text" x-model="q" placeholder="Cari kelas..." class="w-full sm:w-48 rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100 placeholder:text-slate-500">
        </div>

        <form method="POST" action="{{ route('guru.kurikulum.wali.update') }}" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 overflow-hidden">
            @csrf @method('PUT')
            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 w-1/4">Kelas</th>
                            <th class="px-3 py-2">Wali Kelas</th>
                            <th class="px-3 py-2">Guru Wali</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($kelasWali as $k)
                            <tr x-show="!q || '{{ strtolower($k->nama_kelas) }}'.includes(q.toLowerCase())">
                                <td class="px-3 py-1.5 font-semibold text-slate-100 whitespace-nowrap">{{ $k->nama_kelas }}</td>
                                @foreach(['wali_kelas_id' => 'Pilih wali kelas', 'guru_wali_id' => 'Pilih guru wali'] as $kolom => $placeholder)
                                    <td class="px-3 py-1.5">
                                        <select name="wali[{{ $k->id }}][{{ $kolom }}]" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-xs text-slate-100">
                                            <option value="">— {{ $placeholder }} —</option>
                                            @foreach($guruPilihan as $g)
                                                <option value="{{ $g->id }}" @selected($k->$kolom == $g->id)>{{ $g->nama }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-slate-700/60 bg-slate-900 px-3 py-2">
                <span class="text-xs text-slate-400">{{ $kelasWali->count() }} kelas</span>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Penetapan</button>
            </div>
        </form>
    </div>
</div>
