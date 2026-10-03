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
                                <span class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">{{ $panggilan->status }}</span>
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
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">{{ ucfirst($kunjungan->status) }}</span>
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
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Gaya Belajar: {{ $bkGayaBelajar->hasil ?? '-' }}</span>
                        @endif
                        @if($bkPeminatan)
                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">Bakat Minat: {{ implode(', ', array_filter([$bkPeminatan->pilihan1, $bkPeminatan->pilihan2, $bkPeminatan->pilihan3])) ?: '-' }}</span>
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
