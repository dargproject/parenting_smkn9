{{--
    Modal tambah/edit Alih Tangan Kasus, 2 langkah (Data, Review). Tidak ada langkah lampiran --
    di aplikasi BK sumber upload berkas di modal ini tidak pernah benar-benar disimpan ke manapun,
    jadi sengaja tidak diikutkan di sini.

    Mode create: $formId, $kasusOptions, $guruBkOptions.
    Mode edit: tambahkan $alihTangan, $guruBkOptions (kasus_bk_id tidak bisa diubah saat edit).
--}}
@php
    $mode = isset($alihTangan) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.alih-tangan.update', $alihTangan) : route('guru.bk.alih-tangan.store');
    $kasusJson = ($mode === 'create' ? $kasusOptions : collect())->map(fn ($k) => [
        'id' => $k->id,
        'siswa' => $k->siswa->nama ?? '-',
        'judul' => $k->judul,
        'kelas' => $k->siswa->kelas->nama_kelas ?? '-',
        'prioritas' => $k->prioritas,
    ]);
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-lg rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            kasusList: {{ \Illuminate\Support\Js::from($kasusJson) }},
            kasusId: {{ \Illuminate\Support\Js::from(old('kasus_bk_id', $alihTangan->kasus_bk_id ?? '')) }},
            query: '',
            showPicker: false,
            tanggal_alih: {{ \Illuminate\Support\Js::from(old('tanggal_alih', optional($alihTangan->tanggal_alih ?? null)->toDateString() ?? now()->toDateString())) }},
            konselor_tujuan_id: {{ \Illuminate\Support\Js::from(old('konselor_tujuan_id', $alihTangan->konselor_tujuan_id ?? '')) }},
            alasan_alih: {{ \Illuminate\Support\Js::from(old('alasan_alih', $alihTangan->alasan_alih ?? '')) }},
            tindak_lanjut: {{ \Illuminate\Support\Js::from(old('tindak_lanjut', $alihTangan->tindak_lanjut ?? '')) }},
            get filtered() {
                const q = this.query.toLowerCase().trim();
                if (!q) return this.kasusList.slice(0, 8);
                return this.kasusList.filter(k => k.siswa.toLowerCase().includes(q) || k.judul.toLowerCase().includes(q)).slice(0, 8);
            },
            get selectedKasus() { return this.kasusList.find(k => String(k.id) === String(this.kasusId)); },
        }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
                <input type="hidden" name="kasus_bk_id" value="{{ $alihTangan->kasus_bk_id }}">
            @else
                <input type="hidden" name="kasus_bk_id" :value="kasusId">
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Alih Tangan Kasus' : 'Alih Tangan Kasus Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 2: ' + (step === 1 ? 'Data Alih Tangan' : 'Review')"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2]" :key="s">
                        <div class="h-1.5 w-1/2 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data --}}
            <div x-show="step === 1" class="space-y-3">
                @if($mode === 'create')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Kasus yang Dialihkan</label>
                        <div x-show="selectedKasus" style="display: none;" class="rounded-lg border border-emerald-500/40 bg-slate-900 px-3 py-2 text-sm text-slate-100 mb-2 flex items-center justify-between">
                            <span x-text="selectedKasus ? (selectedKasus.siswa + ' -- ' + selectedKasus.judul) : ''"></span>
                            <button type="button" @click="kasusId = ''" class="text-slate-400 hover:text-rose-400"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div x-show="!selectedKasus" class="relative" style="display: none;">
                            <input type="text" x-model="query" @focus="showPicker = true" @click.outside="showPicker = false" autocomplete="off"
                                placeholder="Ketik nama siswa atau judul kasus..."
                                class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                            <div x-show="showPicker && filtered.length" style="display: none;" class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-700 bg-slate-900 shadow-lg">
                                <template x-for="k in filtered" :key="k.id">
                                    <button type="button" @click="kasusId = k.id; showPicker = false" class="block w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800">
                                        <span x-text="k.siswa"></span> <span class="text-xs text-slate-400" x-text="'· ' + k.judul + ' · ' + k.kelas"></span>
                                    </button>
                                </template>
                            </div>
                            <div x-show="showPicker && !filtered.length" style="display: none;" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-400 shadow-lg">Tidak ada kasus terbuka yang bisa dialihkan.</div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Hanya kasus milik Anda yang belum selesai yang bisa dialihkan.</p>
                    </div>
                @else
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Kasus yang Dialihkan</label>
                        <div class="rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">{{ $alihTangan->kasusBk->siswa->nama ?? '-' }} &middot; {{ $alihTangan->kasusBk->judul }}</div>
                        <p class="mt-1 text-xs text-slate-500">Kasus tidak dapat diganti setelah catatan alih tangan dibuat.</p>
                    </div>
                @endif
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal Alih Tangan</label>
                    <input type="date" name="tanggal_alih" required x-model="tanggal_alih" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Guru BK Penerima</label>
                    <select name="konselor_tujuan_id" required x-model="konselor_tujuan_id" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">- Pilih Guru BK Penerima -</option>
                        @foreach($guruBkOptions as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Alasan Alih Tangan <span class="font-normal">(opsional)</span></label>
                    <textarea name="alasan_alih" rows="2" placeholder="Contoh: Membutuhkan penanganan lebih lanjut dari konselor lain..." x-model="alasan_alih" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tindak Lanjut <span class="font-normal">(opsional)</span></label>
                    <textarea name="tindak_lanjut" rows="2" x-model="tindak_lanjut" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
            </div>

            {{-- Langkah 2: Review --}}
            <div x-show="step === 2" x-cloak class="space-y-3">
                <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-300"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Setelah disimpan, kepemilikan kasus akan langsung berpindah ke guru BK penerima.</div>
                <dl class="divide-y divide-slate-700/60 rounded-lg border border-slate-700/60">
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tanggal Alih Tangan</dt><dd class="text-slate-100" x-text="tanggal_alih"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Alasan</dt><dd class="text-slate-100 whitespace-pre-line" x-text="alasan_alih || '-'"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tindak Lanjut</dt><dd class="text-slate-100 whitespace-pre-line" x-text="tindak_lanjut || '-'"></dd></div>
                </dl>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step > 1" x-cloak @click="step--" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="button" x-show="step < 2" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === 2" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
