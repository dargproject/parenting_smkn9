@php
    $raporParentingRows = $siswaRaporParenting->map(fn ($s) => [
        'id' => $s->id,
        'nama' => $s->nama,
        'kelas' => $s->kelas->nama_kelas ?? '-',
        'cari' => mb_strtolower($s->nama.' '.($s->kelas->nama_kelas ?? '')),
        'url' => route('guru.rapor-parenting.cetak', $s),
        'previewUrl' => route('guru.rapor-parenting.preview', $s),
    ])->values();
@endphp
<div id="pane-rapor-parenting" class="pane-content hidden-pane fade-transition" x-data="{ cari: '', rows: @js($raporParentingRows), get hasil() { const q = this.cari.toLowerCase().trim(); return !q ? this.rows : this.rows.filter(r => r.cari.includes(q)); } }">
    <div class="mb-3">
        <h4 class="font-bold text-slate-100 m-0">Cetak Rapor Parenting</h4>
        <p class="text-slate-400 small m-0">Rapor kolaborasi sekolah &amp; orang tua: ringkasan akademik, kedisiplinan, dan karakter siswa binaan. Bagian pantauan di rumah tetap dicetak kosong untuk diisi orang tua.</p>
    </div>

    <div class="mb-3">
        <input type="search" x-model="cari" placeholder="Cari nama atau kelas siswa..." class="w-full max-w-sm rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100 placeholder:text-slate-500">
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr><th class="px-4 py-3">Nama Siswa</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3 text-center">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                <template x-for="r in hasil" :key="r.id">
                    <tr>
                        <td class="px-4 py-2.5 font-semibold text-slate-100" x-text="r.nama"></td>
                        <td class="px-4 py-2.5 text-slate-400" x-text="r.kelas"></td>
                        <td class="px-4 py-2.5 text-center">
                            <a :href="r.previewUrl" target="_blank" class="rounded-lg border border-slate-500/40 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-500/10 mr-1">
                                <i class="fa-solid fa-eye mr-1"></i> Lihat Data
                            </a>
                            <a :href="r.url" target="_blank" class="rounded-lg border border-blue-500/40 px-3 py-1 text-xs font-semibold text-blue-400 hover:bg-blue-500/10">
                                <i class="fa-solid fa-file-pdf mr-1"></i> Cetak PDF
                            </a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!hasil.length"><td colspan="3" class="px-4 py-6 text-center text-slate-400">@if($siswaRaporParenting->isEmpty()) Anda belum menjadi wali kelas atau guru wali untuk siswa manapun. @else Tidak ada siswa yang cocok. @endif</td></tr>
            </tbody>
        </table>
    </div>
</div>
