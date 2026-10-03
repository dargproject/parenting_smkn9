@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.asesmen.gaya-belajar.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Gaya Belajar</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">Gaya Belajar &middot; {{ $gayaBelajar->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $gayaBelajar->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $gayaBelajar->siswa->nis ?? '-' }} &middot; {{ $gayaBelajar->tanggal->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-gb').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.asesmen.gaya-belajar.destroy', $gayaBelajar) }}" onsubmit="return confirm('Hapus data Gaya Belajar ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            @foreach($gayaBelajar->questionGroups() as $group)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h6 class="font-bold text-slate-100 m-0">{{ $group['name'] }}</h6>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400">{{ $group['score'] }}/{{ $group['total'] }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($group['items'] as $item)
                            <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2">
                                <span class="text-sm text-slate-300 leading-5">{{ $item['text'] }}</span>
                                @if($item['checked'])
                                    <span class="shrink-0 rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-bold text-blue-400"><i class="fa-solid fa-check"></i></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Hasil</h6>
                @if($gayaBelajar->hasil)
                    <span class="inline-block rounded-full bg-blue-500/10 px-3 py-1.5 text-sm font-bold text-blue-400">{{ $gayaBelajar->hasil }}</span>
                @else
                    <p class="text-slate-400 text-sm m-0">Belum ditentukan.</p>
                @endif
            </div>
            @if($gayaBelajar->faktor_penghambat)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <h6 class="font-bold text-slate-100 mb-2">Faktor Penghambat</h6>
                    <p class="text-slate-300 text-sm m-0 whitespace-pre-line">{{ $gayaBelajar->faktor_penghambat }}</p>
                </div>
            @endif
            @if($gayaBelajar->faktor_pendukung)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <h6 class="font-bold text-slate-100 mb-2">Faktor Pendukung</h6>
                    <p class="text-slate-300 text-sm m-0 whitespace-pre-line">{{ $gayaBelajar->faktor_pendukung }}</p>
                </div>
            @endif
            @if($gayaBelajar->catatan)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <h6 class="font-bold text-slate-100 mb-2">Catatan</h6>
                    <p class="text-slate-300 text-sm m-0 whitespace-pre-line">{{ $gayaBelajar->catatan }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('guru.bk.asesmen.gaya-belajar._form-modal', ['formId' => 'modal-edit-gb', 'siswas' => collect([$gayaBelajar->siswa]), 'gayaBelajar' => $gayaBelajar])
@endsection
