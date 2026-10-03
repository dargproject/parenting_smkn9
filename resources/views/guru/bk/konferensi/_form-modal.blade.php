{{--
    Modal tambah/edit Konferensi Kasus, 3 langkah (Data+Peserta, Review, Lampiran).
    Peserta bersifat dinamis (nama + peran bebas, bukan dari daftar siswa -- bisa wali kelas,
    kepala sekolah, orang tua, dll), dikirim sebagai peserta[i][nama_peserta]/[peran_peserta].

    Mode create: $formId, $kasusOptions.
    Mode edit: tambahkan $konferensi (kasus_bk_id tidak bisa diubah saat edit).
--}}
@php
    $mode = isset($konferensi) ? 'edit' : 'create';
    $kasusBk = $konferensi->kasusBk ?? null;
    $action = $mode === 'edit' ? route('guru.bk.konferensi.update', $konferensi) : route('guru.bk.konferensi.store');
    $kasusJson = ($mode === 'create' ? $kasusOptions : collect())->map(fn ($k) => [
        'id' => $k->id,
        'siswa' => $k->siswa->nama ?? '-',
        'judul' => $k->judul,
        'kelas' => $k->siswa->kelas->nama_kelas ?? '-',
    ]);
    $pesertaAwal = $mode === 'edit' ? $konferensi->pesertas->map(fn ($p) => ['nama_peserta' => $p->nama_peserta, 'peran_peserta' => $p->peran_peserta])->values() : collect();
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-lg rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            kasusList: {{ \Illuminate\Support\Js::from($kasusJson) }},
            kasusId: {{ \Illuminate\Support\Js::from(old('kasus_bk_id', $kasusBk->id ?? '')) }},
            query: '',
            showPicker: false,
            tanggal_konferensi: {{ \Illuminate\Support\Js::from(old('tanggal_konferensi', optional($konferensi->tanggal_konferensi ?? null)->toDateString() ?? now()->toDateString())) }},
            tempat_pertemuan: {{ \Illuminate\Support\Js::from(old('tempat_pertemuan', $konferensi->tempat_pertemuan ?? '')) }},
            pesertas: {{ \Illuminate\Support\Js::from(old('peserta', $pesertaAwal)) }},
            namaBaru: '', peranBaru: 'Guru BK',
            get filtered() {
                const q = this.query.toLowerCase().trim();
                if (!q) return this.kasusList.slice(0, 8);
                return this.kasusList.filter(k => k.siswa.toLowerCase().includes(q) || k.judul.toLowerCase().includes(q)).slice(0, 8);
            },
            get selectedKasus() { return this.kasusList.find(k => String(k.id) === String(this.kasusId)); },
            tambahPeserta() {
                if (this.namaBaru.trim() === '') return;
                this.pesertas.push({ nama_peserta: this.namaBaru.trim(), peran_peserta: this.peranBaru });
                this.namaBaru = '';
            },
            hapusPeserta(i) { this.pesertas.splice(i, 1); },
        }">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
                <input type="hidden" name="kasus_bk_id" value="{{ $kasusBk->id }}">
            @else
                <input type="hidden" name="kasus_bk_id" :value="kasusId">
            @endif
            <template x-for="(p, i) in pesertas" :key="i">
                <span>
                    <input type="hidden" :name="'peserta[' + i + '][nama_peserta]'" :value="p.nama_peserta">
                    <input type="hidden" :name="'peserta[' + i + '][peran_peserta]'" :value="p.peran_peserta">
                </span>
            </template>

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Konferensi Kasus' : 'Konferensi Kasus Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 3: ' + (step === 1 ? 'Data & Peserta' : step === 2 ? 'Review' : 'Lampiran')"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2, 3]" :key="s">
                        <div class="h-1.5 w-1/3 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data + Peserta --}}
            <div x-show="step === 1" class="space-y-3">
                @if($mode === 'create')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Kasus</label>
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
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Hanya kasus milik Anda yang belum selesai.</p>
                    </div>
                @else
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Kasus</label>
                        <div class="rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">{{ $kasusBk->siswa->nama ?? '-' }} &middot; {{ $kasusBk->judul }}</div>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Peserta Konferensi</label>
                    <div class="flex flex-col gap-2 mb-2" x-show="pesertas.length" style="display: none;">
                        <template x-for="(p, i) in pesertas" :key="i">
                            <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm">
                                <span class="text-slate-100"><span x-text="p.nama_peserta"></span> <span class="text-xs text-blue-400" x-text="'(' + p.peran_peserta + ')'"></span></span>
                                <button type="button" @click="hapusPeserta(i)" class="text-slate-400 hover:text-rose-400"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </template>
                    </div>
                    <div class="rounded-lg border border-dashed border-slate-600 p-3">
                        <div class="flex gap-2">
                            <input type="text" x-model="namaBaru" @keydown.enter.prevent="tambahPeserta()" placeholder="Nama peserta..." class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                            <select x-model="peranBaru" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                                <option value="Guru BK">Guru BK</option>
                                <option value="Wali Kelas">Wali Kelas</option>
                                <option value="Kepala Sekolah">Kepala Sekolah</option>
                                <option value="Orang Tua">Orang Tua</option>
                                <option value="Siswa">Siswa</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <button type="button" @click="tambahPeserta()" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">Tambah</button>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500">Tekan Enter atau klik Tambah.</p>
                    </div>
                    <p class="mt-1 text-xs" :class="pesertas.length ? 'text-slate-500' : 'text-rose-400'" x-text="pesertas.length + ' peserta ditambahkan' + (pesertas.length ? '' : ' -- minimal 1 peserta')"></p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal Konferensi</label>
                    <input type="date" name="tanggal_konferensi" required x-model="tanggal_konferensi" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tempat Pertemuan <span class="font-normal">(opsional)</span></label>
                    <input type="text" name="tempat_pertemuan" x-model="tempat_pertemuan" placeholder="Misal: Ruang BK, Aula, dll." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
            </div>

            {{-- Langkah 2: Review --}}
            <div x-show="step === 2" x-cloak class="space-y-3">
                <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-3 text-sm text-blue-300">Pastikan data sudah benar sebelum menyimpan.</div>
                <dl class="divide-y divide-slate-700/60 rounded-lg border border-slate-700/60">
                    <div class="px-3 py-2">
                        <dt class="text-xs uppercase text-slate-500">Peserta (<span x-text="pesertas.length"></span>)</dt>
                        <dd class="text-slate-100 mt-1">
                            <template x-for="p in pesertas" :key="p.nama_peserta + p.peran_peserta">
                                <div class="text-sm" x-text="p.nama_peserta + ' (' + p.peran_peserta + ')'"></div>
                            </template>
                        </dd>
                    </div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tanggal Konferensi</dt><dd class="text-slate-100" x-text="tanggal_konferensi"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tempat Pertemuan</dt><dd class="text-slate-100" x-text="tempat_pertemuan || '-'"></dd></div>
                </dl>
            </div>

            {{-- Langkah 3: Lampiran --}}
            <div x-show="step === 3" x-cloak class="space-y-3">
                @if($mode === 'edit' && $kasusBk->lampiran->isNotEmpty())
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-400">Lampiran Tersimpan</label>
                        <div class="flex flex-col gap-2">
                            @foreach($kasusBk->lampiran as $lampiran)
                                <label class="flex items-center justify-between gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($lampiran->path_file) }}" target="_blank" class="truncate text-blue-400 hover:text-blue-300">{{ $lampiran->nama_file }}</a>
                                    <span class="flex items-center gap-1.5 text-xs text-rose-400 whitespace-nowrap">
                                        <input type="checkbox" name="lampiran_dihapus[]" value="{{ $lampiran->id }}" class="rounded border-slate-600 bg-slate-900"> Hapus
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tambah Lampiran <span class="font-normal">(maks. 5 file, 12MB/file: pdf, jpg, png, doc, docx)</span></label>
                    <input type="file" name="lampiran[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step > 1" x-cloak @click="step--" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="button" x-show="step < 3" :disabled="step === 1 && pesertas.length === 0" :class="step === 1 && pesertas.length === 0 ? 'opacity-50 cursor-not-allowed' : ''" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === 3" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
