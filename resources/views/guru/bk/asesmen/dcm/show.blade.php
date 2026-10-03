@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.asesmen.dcm.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar DCM</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">DCM &middot; {{ $dcm->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $dcm->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $dcm->siswa->nis ?? '-' }} &middot; {{ $dcm->tanggal->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-dcm').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.asesmen.dcm.destroy', $dcm) }}" onsubmit="return confirm('Hapus data DCM ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-4">
            @foreach($dcm->questionGroups() as $group)
                @if($group['checked_count'] > 0)
                    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h6 class="font-bold text-slate-100 m-0">{{ $group['section'] }}. {{ $group['title'] }}</h6>
                            <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400">{{ $group['checked_count'] }}/{{ $group['total'] }}</span>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($group['items'] as $item)
                                @if($item['checked'])
                                    <div class="rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300"><span class="font-bold text-slate-500 mr-1">{{ $item['kode'] }}</span>{{ $item['pertanyaan'] }}</div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            @if(collect($dcm->questionGroups())->sum('checked_count') === 0)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 text-slate-400 text-sm">Tidak ada masalah yang dicentang.</div>
            @endif
        </div>

        <div class="space-y-5">
            @if($dcm->kesimpulan)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <h6 class="font-bold text-slate-100 mb-2">Kesimpulan</h6>
                    <p class="text-slate-300 text-sm m-0 whitespace-pre-line">{{ $dcm->kesimpulan }}</p>
                </div>
            @endif
            @if($dcm->catatan)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                    <h6 class="font-bold text-slate-100 mb-2">Catatan</h6>
                    <p class="text-slate-300 text-sm m-0 whitespace-pre-line">{{ $dcm->catatan }}</p>
                </div>
            @endif
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-2">Total Masalah Teridentifikasi</h6>
                <p class="text-2xl font-bold text-blue-400 m-0">{{ count($dcm->masalah_teridentifikasi ?? []) }}</p>
            </div>
        </div>
    </div>
</div>

@include('guru.bk.asesmen.dcm._form-modal', ['formId' => 'modal-edit-dcm', 'siswas' => collect([$dcm->siswa]), 'dcm' => $dcm])
@endsection
