@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.asesmen.akpd.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar AKPD</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">AKPD &middot; {{ $akpd->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $akpd->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $akpd->siswa->nis ?? '-' }} &middot; {{ $akpd->tanggal->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-akpd').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.asesmen.akpd.destroy', $akpd) }}" onsubmit="return confirm('Hapus data AKPD ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="space-y-5">
        @foreach($akpd->aspectAnswers() as $group)
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h6 class="font-bold text-slate-100 m-0 uppercase tracking-wide">{{ $group['aspect'] }}</h6>
                    <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400">Ya {{ $group['ya_count'] }}/{{ $group['total'] }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($group['answers'] as $answer)
                        <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2">
                            <span class="text-sm text-slate-300 leading-5"><span class="font-bold text-slate-100 mr-1">{{ $answer['no'] }}.</span>{{ $answer['pertanyaan'] }}</span>
                            @if($answer['jawaban'] === 'Ya')
                                <span class="shrink-0 rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-bold text-blue-400">Ya</span>
                            @else
                                <span class="shrink-0 rounded-full bg-slate-700/40 px-2.5 py-0.5 text-xs font-bold text-slate-400">{{ $answer['jawaban'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('guru.bk.asesmen.akpd._form-modal', ['formId' => 'modal-edit-akpd', 'siswas' => collect([$akpd->siswa]), 'akpd' => $akpd])
@endsection
