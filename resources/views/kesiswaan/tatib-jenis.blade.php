@php
    $grouped = $masterPelanggarans->groupBy(fn ($item) => $item->pasal->nama ?? '-');
    $daftarJenis = $masterPelanggarans->mapWithKeys(fn ($m) => [$m->id => [
        'nama' => mb_strtolower($m->nama_pelanggaran),
        'pasal' => $m->pasal->nama ?? '-',
        'jenis' => $m->jenisPelanggaran->nama ?? '-',
        'aktif' => (bool) $m->is_active,
    ]]);
    $pilihanJenis = $masterPelanggarans->map(fn ($m) => $m->jenisPelanggaran->nama ?? '-')->unique()->sort()->values();
@endphp
<div id="pane-kesiswaan-jenis" class="pane-content hidden-pane fade-transition" x-data="{
        cari: '', pasal: '', jenis: '', status: '',
        daftar: @js($daftarJenis),
        cocok(id) {
            const d = this.daftar[id];
            return (!this.cari || d.nama.includes(this.cari.toLowerCase().trim()))
                && (!this.pasal || d.pasal === this.pasal)
                && (!this.jenis || d.jenis === this.jenis)
                && (!this.status || (this.status === 'aktif') === d.aktif);
        },
        adaDi(pasal) { return Object.keys(this.daftar).some(id => this.daftar[id].pasal === pasal && this.cocok(id)); },
        get jumlah() { return Object.keys(this.daftar).filter(id => this.cocok(id)).length; },
        get filterAktif() { return this.cari || this.pasal || this.jenis || this.status; },
        reset() { this.cari = ''; this.pasal = ''; this.jenis = ''; this.status = ''; }
     }">
    <h4 class="font-bold text-slate-100 mb-1">Jenis Pelanggaran</h4>
    <p class="text-slate-400 text-sm mb-3">Katalog tata tertib sekolah beserta bobot poin masing-masing pelanggaran.</p>

    <div class="mb-4 flex flex-col gap-2 rounded-xl border border-slate-700/60 bg-slate-800/80 p-3 lg:flex-row lg:items-center">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input type="search" x-model="cari" placeholder="Cari nama pelanggaran..." class="w-full rounded-lg border border-slate-600 bg-slate-900 py-1.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500">
        </div>
        <select x-model="pasal" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            <option value="">Semua pasal</option>
            @foreach($grouped->keys() as $namaPasal)<option value="{{ $namaPasal }}">{{ $namaPasal }}</option>@endforeach
        </select>
        <select x-model="jenis" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            <option value="">Semua jenis</option>
            @foreach($pilihanJenis as $namaJenis)<option value="{{ $namaJenis }}">{{ $namaJenis }}</option>@endforeach
        </select>
        <select x-model="status" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            <option value="">Semua status</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
        <button type="button" x-show="filterAktif" style="display: none;" @click="reset()" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-slate-300 hover:border-blue-500">Reset</button>
        <span class="whitespace-nowrap text-xs text-slate-400" x-text="jumlah + ' dari {{ $masterPelanggarans->count() }} jenis'"></span>
    </div>

    <div class="space-y-6">
        @forelse($grouped as $pasalNama => $items)
            <div x-show="adaDi({{ \Illuminate\Support\Js::from($pasalNama) }})" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
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
                            <tr x-show="cocok({{ $item->id }})">
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
        <div x-show="jumlah === 0" style="display: none;" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-8 text-center text-slate-400">Tidak ada pelanggaran yang cocok dengan pencarian/filter.</div>
    </div>
</div>
