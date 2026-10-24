<div id="pane-guru-wali-presensi" class="pane-content hidden-pane fade-transition space-y-4" x-data="rekapAlpaWali()">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Rekap Presensi per Mapel</h4>
        <p class="text-slate-400 text-sm">Jumlah Alpa siswa binaan pada tiap mata pelajaran -- untuk melihat apakah nilai yang kurang baik berkaitan dengan kehadiran di mapel tersebut. Sakit &amp; Izin juga ikut tercatat (klik angkanya untuk lihat detail tanggal dan keterangan dari guru mapel).</p>
    </div>

    @if($siswaBinaan->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum menjadi guru wali (akademik) untuk kelas manapun.</p>
    @else
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 flex flex-col gap-2 sm:flex-row sm:items-center">
            <select x-model="periode" @change="muat()" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
                <option value="minggu">Minggu Ini</option>
                <option value="bulan" selected>Bulan Ini</option>
                <option value="custom">Rentang Tanggal...</option>
            </select>
            <template x-if="periode === 'custom'">
                <div class="flex items-center gap-2">
                    <input type="date" x-model="mulai" @change="muat()" :max="selesai || null" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
                    <span class="text-slate-400 text-sm">s/d</span>
                    <input type="date" x-model="selesai" @change="muat()" :min="mulai || null" max="{{ now()->toDateString() }}" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
                </div>
            </template>
            <span class="text-xs text-slate-400 sm:ml-auto">Sel berwarna merah = Alpa &ge; 3 kali pada periode ini.</span>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 overflow-x-auto">
            <template x-if="loading">
                <p class="text-slate-400 text-sm p-4 m-0"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat...</p>
            </template>
            <template x-if="!loading && !mapel.length">
                <p class="text-slate-400 text-sm p-4 m-0">Tidak ada catatan Alpa, Sakit, atau Izin pada periode ini untuk siswa binaan Anda.</p>
            </template>
            <table x-show="!loading && mapel.length" style="display: none;" class="w-full min-w-[560px] text-left text-sm">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <template x-for="m in mapel" :key="m.id">
                            <th class="px-3 py-3 text-center" x-text="m.nama"></th>
                        </template>
                        <th class="px-4 py-3 text-center font-bold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    <template x-for="s in siswa" :key="s.id">
                        <tr x-show="totalSemuaFor(s) > 0">
                            <td class="px-4 py-2.5 font-semibold text-slate-100" x-text="s.nama"></td>
                            <td class="px-4 py-2.5 text-slate-400" x-text="s.kelas"></td>
                            <template x-for="m in mapel" :key="m.id">
                                <td class="px-3 py-2 text-center cursor-pointer hover:bg-slate-700/30" @click="bukaDetail(s, m)">
                                    <span class="font-bold"
                                        :class="(s.perMapel[m.id]?.alpa || 0) >= ambangMerah ? 'text-rose-400' : (s.perMapel[m.id]?.alpa || 0) > 0 ? 'text-amber-400' : 'text-slate-600'"
                                        x-text="s.perMapel[m.id]?.alpa || 0"></span>
                                    <span class="block text-[10px] leading-tight text-slate-400" x-show="(s.perMapel[m.id]?.sakit || 0) + (s.perMapel[m.id]?.izin || 0) > 0" style="display: none;"
                                        x-text="'S' + (s.perMapel[m.id]?.sakit || 0) + ' · I' + (s.perMapel[m.id]?.izin || 0)"></span>
                                </td>
                            </template>
                            <td class="px-4 py-2.5 text-center font-bold text-slate-100" x-text="s.total"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    @endif

    <div x-show="detailModal" style="display: none;" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="detailModal = false" @click.self="detailModal = false">
        <div class="solid-panel w-full max-w-md rounded-2xl p-5 text-slate-100 shadow-xl max-h-[85vh] flex flex-col">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold m-0" x-text="detailSiswa"></h3>
                    <p class="text-xs text-slate-400 m-0" x-text="detailMapel"></p>
                </div>
                <button type="button" @click="detailModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="overflow-y-auto">
                <table class="w-full text-left text-sm" x-show="detailList.length">
                    <thead class="bg-slate-900 text-xs text-slate-400">
                        <tr><th class="px-2 py-1.5">Tanggal</th><th class="px-2 py-1.5 text-center">Status</th><th class="px-2 py-1.5">Keterangan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <template x-for="d in detailList" :key="d.tanggal + d.status">
                            <tr>
                                <td class="px-2 py-1.5 whitespace-nowrap" x-text="d.tanggal"></td>
                                <td class="px-2 py-1.5 text-center">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                                        :class="{ 'bg-cyan-500/10 text-cyan-400': d.status === 'S', 'bg-amber-500/10 text-amber-400': d.status === 'I', 'bg-rose-500/10 text-rose-400': d.status === 'A' }"
                                        x-text="d.status"></span>
                                </td>
                                <td class="px-2 py-1.5 text-slate-400" x-text="d.keterangan || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p class="text-sm text-slate-400 m-0" x-show="!detailList.length">Tidak ada presensi Alpa/Sakit/Izin untuk kombinasi ini.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function rekapAlpaWali() {
        return {
            periode: 'bulan',
            mulai: '',
            selesai: '',
            loading: false,
            ambangMerah: 3,
            mapel: [],
            siswa: [],
            detailModal: false,
            detailSiswa: '',
            detailMapel: '',
            detailList: [],
            init() { this.muat(); },
            totalSemuaFor(s) {
                return Object.values(s.perMapel).reduce((sum, c) => sum + c.alpa + c.sakit + c.izin, 0);
            },
            bukaDetail(s, m) {
                this.detailSiswa = s.nama;
                this.detailMapel = m.nama;
                this.detailList = s.perMapel[m.id]?.detail || [];
                this.detailModal = true;
            },
            rentang() {
                const now = new Date();
                const todayIso = now.toISOString().slice(0, 10);
                if (this.periode === 'minggu') {
                    const diff = (now.getDay() + 6) % 7;
                    const senin = new Date(now);
                    senin.setDate(now.getDate() - diff);
                    return [senin.toISOString().slice(0, 10), todayIso];
                }
                if (this.periode === 'bulan') {
                    const awal = new Date(now.getFullYear(), now.getMonth(), 1);
                    return [awal.toISOString().slice(0, 10), todayIso];
                }
                return [this.mulai, this.selesai];
            },
            async muat() {
                if (this.periode === 'custom' && (!this.mulai || !this.selesai)) return;
                this.loading = true;
                const [mulai, selesai] = this.rentang();
                try {
                    const res = await fetch(`{{ route('guru.wali.rekap-alpa') }}?mulai=${mulai}&selesai=${selesai}`);
                    const data = await res.json();
                    this.mapel = data.mapel;
                    this.siswa = data.siswa;
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
