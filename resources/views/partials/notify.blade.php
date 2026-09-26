@php
    $pesanError = collect();
    $judulError = null;

    if (session('error')) {
        $judulError = 'Terjadi Kesalahan';
        $pesanError->push(session('error'));
    }

    if ($errors->any()) {
        $judulError = 'Data Belum Lengkap atau Tidak Valid';
        foreach ($errors->getBags() as $bag) {
            $pesanError = $pesanError->merge($bag->all());
        }
    }

    $pesanError = $pesanError->unique()->values();
@endphp
@if($pesanError->isNotEmpty() && !request()->routeIs('login', 'ortu.login'))
    <div x-data="{ open: true }" x-show="open" @keydown.escape.window="open = false" style="z-index: 10001;"
        class="fixed inset-0 flex items-center justify-center p-4 bg-black/50" role="alertdialog" aria-modal="true">
        <div class="solid-panel w-full max-w-md rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl p-5" @click.outside="open = false">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-rose-500/10 text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h5 class="font-bold text-slate-100 m-0 mb-1">{{ $judulError }}</h5>
                    <p class="text-sm text-slate-400 mb-2">Mohon periksa kembali hal berikut:</p>
                    <ul class="list-disc pl-5 space-y-1 text-sm text-slate-100 m-0">
                        @foreach($pesanError as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" @click="open = false" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Mengerti</button>
            </div>
        </div>
    </div>
@endif
