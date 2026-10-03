{{--
    Modal tambah/edit AKPD. Langkah 1 = data siswa & tanggal, langkah 2-6 = 5 aspek (10 soal/aspek).
    Variabel wajib: $formId, $siswas. Variabel opsional: $akpd (model Akpd, untuk mode edit).
--}}
@php
    $mode = isset($akpd) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.asesmen.akpd.update', $akpd) : route('guru.bk.asesmen.akpd.store');
    $aspects = array_keys(\App\Models\Akpd::ASPECT_RANGES);
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-2xl rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            tanggal: {{ \Illuminate\Support\Js::from(old('tanggal', optional($akpd->tanggal ?? null)->toDateString() ?? now()->toDateString())) }},
            jawaban: {{ \Illuminate\Support\Js::from(old('jawaban', $akpd->jawaban ?? [])) }},
            countYa(start, end) {
                let n = 0;
                for (let i = start; i <= end; i++) { if (this.jawaban[i] === 'Ya') n++; }
                return n;
            }
        }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit AKPD' : 'AKPD Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 6: ' + (step === 1 ? 'Data Siswa' : 'Aspek ' + {{ \Illuminate\Support\Js::from($aspects) }}[step - 2])"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2, 3, 4, 5, 6]" :key="s">
                        <div class="h-1.5 w-1/6 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data Siswa --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $akpd->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required x-model="tanggal" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            {{-- Langkah 2-6: Aspek --}}
            @foreach($aspects as $i => $aspect)
                @php [$start, $end] = \App\Models\Akpd::ASPECT_RANGES[$aspect]; @endphp
                <div x-show="step === {{ $i + 2 }}" x-cloak class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-bold text-slate-100 m-0">{{ $aspect }}</h6>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400" x-text="'Ya ' + countYa({{ $start }}, {{ $end }}) + '/{{ $end - $start + 1 }}'"></span>
                    </div>
                    <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1">
                        @foreach(range($start, $end) as $no)
                            <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2.5">
                                <span class="text-sm text-slate-300 leading-5"><span class="font-bold text-slate-100 mr-1">{{ $no }}.</span>{{ \App\Models\Akpd::QUESTIONS[$no] }}</span>
                                <div class="flex gap-1.5 shrink-0">
                                    <button type="button" @click="jawaban[{{ $no }}] = 'Ya'" class="px-2.5 py-1 rounded-md text-xs font-bold border transition" :class="jawaban[{{ $no }}] === 'Ya' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-800 text-slate-400 border-slate-600'">Ya</button>
                                    <button type="button" @click="jawaban[{{ $no }}] = 'Tidak'" class="px-2.5 py-1 rounded-md text-xs font-bold border transition" :class="jawaban[{{ $no }}] === 'Tidak' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-800 text-slate-400 border-slate-600'">Tidak</button>
                                    <input type="hidden" :name="'jawaban[{{ $no }}]'" :value="jawaban[{{ $no }}] || ''">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step > 1" x-cloak @click="step--" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="button" x-show="step < 6" @click="step++" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya</button>
                <button type="submit" x-show="step === 6" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
