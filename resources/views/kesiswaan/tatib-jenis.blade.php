<div id="pane-kesiswaan-jenis" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-1">Jenis Pelanggaran</h4>
    <p class="text-slate-400 text-sm mb-4">Katalog tata tertib sekolah beserta bobot poin masing-masing pelanggaran.</p>

    @php $grouped = $masterPelanggarans->groupBy(fn ($item) => $item->pasal->nama ?? '-'); @endphp

    <div class="space-y-6">
        @forelse($grouped as $pasalNama => $items)
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
                <div class="px-5 py-3 border-b border-slate-700/60">
                    <h6 class="font-bold text-slate-100 m-0">{{ $pasalNama }}</h6>
                </div>
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                        <tr>
                            <th class="px-5 py-3">Nama Pelanggaran</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3 text-center">Poin</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($items as $item)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-100">{{ $item->nama_pelanggaran }}</td>
                                <td class="px-5 py-3 text-slate-400">{{ $item->jenisPelanggaran->nama ?? '-' }}</td>
                                <td class="px-5 py-3 text-center font-bold text-blue-400">{{ $item->jenisPelanggaran->poin ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-8 text-center text-slate-500">Belum ada jenis pelanggaran terdaftar.</div>
        @endforelse
    </div>
</div>
