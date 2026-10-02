@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Dashboard BK</h4>
        <p class="text-slate-400 text-sm m-0">Ringkasan kasus bimbingan konseling yang Anda tangani. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Total Kasus</span>
            <span class="text-3xl font-bold text-slate-100">{{ $totalKasus }}</span>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Antrean</span>
            <span class="text-3xl font-bold text-rose-400">{{ $perStatus['antrean'] ?? 0 }}</span>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Sedang Diproses</span>
            <span class="text-3xl font-bold text-blue-400">{{ $perStatus['proses'] ?? 0 }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Kasus per Tingkat Kelas</h6>
            <div class="flex flex-col gap-2">
                @forelse($perTingkat as $tingkat => $jumlah)
                    <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2">
                        <span class="text-sm font-semibold text-slate-100">Kelas {{ $tingkat }}</span>
                        <span class="text-sm font-bold text-blue-400">{{ $jumlah }} siswa</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada kasus tercatat.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Kasus per Prioritas</h6>
            <div class="flex flex-col gap-2">
                @forelse(['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'] as $key => $label)
                    @continue(!($perPrioritas[$key] ?? 0))
                    <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2">
                        <span class="text-sm font-semibold text-slate-100">{{ $label }}</span>
                        <span class="text-sm font-bold text-amber-400">{{ $perPrioritas[$key] }} kasus</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada kasus tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
        <div class="flex items-center justify-between mb-3">
            <h6 class="font-bold text-slate-100 m-0">Kasus Terbaru</h6>
            <a href="{{ route('guru.bk.kasus.index') }}" class="text-sm text-blue-400 hover:text-blue-300">Lihat Semua &rarr;</a>
        </div>
        <div class="flex flex-col gap-2">
            @forelse($kasusTerbaru as $kasus)
                <a href="{{ route('guru.bk.kasus.show', $kasus) }}" class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2 hover:bg-slate-900">
                    <span class="text-sm font-semibold text-slate-100">{{ $kasus->judul }} <span class="text-slate-400 font-normal">&middot; {{ $kasus->siswa->nama ?? '-' }}</span></span>
                    <span class="text-xs text-slate-400">{{ ucfirst($kasus->status) }}</span>
                </a>
            @empty
                <p class="text-slate-400 text-sm m-0">Belum ada kasus BK yang dicatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
