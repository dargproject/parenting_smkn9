@extends('layouts.ortu')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Halo, {{ $siswa->orangTua->nama ?? 'Orang Tua' }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ringkasan nilai <span
                    class="font-semibold">{{ $siswa->nama }}</span> &middot; {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
        </div>

        @if($riwayat->isNotEmpty())
            <form method="GET" class="flex items-center gap-2 text-sm">
                <label class="text-slate-500 dark:text-slate-400">Semester:</label>
                <select name="ta" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:text-white">
                    @if(!$riwayat->contains('id', $tahunAjaranTerpilih?->id))
                        <option value="{{ $tahunAjaranTerpilih?->id }}" selected>{{ $tahunAjaranTerpilih?->nama ?? 'Semester aktif' }} (berjalan)</option>
                    @endif
                    @foreach($riwayat as $ta)
                        <option value="{{ $ta->id }}" @selected($tahunAjaranTerpilih?->id === $ta->id)>{{ $ta->nama }}</option>
                    @endforeach
                </select>
            </form>
        @endif

        @if(!$dirilis)
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
                <i class="fa-solid fa-hourglass-half text-2xl text-amber-500 mb-2"></i>
                <p class="font-semibold text-amber-700 dark:text-amber-400">Rapor semester ini belum dirilis</p>
                <p class="text-sm text-amber-600 dark:text-amber-400/80 mt-1">Guru Wali belum menyelesaikan finalisasi nilai.
                    Silakan cek kembali nanti.</p>
            </div>
        @else
            @if($perluPerhatian->isNotEmpty())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 border-l-4 border-l-rose-500 dark:border-rose-500/40 dark:bg-slate-800">
                    <p class="font-bold text-rose-700 dark:text-rose-400 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400"></i>
                        <span>Perlu perhatian</span>
                    </p>
                    <p class="text-sm text-slate-700 dark:text-slate-200 mt-1">
                        <span class="font-medium text-slate-900 dark:text-white">{{ $perluPerhatian->pluck('mapel.nama_mapel')->implode(', ') }}</span> memerlukan bimbingan tambahan.
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($ringkasan as $r)
                    <a href="{{ route('ortu.mapel.show', ['mataPelajaran' => $r['mapel'], 'ta' => $tahunAjaranTerpilih?->id]) }}"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-400 transition-colors dark:border-slate-700 dark:bg-slate-800">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $r['mapel']->nama_mapel }}</p>
                        <div class="mt-2 flex items-center">
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
