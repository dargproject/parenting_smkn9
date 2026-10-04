@php
    $rekapRows = $rekapPoin->map(function ($siswa) {
        $poin = (int) ($siswa->total_poin ?? 0);
        [$label, $kelasCss] = match (true) {
            $poin >= 75 => ['Terancam DO', 'bg-rose-500/10 text-rose-400'],
            $poin >= 50 => ['SP 2', 'bg-rose-500/10 text-rose-400'],
            $poin >= 25 => ['SP 1', 'bg-amber-500/10 text-amber-400'],
            default => ['Aman', 'bg-emerald-500/10 text-emerald-400'],
        };

        return ['id' => $siswa->id, 'cari' => mb_strtolower($siswa->nama.' '.$siswa->nis), 'kelas' => (string) $siswa->kelas_id, 'status' => $label];
    })->values();
    $kelasRekap = $rekapPoin->pluck('kelas')->filter()->unique('id')->sortBy('nama_kelas', SORT_NATURAL)->values();
@endphp
<div id="pane-kesiswaan-rekap" class="pane-content hidden-pane fade-transition" x-data="{
        rows: @js($rekapRows), cari: '', kelas: '', status: '', hal: 1, perHal: 20, terlihat: new Set(), total: 0,
        hitung() {
            const q = this.cari.toLowerCase().trim();
            const hasil = this.rows.filter(r => (!q || r.cari.includes(q)) && (!this.kelas || r.kelas === this.kelas) && (!this.status || r.status === this.status));
            this.total = hasil.length;
            const maks = Math.max(1, Math.ceil(hasil.length / this.perHal));
            if (this.hal > maks) this.hal = maks;
            this.terlihat = new Set(hasil.slice((this.hal - 1) * this.perHal, this.hal * this.perHal).map(r => r.id));
        },
        get maks() { return Math.max(1, Math.ceil(this.total / this.perHal)); },
        get filterAktif() { return this.cari || this.kelas || this.status; },
        reset() { this.cari = ''; this.kelas = ''; this.status = ''; this.hal = 1; }
     }" x-effect="hitung()" x-init="$watch('cari', () => hal = 1); $watch('kelas', () => hal = 1); $watch('status', () => hal = 1)">
    <h4 class="font-bold text-slate-100 mb-1">Rekap Poin Pelanggaran Siswa</h4>
    <p class="text-slate-400 text-sm mb-3">Akumulasi poin pelanggaran seluruh siswa berdasarkan data yang tercatat.</p>

    <div class="mb-3 flex flex-col gap-2 rounded-xl border border-slate-700/60 bg-slate-800/80 p-3 lg:flex-row lg:items-center">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input type="search" x-model="cari" placeholder="Cari nama atau NIS siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 py-1.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500">
        </div>
        <select x-model="kelas" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            <option value="">Semua kelas</option>
            @foreach($kelasRekap as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
        </select>
        <select x-model="status" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            <option value="">Semua status</option>
            <option>Aman</option><option>SP 1</option><option>SP 2</option><option>Terancam DO</option>
        </select>
        <button type="button" x-show="filterAktif" style="display: none;" @click="reset()" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-slate-300 hover:border-blue-500">Reset</button>
    </div>

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
                @forelse($rekapPoin as $i => $siswa)
                    @php
                        $poin = (int) ($siswa->total_poin ?? 0);
                        $statusLabel = $rekapRows[$i]['status'];
                        $statusTone = match ($statusLabel) { 'Aman' => 'emerald', 'SP 1' => 'amber', default => 'rose' };
                    @endphp
                    <tr x-show="terlihat.has({{ $siswa->id }})">
                        <td class="px-5 py-3 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $siswa->nis }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-center font-bold text-blue-400">{{ $poin }}</td>
                        <td class="px-5 py-3 text-center">
                            <x-status-badge :tone="$statusTone">{{ $statusLabel }}</x-status-badge>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data siswa.</td></tr>
                @endforelse
                <tr x-show="total === 0 && {{ $rekapPoin->count() }} > 0" style="display: none;"><td colspan="5" class="px-5 py-8 text-center text-slate-400">Tidak ada siswa yang cocok dengan pencarian/filter.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex flex-col items-center justify-between gap-2 sm:flex-row" x-show="total > 0">
        <p class="text-sm text-slate-400" x-text="'Menampilkan ' + ((hal - 1) * perHal + 1) + '–' + Math.min(hal * perHal, total) + ' dari ' + total + ' siswa'"></p>
        <div class="flex items-center gap-2">
            <button type="button" @click="hal--" :disabled="hal <= 1" class="rounded-lg border border-slate-600 px-3 py-1 text-sm text-slate-300 disabled:opacity-40">&larr;</button>
            <span class="text-sm text-slate-300" x-text="hal + ' / ' + maks"></span>
            <button type="button" @click="hal++" :disabled="hal >= maks" class="rounded-lg border border-slate-600 px-3 py-1 text-sm text-slate-300 disabled:opacity-40">&rarr;</button>
        </div>
    </div>
</div>