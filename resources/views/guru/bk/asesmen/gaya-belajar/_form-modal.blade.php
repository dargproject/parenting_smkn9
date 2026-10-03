{{--
    Modal tambah/edit Gaya Belajar. Langkah 1 = data siswa & tanggal, langkah 2-4 = checklist
    pernyataan per kelompok (Visual/Auditorial/Kinestetik), langkah 5 = hasil & catatan.
    Variabel wajib: $formId, $siswas. Variabel opsional: $gayaBelajar (model, untuk mode edit).
--}}
@php
    $mode = isset($gayaBelajar) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.asesmen.gaya-belajar.update', $gayaBelajar) : route('guru.bk.asesmen.gaya-belajar.store');
    $groups = array_keys(\App\Models\GayaBelajar::QUESTION_GROUPS);
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-2xl rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            tanggal: {{ \Illuminate\Support\Js::from(old('tanggal', optional($gayaBelajar->tanggal ?? null)->toDateString() ?? now()->toDateString())) }},
            jawaban: {{ \Illuminate\Support\Js::from(old('jawaban', $gayaBelajar->jawaban ?? ['Visual' => [], 'Auditorial' => [], 'Kinestetik' => []])) }},
            hasil: {{ \Illuminate\Support\Js::from(old('hasil', $gayaBelajar->hasil ?? '')) }},
            get dominan() {
                const scores = { Visual: (this.jawaban.Visual || []).length, Auditorial: (this.jawaban.Auditorial || []).length, Kinestetik: (this.jawaban.Kinestetik || []).length };
                let best = null;
                for (const k in scores) { if (scores[k] > 0 && (best === null || scores[k] > scores[best])) best = k; }
                return best;
            }
        }"
        x-init="$watch('jawaban', () => { if (!hasil) hasil = dominan || ''; })">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Gaya Belajar' : 'Gaya Belajar Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 5'"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2, 3, 4, 5]" :key="s">
                        <div class="h-1.5 w-1/5 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data Siswa --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $gayaBelajar->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required x-model="tanggal" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            {{-- Langkah 2-4: Kelompok Pernyataan --}}
            @foreach($groups as $gi => $group)
                <div x-show="step === {{ $gi + 2 }}" x-cloak class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-bold text-slate-100 m-0">{{ $group }}</h6>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400" x-text="(jawaban.{{ $group }} || []).length + '/{{ count(\App\Models\GayaBelajar::QUESTION_GROUPS[$group]) }} dipilih'"></span>
                    </div>
                    <p class="text-xs text-slate-500 mb-2">Centang pernyataan yang sesuai dengan siswa.</p>
                    <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1">
                        @foreach(\App\Models\GayaBelajar::QUESTION_GROUPS[$group] as $index => $text)
                            <label class="flex items-start gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2.5 cursor-pointer">
                                <input type="checkbox" name="jawaban[{{ $group }}][]" value="{{ $index }}" x-model="jawaban.{{ $group }}" class="mt-0.5 rounded border-slate-600 bg-slate-900">
                                <span class="text-sm text-slate-300 leading-5">{{ $text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Langkah 5: Hasil & Catatan --}}
            <div x-show="step === 5" x-cloak class="space-y-3">
                <div class="rounded-lg border border-slate-700/60 bg-slate-900 p-3 text-sm text-slate-300">
                    <p class="m-0">Visual: <span class="font-bold text-slate-100" x-text="(jawaban.Visual || []).length"></span> &middot; Auditorial: <span class="font-bold text-slate-100" x-text="(jawaban.Auditorial || []).length"></span> &middot; Kinestetik: <span class="font-bold text-slate-100" x-text="(jawaban.Kinestetik || []).length"></span></p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Hasil (gaya belajar dominan)</label>
                    <select name="hasil" x-model="hasil" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">- Pilih -</option>
                        <option value="Visual">Visual</option>
                        <option value="Auditorial">Auditorial</option>
                        <option value="Kinestetik">Kinestetik</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Otomatis terisi skor tertinggi, bisa diubah manual.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Faktor Penghambat <span class="font-normal">(opsional)</span></label>
                    <textarea name="faktor_penghambat" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('faktor_penghambat', $gayaBelajar->faktor_penghambat ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Faktor Pendukung <span class="font-normal">(opsional)</span></label>
                    <textarea name="faktor_pendukung" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('faktor_pendukung', $gayaBelajar->faktor_pendukung ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Catatan <span class="font-normal">(opsional)</span></label>
                    <textarea name="catatan" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('catatan', $gayaBelajar->catatan ?? '') }}</textarea>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">
                    <input type="checkbox" name="tampilkan_ke_ortu" value="1" @checked(old('tampilkan_ke_ortu', $gayaBelajar->tampilkan_ke_ortu ?? false)) class="mt-0.5 rounded border-slate-600 bg-slate-900">
                    <span>Tampilkan hasil Gaya Belajar ini ke dashboard orang tua.</span>
                </label>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step > 1" x-cloak @click="step--" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="button" x-show="step < 5" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === 5" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
