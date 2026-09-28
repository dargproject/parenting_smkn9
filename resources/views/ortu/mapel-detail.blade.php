@extends('layouts.ortu')

@section('content')
    <div class="space-y-6">
        <a href="{{ route('ortu.dashboard', ['ta' => $tahunAjaranTerpilih?->id]) }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"><i
                class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Ringkasan</a>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $mapel->nama_mapel }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $siswa->nama }} &middot;
                    {{ $siswa->kelas->nama_kelas ?? '-' }}
                </p>
            </div>
        </div>

        @if($tpRemedial->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 border-l-4 border-l-rose-500 dark:border-rose-500/40 dark:bg-slate-800">
                <p class="font-bold text-rose-700 dark:text-rose-400 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400"></i>
                    <span>Materi yang masih perlu bimbingan</span>
                </p>
                <ul class="mt-2.5 list-disc list-inside text-sm text-slate-700 dark:text-slate-200 space-y-1">
                    @foreach($tpRemedial as $tp)
                        <li class="leading-relaxed">
                            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $tp->tujuanPembelajaran->deskripsi ?? '-' }}</span>
                            {{-- <span class="ml-1 font-semibold text-rose-600 dark:text-rose-400">(nilai {{ $tp->nilai }})</span> --}}
                            <span class="ml-1 font-semibold text-rose-600 dark:text-rose-400">({{ $tp->tujuanPembelajaran->kode ?? '' }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Rincian Nilai Sumatif Lingkup Materi</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[480px] text-left text-sm">
                    <thead
                        class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2.5">Nilai Sumatif (N)</th>
                            <th class="px-4 py-2.5 text-center w-36">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($nilaiLm as $lm)
                            <tr>
                                <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">
                                    {{ $lm->tujuanPembelajaran->kode ?? '' }} {{ $lm->tujuanPembelajaran->deskripsi ?? '-' }}
                                </td>
                                <td
                                    class="px-4 py-2.5 text-center font-semibold {{ $lm->nilai >= setting('kktp_threshold', 75) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $lm->nilai }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-center text-sm text-slate-500 dark:text-slate-400">Belum
                                    ada nilai LM tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}

        <!-- @if($pklUkk->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-3">Nilai PKL &amp; UKK</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">PKL</span>
                            <span class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $pklUkk['pkl']->nilai ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">UKK</span>
                            <span class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $pklUkk['ukk']->nilai ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif -->

        @if($capaian || $catatanKompetensi || $catatanWaliKelas?->catatan_karakter)
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white">Catatan Guru</h3>
                @if($capaian)
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Capaian Kompetensi
                            ({{ $mapel->nama_mapel }})</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $capaian }}</p>
                    </div>
                @endif
                @if($catatanKompetensi)
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Catatan Tambahan Guru {{ $mapel->nama_mapel }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $catatanKompetensi->catatan }}</p>
                    </div>
                @endif
                @if($catatanWaliKelas?->catatan_karakter)
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Catatan Wali Kelas</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $catatanWaliKelas->catatan_karakter }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
