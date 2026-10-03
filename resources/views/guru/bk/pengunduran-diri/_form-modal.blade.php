{{--
    Modal tambah/edit Pengunduran Diri, 2 langkah (Data, Review). Tidak ada langkah lampiran --
    di aplikasi BK sumber upload berkas di modal ini tidak pernah benar-benar disimpan ke manapun.

    Variabel wajib: $formId, $siswas. Variabel opsional: $pengunduranDiri (untuk mode edit).
--}}
@php
    $mode = isset($pengunduranDiri) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.pengunduran-diri.update', $pengunduranDiri) : route('guru.bk.pengunduran-diri.store');
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-lg rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto"
        x-data="{
            step: 1,
            nama_ortu_wali: {{ \Illuminate\Support\Js::from(old('nama_ortu_wali', $pengunduranDiri->nama_ortu_wali ?? '')) }},
            alamat_ortu_wali: {{ \Illuminate\Support\Js::from(old('alamat_ortu_wali', $pengunduranDiri->alamat_ortu_wali ?? '')) }},
            alasan_pengunduran: {{ \Illuminate\Support\Js::from(old('alasan_pengunduran', $pengunduranDiri->alasan_pengunduran ?? '')) }},
            tanggal_pengunduran: {{ \Illuminate\Support\Js::from(old('tanggal_pengunduran', optional($pengunduranDiri->tanggal_pengunduran ?? null)->toDateString() ?? now()->toDateString())) }},
        }">
        <form method="POST" action="{{ $action }}" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Pengunduran Diri' : 'Pengunduran Diri Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="'Langkah ' + step + ' dari 2: ' + (step === 1 ? 'Data' : 'Review')"></p>
                <div class="flex gap-1.5">
                    <template x-for="s in [1, 2]" :key="s">
                        <div class="h-1.5 w-1/2 rounded-full" :class="step >= s ? 'bg-blue-600' : 'bg-slate-700'"></div>
                    </template>
                </div>
            </div>

            {{-- Langkah 1: Data --}}
            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $pengunduranDiri->siswa_id ?? null])
                    @if($mode === 'edit')
                        <p class="mt-1 text-xs text-slate-500">Siswa tidak dapat diganti setelah catatan dibuat.</p>
                    @endif
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal Pengunduran</label>
                    <input type="date" name="tanggal_pengunduran" required x-model="tanggal_pengunduran" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Nama Orang Tua / Wali</label>
                    <input type="text" name="nama_ortu_wali" required placeholder="Masukkan nama orang tua atau wali" x-model="nama_ortu_wali" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Alamat Orang Tua / Wali</label>
                    <textarea name="alamat_ortu_wali" rows="2" required placeholder="Masukkan alamat orang tua atau wali" x-model="alamat_ortu_wali" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Alasan Pengunduran Diri</label>
                    <textarea name="alasan_pengunduran" rows="4" required placeholder="Tuliskan alasan pengunduran diri siswa..." x-model="alasan_pengunduran" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                </div>
            </div>

            {{-- Langkah 2: Review --}}
            <div x-show="step === 2" x-cloak class="space-y-3">
                <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-3 text-sm text-blue-300">Pastikan data sudah benar sebelum menyimpan.</div>
                <dl class="divide-y divide-slate-700/60 rounded-lg border border-slate-700/60">
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Tanggal Pengunduran</dt><dd class="text-slate-100" x-text="tanggal_pengunduran"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Nama Orang Tua / Wali</dt><dd class="text-slate-100" x-text="nama_ortu_wali"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Alamat Orang Tua / Wali</dt><dd class="text-slate-100 whitespace-pre-line" x-text="alamat_ortu_wali"></dd></div>
                    <div class="px-3 py-2"><dt class="text-xs uppercase text-slate-500">Alasan Pengunduran Diri</dt><dd class="text-slate-100 whitespace-pre-line" x-text="alasan_pengunduran"></dd></div>
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
