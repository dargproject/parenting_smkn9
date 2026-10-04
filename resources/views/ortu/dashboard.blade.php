@extends('layouts.ortu')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Halo, {{ $siswa->orangTua->nama ?? 'Orang Tua' }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ringkasan nilai <span
                    class="font-semibold">{{ $siswa->nama }}</span> &middot; {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
        </div>

        @if($pesanWaliKelas->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <p class="font-semibold text-slate-900 dark:text-white mb-3"><i class="fa-solid fa-envelope text-blue-500 mr-2"></i>Pesan dari Wali Kelas</p>
                <div class="flex flex-col gap-3 max-h-72 overflow-y-auto">
                    @foreach($pesanWaliKelas as $pesan)
                        <div class="rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-900/60">
                            <p class="text-sm text-slate-800 dark:text-slate-100 m-0">{{ $pesan->pesan }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5 m-0">{{ $pesan->guru->nama ?? 'Wali Kelas' }} &middot; {{ $pesan->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($prestasiNonAkademik->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <p class="font-semibold text-slate-900 dark:text-white mb-3"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i>Prestasi Non-Akademik</p>
                <div class="flex flex-col gap-2">
                    @foreach($prestasiNonAkademik as $prestasi)
                        <div class="rounded-xl bg-slate-50 px-4 py-2.5 dark:bg-slate-900/60">
                            <div class="flex items-start justify-between gap-2">
                                <p class="m-0 font-semibold text-slate-800 dark:text-slate-100">{{ $prestasi->nama_prestasi }}</p>
                                <x-status-badge tone="amber" light class="whitespace-nowrap">{{ $prestasi->tingkat }}</x-status-badge>
                            </div>
                            <p class="m-0 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ $prestasi->tanggal->translatedFormat('d M Y') }}
                                @if($prestasi->peringkat) &middot; {{ $prestasi->peringkat }} @endif
                                @if($prestasi->penyelenggara) &middot; {{ $prestasi->penyelenggara }} @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <p class="font-semibold text-slate-900 dark:text-white mb-1"><i class="fa-solid fa-hand-holding-heart text-emerald-500 mr-2"></i>Bimbingan Konseling</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Hanya informasi yang dibagikan guru BK yang tampil di sini -- isi konseling tetap rahasia.</p>

            <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Konselor BK: <span class="font-semibold text-slate-900 dark:text-white">{{ $konselorBk->isNotEmpty() ? $konselorBk->pluck('nama')->implode(', ') : 'Belum ditentukan' }}</span></p>

            @if($panggilanOrtus->isNotEmpty())
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Panggilan Orang Tua</p>
                    <div class="flex flex-col gap-2">
                        @foreach($panggilanOrtus as $panggilan)
                            <div class="rounded-xl bg-slate-50 px-4 py-2.5 dark:bg-slate-900/60 text-sm">
                                <p class="m-0 text-slate-800 dark:text-slate-100">{{ \Illuminate\Support\Carbon::parse($panggilan->tanggal)->translatedFormat('d M Y') }}, {{ $panggilan->waktu }}{{ $panggilan->ruang ? ' · '.$panggilan->ruang : '' }}</p>
                                <p class="m-0 text-slate-500 dark:text-slate-400">{{ $panggilan->alasan }}</p>
                                <x-status-badge tone="amber" light class="mt-1 inline-block">{{ $panggilan->status }}</x-status-badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($bkKunjunganRumah->isNotEmpty())
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Jadwal Kunjungan Rumah</p>
                    <div class="flex flex-col gap-2">
                        @foreach($bkKunjunganRumah as $kunjungan)
                            <div class="rounded-xl bg-slate-50 px-4 py-2.5 dark:bg-slate-900/60 text-sm flex items-center justify-between">
                                <span class="text-slate-800 dark:text-slate-100">{{ $kunjungan->tanggal_kunjungan->translatedFormat('d M Y') }}</span>
                                <x-status-badge tone="blue" light>{{ ucfirst($kunjungan->status) }}</x-status-badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($bkKonferensi->isNotEmpty())
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Undangan Konferensi Kasus</p>
                    <div class="flex flex-col gap-2">
                        @foreach($bkKonferensi as $konferensi)
                            <div class="rounded-xl bg-slate-50 px-4 py-2.5 dark:bg-slate-900/60 text-sm">
                                <span class="text-slate-800 dark:text-slate-100">{{ $konferensi->tanggal_konferensi->translatedFormat('d M Y') }}{{ $konferensi->tempat_pertemuan ? ' · '.$konferensi->tempat_pertemuan : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($bkGayaBelajar || $bkPeminatan)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Hasil Asesmen</p>
                    <div class="flex flex-wrap gap-2">
                        @if($bkGayaBelajar)
                            <x-status-badge tone="emerald" light>Gaya Belajar: {{ $bkGayaBelajar->hasil ?? '-' }}</x-status-badge>
                        @endif
                        @if($bkPeminatan)
                            <x-status-badge tone="purple" light>Bakat Minat: {{ implode(', ', array_filter([$bkPeminatan->pilihan1, $bkPeminatan->pilihan2, $bkPeminatan->pilihan3])) ?: '-' }}</x-status-badge>
                        @endif
                    </div>
                </div>
            @endif

            @if($panggilanOrtus->isEmpty() && $bkKunjunganRumah->isEmpty() && $bkKonferensi->isEmpty() && ! $bkGayaBelajar && ! $bkPeminatan)
                <p class="text-sm text-slate-400 dark:text-slate-500 m-0">Belum ada informasi BK yang dibagikan.</p>
            @endif
        </div>

        <a href="{{ route('ortu.keluarga.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:border-blue-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <i class="fa-solid fa-house-user text-blue-500"></i> Lengkapi Data Keluarga
        </a>

        @if($riwayat->isNotEmpty() || $tahunAjaranAktifSistem)
            <form method="GET" class="flex items-center gap-2 text-sm">
                <label class="text-slate-500 dark:text-slate-400">Semester:</label>
                <select name="ta" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:text-white">
                    @if($tahunAjaranAktifSistem && !$riwayat->contains('id', $tahunAjaranAktifSistem->id))
                        <option value="{{ $tahunAjaranAktifSistem->id }}" @selected($tahunAjaranTerpilih?->id === $tahunAjaranAktifSistem->id)>{{ $tahunAjaranAktifSistem->nama }} (berjalan)</option>
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
                            @php $tone = $r['status'] === 'tuntas' ? 'emerald' : ($r['status'] === 'remedial' ? 'rose' : 'slate'); @endphp
                            <x-status-badge :tone="$tone" light>{{ $r['status'] === 'remedial' ? 'Belum Tuntas' : ucfirst($r['status']) }}</x-status-badge>
                        </div>
                    </a>
                @empty
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data nilai mata pelajaran.</p>
                @endforelse
            </div>
        @endif
    </div>
@endsection
