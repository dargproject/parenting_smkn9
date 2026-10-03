{{--
    Modal tambah/edit Kunjungan Rumah, 3 langkah (Data, Review, Lampiran).
    Variabel wajib: $formId (string unik), $siswas.
    Variabel opsional: $kunjunganRumah (model KunjunganRumah, untuk mode edit).
--}}
@php
    $mode = isset($kunjunganRumah) ? 'edit' : 'create';
    $kasusBk = $kunjunganRumah->kasusBk ?? null;
    $action = $mode === 'edit' ? route('guru.bk.kunjungan-rumah.update', $kunjunganRumah) : route('guru.bk.kunjungan-rumah.store');
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-lg rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            judul: {{ \Illuminate\Support\Js::from(old('judul', $kasusBk->judul ?? '')) }},
            deskripsi: {{ \Illuminate\Support\Js::from(old('deskripsi', $kasusBk->deskripsi ?? '')) }},
            tindak_lanjut: {{ \Illuminate\Support\Js::from(old('tindak_lanjut', $kasusBk->tindak_lanjut ?? '')) }},
            tanggal_kunjungan: {{ \Illuminate\Support\Js::from(old('tanggal_kunjungan', optional($kunjunganRumah->tanggal_kunjungan ?? null)->toDateString() ?? now()->toDateString())) }},
            status: {{ \Illuminate\Support\Js::from(old('status', $kunjunganRumah->status ?? 'diproses')) }},
        }">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Kunjungan Rumah' : 'Kunjungan Rumah Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 3: ' + (step === 1 ? 'Data Kunjungan' : step === 2 ? 'Review' : 'Lampiran')"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2, 3]" :key="s">
                        <div class="h-1.5 w-1/3 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $kasusBk->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan" required x-model="tanggal_kunjungan" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Status</label>
                    <select name="status" required x-model="status" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="diproses">Diproses</option>
                        <option value="ditunda">Ditunda</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Penanganan</label>
                    <textarea name="judul" rows="2" required placeholder="Tuliskan penanganan yang dilakukan..." x-model="judul" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Uraian Masalah</label>
                    <textarea name="deskripsi" rows="3" required placeholder="Tuliskan uraian masalah yang ditemukan..." x-model="deskripsi" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tindak Lanjut <span class="font-normal">(opsional)</span></label>
                    <textarea name="tindak_lanjut" rows="2" placeholder="Tuliskan tindak lanjut..." x-model="tindak_lanjut" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">
                    <input type="checkbox" name="tampilkan_ke_ortu" value="1" @checked(old('tampilkan_ke_ortu', $kunjunganRumah->tampilkan_ke_ortu ?? false)) class="mt-0.5 rounded border-slate-600 bg-slate-900">
                    <span>Tampilkan jadwal kunjungan ini (tanggal &amp; status saja) ke dashboard orang tua.</span>
                </label>
            </div>

            {{-- Langkah 2: Review --}}
            <div x-show="step === 2" x-cloak class="space-y-3">
                <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-3 text-sm text-blue-300">Pastikan data sudah benar sebelum menyimpan.</div>
                <dl class="divide-y divide-slate-700/60 rounded-lg border border-slate-700/60">
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tanggal Kunjungan</dt><dd class="text-slate-100" x-text="tanggal_kunjungan"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Status</dt><dd class="text-slate-100" x-text="status"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Penanganan</dt><dd class="text-slate-100 whitespace-pre-line" x-text="judul"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Uraian Masalah</dt><dd class="text-slate-100 whitespace-pre-line" x-text="deskripsi"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tindak Lanjut</dt><dd class="text-slate-100 whitespace-pre-line" x-text="tindak_lanjut || '-'"></dd></div>
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
                <button type="button" x-show="step < 3" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === 3" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
