{{--
    Modal tambah/edit DCM. Langkah 1 = data siswa & tanggal, langkah 2-13 = 12 bagian (A-L),
    langkah 14 = kesimpulan & catatan. Variabel wajib: $formId, $siswas. Opsional: $dcm (mode edit).
--}}
@php
    $mode = isset($dcm) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.asesmen.dcm.update', $dcm) : route('guru.bk.asesmen.dcm.store');
    $sections = \App\Models\Dcm::SECTIONS;
    $totalSteps = count($sections) + 2;
    $initJawaban = [];
    foreach (array_keys($sections) as $letter) { $initJawaban[$letter] = []; }
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-2xl rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            tanggal: {{ \Illuminate\Support\Js::from(old('tanggal', optional($dcm->tanggal ?? null)->toDateString() ?? now()->toDateString())) }},
            jawaban: {{ \Illuminate\Support\Js::from(old('jawaban', $dcm->jawaban ?? $initJawaban)) }},
        }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit DCM' : 'DCM Baru' }}</h5>
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
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $dcm->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required x-model="tanggal" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            {{-- Langkah 2-13: Bagian A-L --}}
            @foreach($sections as $letter => $title)
                <div x-show="step === {{ array_search($letter, array_keys($sections)) + 2 }}" x-cloak class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-bold text-slate-100 m-0">{{ $letter }}. {{ $title }}</h6>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400" x-text="(jawaban.{{ $letter }} || []).length + '/{{ count(\App\Models\Dcm::QUESTION_GROUPS[$letter]) }}'"></span>
                    </div>
                    <p class="text-xs text-slate-500 mb-2">Centang pernyataan yang sesuai dengan kondisi siswa.</p>
                    <div class="space-y-1.5 max-h-[50vh] overflow-y-auto pr-1">
                        @foreach(\App\Models\Dcm::QUESTION_GROUPS[$letter] as $kode => $text)
                            <label class="flex items-start gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 cursor-pointer">
                                <input type="checkbox" name="jawaban[{{ $letter }}][]" value="{{ $kode }}" x-model="jawaban.{{ $letter }}" class="mt-0.5 rounded border-slate-600 bg-slate-900">
                                <span class="text-sm text-slate-300 leading-5"><span class="font-bold text-slate-500 mr-1">{{ $kode }}</span>{{ $text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Langkah terakhir: Kesimpulan --}}
            <div x-show="step === {{ $totalSteps }}" x-cloak class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Kesimpulan <span class="font-normal">(opsional)</span></label>
                    <textarea name="kesimpulan" rows="3" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('kesimpulan', $dcm->kesimpulan ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Catatan <span class="font-normal">(opsional)</span></label>
                    <textarea name="catatan" rows="3" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('catatan', $dcm->catatan ?? '') }}</textarea>
                </div>
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
