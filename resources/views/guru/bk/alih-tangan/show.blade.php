@extends('layouts.app')

@section('content')
@php $kasusBk = $alihTangan->kasusBk; @endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.alih-tangan.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Alih Tangan Kasus</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">Alih Tangan &middot; {{ $kasusBk->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $kasusBk->judul }} &middot; {{ $alihTangan->tanggal_alih->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-alih-tangan').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.alih-tangan.destroy', $alihTangan) }}" onsubmit="return confirm('Hapus catatan alih tangan ini? Kepemilikan kasus TIDAK akan dikembalikan ke konselor asal.')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Detail Alih Tangan</h6>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-400">Dari</dt><dd class="text-slate-100 font-semibold">{{ $alihTangan->konselorAsal->nama ?? '-' }}</dd></div>
                    <div><dt class="text-slate-400">Ke</dt><dd class="text-slate-100 font-semibold">{{ $alihTangan->konselorTujuan->nama ?? '-' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Alasan Alih Tangan</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $alihTangan->alasan_alih ?: '-' }}</dd>
                </div>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Tindak Lanjut</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $alihTangan->tindak_lanjut ?: '-' }}</dd>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Kasus Terkait</h6>
                <p class="text-slate-400 text-sm mb-1">Status saat ini: <span class="font-semibold text-slate-100">{{ ucfirst($kasusBk->status) }}</span></p>
                <p class="text-slate-400 text-sm mb-2">Konselor saat ini: <span class="font-semibold text-slate-100">{{ $kasusBk->konselor->nama ?? '-' }}</span></p>
                <a href="{{ route('guru.bk.kasus.show', $kasusBk) }}" class="text-sm text-blue-400 hover:text-blue-300">Lihat detail kasus <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>
    </div>
</div>

@include('guru.bk.alih-tangan._form-modal', ['formId' => 'modal-edit-alih-tangan', 'alihTangan' => $alihTangan, 'guruBkOptions' => $guruBkOptions])
@endsection
