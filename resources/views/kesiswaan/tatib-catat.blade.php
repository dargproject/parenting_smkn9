<div id="pane-kesiswaan-catat" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-1">Catat Pelanggaran</h4>
    <p class="text-slate-400 text-sm mb-4">Catat kejadian pelanggaran tata tertib yang dilakukan siswa.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 max-w-2xl">
        <form method="POST" action="{{ route('guru.pelanggaran.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
                f: { siswa_id: '{{ old('siswa_id', '') }}' },
                statusSemester: null,
                statusHarian: null,
                cekStatus() {
                    if (!this.f.siswa_id) { this.statusSemester = null; this.statusHarian = null; return; }
                    fetch(`/guru/api/siswa-poin/${this.f.siswa_id}`).then(r => r.json()).then(d => {
                        this.statusSemester = d.status_semester?.kode ? { ...d.status_semester, poin: d.poin_semester } : null;
                        this.statusHarian = d.status_harian ? { ...d.status_harian, poin: d.poin_harian } : null;
                    });
                },
                get tampilTindakan() { return !!this.statusSemester || !!this.statusHarian; }
             }" x-effect="cekStatus()">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Siswa</label>
                @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'bind' => 'f.siswa_id'])
                @error('siswa_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            @php
                $opsiJenis = $masterPelanggaranAktif->map(fn ($mp) => [
                    'id' => $mp->id,
                    'nama' => $mp->nama_pelanggaran,
                    'pasal' => $mp->pasal->nama ?? '-',
                    'jenis' => $mp->jenisPelanggaran->nama ?? '-',
                    'poin' => $mp->jenisPelanggaran->poin ?? 0,
                    'tindakanLangsung' => $mp->tindakan_langsung,
                ])->values();
            @endphp
            <div x-data="{
                    daftar: @js($opsiJenis), cari: '', pasal: '', open: false, pilihId: '{{ old('master_pelanggaran_id') }}',
                    get hasil() {
                        const q = this.cari.toLowerCase().trim();
                        return this.daftar.filter(d => (!this.pasal || d.pasal === this.pasal) && (!q || d.nama.toLowerCase().includes(q))).slice(0, 30);
                    },
                    get terpilih() { return this.daftar.find(d => String(d.id) === String(this.pilihId)); },
                    pilih(d) { this.pilihId = d.id; this.cari = ''; this.open = false; }
                 }" class="relative">
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Jenis Pelanggaran</label>
                <input type="hidden" name="master_pelanggaran_id" :value="pilihId">
                <div x-show="terpilih" style="display: none;" class="rounded-lg border border-emerald-500/40 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span><span x-text="terpilih?.nama"></span> <span class="text-xs text-slate-400" x-text="'· ' + terpilih?.jenis + ' · ' + terpilih?.poin + ' poin · ' + terpilih?.pasal"></span></span>
                        <button type="button" @click="pilihId = ''" class="text-slate-400 hover:text-slate-100" title="Ganti jenis"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <p x-show="terpilih?.tindakanLangsung" style="display: none;" class="mt-1.5 text-xs text-amber-400">
                        <i class="fa-solid fa-bolt mr-1"></i> Tindakan langsung: <span x-text="terpilih?.tindakanLangsung"></span>
                    </p>
                </div>
                <div x-show="!terpilih" class="space-y-2">
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select x-model="pasal" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 sm:w-56">
                            <option value="">Semua pasal</option>
                            @foreach($masterPelanggaranAktif->map(fn ($mp) => $mp->pasal->nama ?? '-')->unique()->values() as $namaPasal)<option value="{{ $namaPasal }}">{{ $namaPasal }}</option>@endforeach
                        </select>
                        <input type="text" x-model="cari" @focus="open = true" @click.outside="open = false" autocomplete="off" placeholder="Ketik nama pelanggaran..." class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                    </div>
                    <div class="max-h-56 overflow-y-auto rounded-lg border border-slate-700 bg-slate-900 shadow-lg" x-show="open || cari || pasal" style="display: none;">
                        <template x-for="d in hasil" :key="d.id">
                            <button type="button" @click="pilih(d)" class="block w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800">
                                <span x-text="d.nama"></span> <span class="text-xs text-slate-400" x-text="'· ' + d.jenis + ' · ' + d.poin + ' poin'"></span>
                                <span class="block text-xs text-slate-500" x-text="d.pasal"></span>
                            </button>
                        </template>
                        <p x-show="!hasil.length" class="px-3 py-2 text-sm text-slate-400">Tidak ada jenis pelanggaran yang cocok.</p>
                    </div>
                </div>
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

            <div>
                <x-camera-upload-field name="foto" label="Foto Bukti (opsional)" />
            </div>

            <div x-show="statusHarian" style="display: none;" class="rounded-lg border border-amber-500/30 bg-amber-500/5 p-4">
                <p class="text-sm font-semibold text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    <span x-text="statusHarian?.label"></span> &mdash; siswa ini sudah <span x-text="statusHarian?.poin"></span> poin pelanggaran <u>hari ini</u>.
                </p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-slate-300">
                    <template x-for="t in (statusHarian?.tindakan || [])" :key="t"><li x-text="t"></li></template>
                </ul>
            </div>

            <div x-show="statusSemester" style="display: none;" class="rounded-lg border border-rose-500/30 bg-rose-500/5 p-4">
                <p class="text-sm font-semibold text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    <span x-text="statusSemester?.label"></span> &mdash; siswa ini sudah <span x-text="statusSemester?.poin"></span> poin pelanggaran tahun ajaran ini.
                </p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-slate-300">
                    <template x-for="t in (statusSemester?.tindakan || [])" :key="t"><li x-text="t"></li></template>
                </ul>
            </div>

            <div x-show="tampilTindakan" style="display: none;" class="rounded-lg border border-slate-600 bg-slate-900/60 p-4">
                <p class="mb-3 text-sm text-slate-300">Catat tindakan yang benar-benar diberikan (opsional).</p>
                <div class="mb-3">
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Tindakan</label>
                    <textarea name="tindakan" rows="2" placeholder="Contoh: Menulis istighfar 25 kali" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('tindakan') }}</textarea>
                    @error('tindakan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <x-camera-upload-field name="foto_tindakan" label="Foto Hasil Tindakan (opsional)" />
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Pelanggaran</button>
            </div>
        </form>
    </div>
</div>
