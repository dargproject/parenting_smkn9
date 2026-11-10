@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">PKL Bimbingan Saya</h4>
        <p class="text-slate-400 text-sm">Atur periode PKL dan isi catatan perkembangan siswa yang menjadi bimbingan Anda.</p>
    </div>

    @include('admin.partials.flash')

    @if($items->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai pembimbing PKL siswa manapun. Hubungi Admin.</p>
    @else
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6">
            <h5 class="text-base font-bold text-slate-100 m-0 mb-1">Periode PKL untuk Semua Siswa Bimbingan</h5>
            <p class="text-sm text-slate-400 mb-3">Cukup diisi sekali, berlaku untuk seluruh siswa yang Anda bimbing pada tahun ajaran aktif.</p>
            <form method="POST" action="{{ route('guru.pkl.periode') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $items->first()->tanggal_mulai?->toDateString()) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $items->first()->tanggal_selesai?->toDateString()) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Periode untuk Semua</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            @foreach($items as $item)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h5 class="text-lg font-bold text-slate-100 m-0">{{ $item->siswa->nama ?? '-' }}</h5>
                            <p class="text-sm text-slate-400 m-0">{{ $item->siswa->kelas->nama_kelas ?? '-' }} &middot; {{ $item->tahunAjaran->nama ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-slate-300 block">{{ $item->nama_iduka }}</span>
                            <span class="text-xs text-slate-500">
                                @if($item->tanggal_mulai || $item->tanggal_selesai)
                                    {{ $item->tanggal_mulai?->translatedFormat('d M Y') ?? '?' }} &ndash; {{ $item->tanggal_selesai?->translatedFormat('d M Y') ?? '?' }}
                                @else
                                    Periode belum diisi
                                @endif
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('guru.pkl.update', $item) }}" class="space-y-3" data-pkl-catatan-form>
                        @csrf
                        @method('PUT')

                        @foreach([
                            'etika' => 'Perkembangan Etika, Kedisiplinan & Sikap',
                            'kemandirian' => 'Perkembangan Kemandirian & Problem Solving',
                            'administrasi' => 'Perkembangan Administrasi & Tanggung Jawab (Jurnal PKL)',
                        ] as $kunci => $label)
                            <div class="rounded-xl border border-slate-700/50 p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <label class="text-xs font-semibold text-slate-300">{{ $label }}</label>
                                    <select name="predikat_{{ $kunci }}" data-predikat="{{ $kunci }}" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1 text-xs text-slate-800 dark:text-slate-100">
                                        <option value="">Pilih Predikat</option>
                                        @foreach($predikatOptions as $opsi)
                                            <option value="{{ $opsi }}" @selected(old("predikat_{$kunci}", $item->{"predikat_{$kunci}"}) === $opsi)>{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <textarea name="catatan_{{ $kunci }}" data-catatan="{{ $kunci }}" rows="2" placeholder="Pilih predikat untuk mengisi catatan otomatis, lalu sunting bila perlu." class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-slate-800 dark:text-slate-100">{{ old("catatan_{$kunci}", $item->{"catatan_{$kunci}"}) }}</textarea>
                            </div>
                        @endforeach

                        <div class="rounded-xl border border-slate-700/50 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <label class="text-xs font-semibold text-slate-300">Progres Laporan PKL (%)</label>
                                <select name="predikat_progres" data-predikat="progres" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1 text-xs text-slate-800 dark:text-slate-100">
                                    <option value="">Pilih Persentase</option>
                                    @foreach($progresOptions as $opsi)
                                        <option value="{{ $opsi }}" @selected(old('predikat_progres', $item->predikat_progres) === $opsi)>{{ $opsi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="catatan_progres" data-catatan="progres" rows="2" placeholder="Pilih persentase untuk mengisi catatan otomatis, lalu sunting bila perlu." class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-slate-800 dark:text-slate-100">{{ old('catatan_progres', $item->catatan_progres) }}</textarea>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Catatan Tambahan (opsional)</label>
                            <textarea name="catatan" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-slate-800 dark:text-slate-100">{{ old('catatan', $item->catatan) }}</textarea>
                        </div>

                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Catatan Perkembangan</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    (function () {
        var templateCatatan = @json($templateCatatan);

        document.querySelectorAll('[data-pkl-catatan-form]').forEach(function (form) {
            form.querySelectorAll('select[data-predikat]').forEach(function (select) {
                select.addEventListener('change', function () {
                    var kunci = select.getAttribute('data-predikat');
                    var textarea = form.querySelector('textarea[data-catatan="' + kunci + '"]');
                    var template = (templateCatatan[kunci] || {})[select.value];
                    if (textarea && template) {
                        textarea.value = template;
                    }
                });
            });
        });
    })();
</script>
@endsection
