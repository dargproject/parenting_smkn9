@php
    $sanksiWali = app(\App\Services\SanksiService::class);
    $rekapPoinWaliRows = $rekapPoinWali->map(function ($siswa) use ($sanksiWali) {
        $status = $sanksiWali->statusSemester((int) ($siswa->total_poin ?? 0));

        return ['siswa' => $siswa, 'poin' => (int) ($siswa->total_poin ?? 0), 'status' => $status];
    })->sortByDesc('poin')->values();
@endphp
<div id="pane-wali-rekap-poin" class="pane-content hidden-pane fade-transition" x-data="{
        rujukModal: false, rujukSiswaId: null, rujukSiswaNama: '', kategori: 'Normal', alasan: '',
        bukaRujuk(id, nama, statusLabel, poin) {
            this.rujukSiswaId = id; this.rujukSiswaNama = nama; this.kategori = 'Normal';
            this.alasan = `Siswa ${nama} sudah mencapai status ${statusLabel} dengan ${poin} poin pelanggaran tahun ajaran ini.`;
            this.rujukModal = true;
        }
     }">
    <div class="mb-3">
        <h4 class="font-bold text-slate-100 m-0">Rekap Poin Siswa Binaan</h4>
        <p class="text-slate-400 small m-0">Akumulasi poin pelanggaran siswa kelas perwalian Anda, tahun ajaran aktif. Siswa dengan status SP bisa langsung dirujuk ke BK.</p>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-5 py-3">Nama Siswa</th>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3 text-center">Poin</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($rekapPoinWaliRows as $row)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-slate-100">{{ $row['siswa']->nama }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $row['siswa']->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-center font-bold text-blue-400">{{ $row['poin'] }}</td>
                        <td class="px-5 py-3 text-center">
                            <x-status-badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-status-badge>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($row['status']['kode'])
                                <button type="button" @click="bukaRujuk({{ $row['siswa']->id }}, {{ \Illuminate\Support\Js::from($row['siswa']->nama) }}, {{ \Illuminate\Support\Js::from($row['status']['label']) }}, {{ $row['poin'] }})" class="rounded-lg border border-blue-500/40 px-3 py-1 text-xs font-semibold text-blue-400 hover:bg-blue-500/10">
                                    <i class="fa-solid fa-share mr-1"></i> Rujuk ke BK
                                </button>
                            @else
                                <span class="text-xs text-slate-500">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada siswa binaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4">
        <h6 class="font-bold text-slate-100 mb-2"><i class="fa-solid fa-paper-plane mr-1 text-blue-400"></i> Rujukan BK Terakhir Anda</h6>
        <div class="space-y-2">
            @forelse($rujukanBkSaya as $r)
                <div class="flex items-center justify-between rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm">
                    <span class="text-slate-100">{{ $r->siswa->nama ?? '-' }} <span class="text-xs text-slate-400">· {{ $r->kategori }}</span></span>
                    <x-status-badge :tone="match($r->status) { 'diterima' => 'emerald', 'ditolak' => 'rose', default => 'amber' }">{{ ucfirst($r->status) }}</x-status-badge>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum pernah merujuk siswa ke BK.</p>
            @endforelse
        </div>
    </div>

    <div x-show="rujukModal" style="display: none;" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="rujukModal = false" @click.self="rujukModal = false">
        <div class="solid-panel w-full max-w-md rounded-2xl p-5 text-slate-100 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold">Rujuk ke BK &mdash; <span x-text="rujukSiswaNama"></span></h3>
                <button type="button" @click="rujukModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="{{ route('guru.wali-kelas.rujukan-bk.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="siswa_id" :value="rujukSiswaId">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Kategori</label>
                    <select name="kategori" x-model="kategori" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="Normal">Normal</option>
                        <option value="Mendesak">Mendesak</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Alasan</label>
                    <textarea name="alasan" x-model="alasan" rows="4" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="rujukModal = false" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Kirim Rujukan</button>
                </div>
            </form>
        </div>
    </div>
</div>
