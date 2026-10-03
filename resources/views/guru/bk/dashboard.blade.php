@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Dashboard BK</h4>
        <p class="text-slate-400 text-sm m-0">Ringkasan kasus bimbingan konseling yang Anda tangani. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Layanan Bulan Ini</span>
            <span class="text-3xl font-bold text-emerald-400">{{ $layananBulanIni }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Siswa Kelas X</span>
            <span class="text-2xl font-bold text-slate-100">{{ $countKelasX }}</span>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Siswa Kelas XI</span>
            <span class="text-2xl font-bold text-slate-100">{{ $countKelasXI }}</span>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <span class="text-slate-400 text-sm block">Siswa Kelas XII</span>
            <span class="text-2xl font-bold text-slate-100">{{ $countKelasXII }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Kasus per Status</h6>
            @if($totalKasus === 0)
                <div class="flex items-center justify-center min-h-[220px] bg-slate-900/50 rounded-xl border border-slate-700/30">
                    <p class="text-slate-400 text-sm">Belum ada kasus tercatat.</p>
                </div>
            @else
                <div id="chartKasusStatus"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        registerChart(new ApexCharts(document.querySelector('#chartKasusStatus'), {
                            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                            series: @json([$perStatus['antrean'] ?? 0, $perStatus['proses'] ?? 0, $perStatus['selesai'] ?? 0]),
                            labels: ['Antrean', 'Sedang Diproses', 'Selesai'],
                            colors: ['#f43f5e', '#3b82f6', '#10b981'],
                            legend: { position: 'bottom' },
                            dataLabels: { enabled: true },
                        }));
                    });
                </script>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Kasus per Prioritas</h6>
            @if($totalKasus === 0)
                <div class="flex items-center justify-center min-h-[220px] bg-slate-900/50 rounded-xl border border-slate-700/30">
                    <p class="text-slate-400 text-sm">Belum ada kasus tercatat.</p>
                </div>
            @else
                <div id="chartKasusPrioritas"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        registerChart(new ApexCharts(document.querySelector('#chartKasusPrioritas'), {
                            chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                            series: [{ name: 'Kasus', data: @json([$perPrioritas['tinggi'] ?? 0, $perPrioritas['sedang'] ?? 0, $perPrioritas['rendah'] ?? 0]) }],
                            xaxis: { categories: ['Tinggi', 'Sedang', 'Rendah'] },
                            plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '55%' } },
                            colors: ['#f59e0b'],
                            dataLabels: { enabled: false },
                            grid: { strokeDashArray: 4 },
                        }));
                    });
                </script>
            @endif
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
