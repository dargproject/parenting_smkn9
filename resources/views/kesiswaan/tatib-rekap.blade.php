<div id="pane-kesiswaan-rekap" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-1">Rekap Poin Pelanggaran Siswa</h4>
    <p class="text-slate-400 text-sm mb-4">Akumulasi poin pelanggaran seluruh siswa berdasarkan data yang tercatat.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-5 py-3">Nama Siswa</th>
                    <th class="px-5 py-3">NIS</th>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3 text-center">Total Poin</th>
                    <th class="px-5 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($rekapPoin as $siswa)
                    @php
                        $poin = $siswa->total_poin ?? 0;
                        $statusLabel = 'Aman';
                        $statusClass = 'bg-emerald-500/10 text-emerald-400';
                        if ($poin >= 75) { $statusLabel = 'Terancam DO'; $statusClass = 'bg-rose-500/10 text-rose-400'; }
                        elseif ($poin >= 50) { $statusLabel = 'SP 2'; $statusClass = 'bg-rose-500/10 text-rose-400'; }
                        elseif ($poin >= 25) { $statusLabel = 'SP 1'; $statusClass = 'bg-amber-500/10 text-amber-400'; }
                    @endphp
                    <tr>
                        <td class="px-5 py-3 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $siswa->nis }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-center font-bold text-blue-400">{{ $poin }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
