{{--
    Modal tambah/edit Sosiometri. Langkah 1 = siswa pengisi & tanggal, langkah 2-6 = 5 pertanyaan,
    masing-masing hingga 3 pilihan teman. Variabel wajib: $formId, $siswas. Opsional: $sosiometri (mode edit).
--}}
@php
    $mode = isset($sosiometri) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.asesmen.sosiometri.update', $sosiometri) : route('guru.bk.asesmen.sosiometri.store');
    $pertanyaan = \App\Models\Sosiometri::PERTANYAAN;
    $existing = [];
    foreach (array_keys($pertanyaan) as $key) {
        $picks = isset($sosiometri)
            ? $sosiometri->respons->where('pertanyaan', $key)->sortBy('urutan')->pluck('siswa_dipilih_id')->values()
            : collect();
        $existing[$key] = [$picks[0] ?? null, $picks[1] ?? null, $picks[2] ?? null];
    }
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-2xl rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto" x-data="{ step: 1 }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Sosiometri' : 'Sosiometri Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 6'"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2, 3, 4, 5, 6]" :key="s">
                        <div class="h-1.5 w-1/6 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Siswa Pengisi --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa Pengisi</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $sosiometri->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required value="{{ old('tanggal', optional($sosiometri->tanggal ?? null)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            {{-- Langkah 2-6: Pertanyaan --}}
            @foreach($pertanyaan as $key => $teks)
                @php $qi = array_search($key, array_keys($pertanyaan)); @endphp
                <div x-show="step === {{ $qi + 2 }}" x-cloak class="space-y-3">
                    <h6 class="font-bold text-slate-100 m-0">{{ $key }}</h6>
                    <p class="text-sm text-slate-300">{{ $teks }}</p>
                    @for($slot = 0; $slot < 3; $slot++)
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500">Pilihan {{ $slot + 1 }}</label>
                            @include('kesiswaan.partials.siswa-search', ['name' => "pilihan[{$key}][{$slot}]", 'siswas' => $siswas, 'default' => $existing[$key][$slot]])
                        </div>
                    @endfor
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
