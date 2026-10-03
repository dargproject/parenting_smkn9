@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.asesmen.sosiometri.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Sosiometri</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">Sosiometri &middot; {{ $sosiometri->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $sosiometri->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $sosiometri->siswa->nis ?? '-' }} &middot; {{ $sosiometri->tanggal->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-sosio').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.asesmen.sosiometri.destroy', $sosiometri) }}" onsubmit="return confirm('Hapus data Sosiometri ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="space-y-4">
        @foreach($sosiometri->questionGroups() as $group)
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 m-0 mb-1">{{ $group['key'] }}</h6>
                <p class="text-sm text-slate-300 mb-3">{{ $group['pertanyaan'] }}</p>
                @if(count($group['dipilih']))
                    <div class="flex flex-wrap gap-2">
                        @foreach($group['dipilih'] as $nama)
                            <span class="rounded-full bg-blue-500/10 px-3 py-1 text-sm font-semibold text-blue-400">{{ $nama }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm m-0">Tidak ada pilihan.</p>
                @endif
            </div>
        @endforeach
    </div>
</div>

@include('guru.bk.asesmen.sosiometri._form-modal', ['formId' => 'modal-edit-sosio', 'siswas' => $siswas, 'sosiometri' => $sosiometri])
@endsection
