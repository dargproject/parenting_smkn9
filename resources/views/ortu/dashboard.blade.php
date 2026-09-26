@extends('layouts.ortu')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Halo, {{ $siswa->orangTua->nama ?? 'Orang Tua' }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ringkasan nilai <span
                    class="font-semibold">{{ $siswa->nama }}</span> &middot; {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
        </div>

        @if(!$dirilis)
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
                <i class="fa-solid fa-hourglass-half text-2xl text-amber-500 mb-2"></i>
                <p class="font-semibold text-amber-700 dark:text-amber-400">Rapor semester ini belum dirilis</p>
                <p class="text-sm text-amber-600 dark:text-amber-400/80 mt-1">Wali kelas belum menyelesaikan finalisasi nilai.
                    Silakan cek kembali nanti.</p>
            </div>
        @else
            @if($perluPerhatian->isNotEmpty())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                    <p class="font-semibold text-rose-700 dark:text-rose-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Perlu perhatian</p>
                    <p class="text-sm text-rose-600 dark:text-rose-400/80 mt-1">
                        {{ $perluPerhatian->pluck('mapel.nama_mapel')->implode(', ') }} memerlukan bimbingan tambahan.
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($ringkasan as $r)
                    <a href="{{ route('ortu.mapel.show', $r['mapel']) }}"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-400 transition-colors dark:border-slate-700 dark:bg-slate-800">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $r['mapel']->nama_mapel }}</p>
                        <div class="mt-2 flex items-center justify-between">
                            <!-- <span class="text-2xl font-bold {{ $r['status'] === 'tuntas' ? 'text-emerald-600 dark:text-emerald-400' : ($r['status'] === 'remedial' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">{{ $r['na'] ?? '-' }}</span> -->
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $r['status'] === 'tuntas' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($r['status'] === 'remedial' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400') }}">{{ $r['status'] === 'remedial' ? 'Belum Tuntas' : ucfirst($r['status']) }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data nilai mata pelajaran.</p>
                @endforelse
            </div>
        @endif
    </div>
@endsection