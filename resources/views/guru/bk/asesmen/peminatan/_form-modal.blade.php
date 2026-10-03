{{--
    Modal tambah/edit Tes Bakat Minat. Langkah 1 = data siswa & tanggal, langkah 2-9 = 8 bagian
    kecerdasan majemuk, langkah 10 = hasil & catatan. Variabel wajib: $formId, $siswas. Opsional: $peminatan.
--}}
@php
    $mode = isset($peminatan) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.asesmen.peminatan.update', $peminatan) : route('guru.bk.asesmen.peminatan.store');
    $sections = \App\Models\Peminatan::SECTIONS;
    $totalSteps = count($sections) + 2;
    $initJawaban = [];
    foreach ($sections as $section) { $initJawaban[$section] = []; }
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-2xl rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            tanggal: {{ \Illuminate\Support\Js::from(old('tanggal', optional($peminatan->tanggal ?? null)->toDateString() ?? now()->toDateString())) }},
            jawaban: {{ \Illuminate\Support\Js::from(old('jawaban', $peminatan->jawaban ?? $initJawaban)) }},
            get dominan() {
                const sections = {{ \Illuminate\Support\Js::from($sections) }};
                const scored = sections.map(s => ({ s, n: (this.jawaban[s] || []).length })).filter(x => x.n > 0);
                scored.sort((a, b) => b.n - a.n);
                return scored.slice(0, 3).map(x => x.s);
            }
        }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Tes Bakat Minat' : 'Tes Bakat Minat Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari {{ $totalSteps }}'"></p>
                <div class="h-1.5 w-full rounded-full bg-slate-700 overflow-hidden"><div class="h-full bg-blue-600 transition-all" :style="'width: ' + (step / {{ $totalSteps }} * 100) + '%'"></div></div>
            </div>

            {{-- Langkah 1: Data Siswa --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $peminatan->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required x-model="tanggal" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            {{-- Langkah 2-9: Bagian kecerdasan --}}
            @foreach($sections as $si => $section)
                <div x-show="step === {{ $si + 2 }}" x-cloak class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-bold text-slate-100 m-0">{{ $section }}</h6>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400" x-text="(jawaban['{{ $section }}'] || []).length + '/{{ count(\App\Models\Peminatan::QUESTION_GROUPS[$section]) }}'"></span>
                    </div>
                    <div class="space-y-1.5 max-h-[50vh] overflow-y-auto pr-1">
                        @foreach(\App\Models\Peminatan::QUESTION_GROUPS[$section] as $kode => $text)
                            <label class="flex items-start gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2.5 cursor-pointer">
                                <input type="checkbox" name="jawaban[{{ $section }}][]" value="{{ $kode }}" x-model="jawaban['{{ $section }}']" class="mt-0.5 rounded border-slate-600 bg-slate-900">
                                <span class="text-sm text-slate-300 leading-5">{{ $text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Langkah terakhir: Hasil & Catatan --}}
            <div x-show="step === {{ $totalSteps }}" x-cloak class="space-y-3">
                <div class="rounded-lg border border-slate-700/60 bg-slate-900 p-3 text-sm text-slate-300">
                    <p class="m-0">3 kecerdasan dominan: <span class="font-bold text-blue-400" x-text="dominan.join(', ') || '-'"></span></p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Pilihan 1 <span class="font-normal">(opsional, override otomatis)</span></label>
                    <input type="text" name="pilihan1" value="{{ old('pilihan1', $peminatan->pilihan1 ?? '') }}" placeholder="Otomatis dari kecerdasan dominan tertinggi" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Pilihan 2 <span class="font-normal">(opsional)</span></label>
                    <input type="text" name="pilihan2" value="{{ old('pilihan2', $peminatan->pilihan2 ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Pilihan 3 <span class="font-normal">(opsional)</span></label>
                    <input type="text" name="pilihan3" value="{{ old('pilihan3', $peminatan->pilihan3 ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Catatan <span class="font-normal">(opsional)</span></label>
                    <textarea name="catatan" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('catatan', $peminatan->catatan ?? '') }}</textarea>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">
                    <input type="checkbox" name="tampilkan_ke_ortu" value="1" @checked(old('tampilkan_ke_ortu', $peminatan->tampilkan_ke_ortu ?? false)) class="mt-0.5 rounded border-slate-600 bg-slate-900">
                    <span>Tampilkan hasil Tes Bakat Minat ini ke dashboard orang tua.</span>
                </label>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step > 1" x-cloak @click="step--" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="button" x-show="step < {{ $totalSteps }}" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === {{ $totalSteps }}" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
