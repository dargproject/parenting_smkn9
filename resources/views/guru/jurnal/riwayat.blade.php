<div id="pane-guru-mapel-riwayat" class="pane-content hidden-pane fade-transition" x-data="{ pilih: null }">
    <div class="mb-3">
        <h4 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Riwayat Jurnal Mengajar</h4>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Kalender pengisian jurnal mengajar Anda. Arahkan kursor ke simbol <i class="fa-solid fa-circle-info text-amber-500 dark:text-amber-400"></i> untuk melihat kekurangannya, atau klik tanggal untuk melihat rinciannya.</p>
    </div>

    <div class="cal-card rounded-2xl border p-4 md:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('guru.portal', ['jurnal_bulan' => $bulanSebelumnya]) }}" class="cal-nav-btn rounded-lg border px-3 py-1.5 text-sm transition-colors"><i class="fa-solid fa-chevron-left"></i></a>
            <span class="font-bold text-base md:text-lg" style="color: var(--text-primary);">{{ ucfirst($namaBulanTerpilih) }}</span>
            <a href="{{ route('guru.portal', ['jurnal_bulan' => $bulanBerikutnya]) }}" class="cal-nav-btn rounded-lg border px-3 py-1.5 text-sm transition-colors"><i class="fa-solid fa-chevron-right"></i></a>
        </div>

        <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-semibold mb-2">
            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)
                <div class="py-1 cal-day-header">{{ $h }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-1.5">
            @for($i = 0; $i < $offsetAwalKalender; $i++)
                <div></div>
            @endfor
            @foreach($kalenderJurnal as $tgl => $info)
                @php
                    $warna = 'cal-cell-empty';
                    if ($info['ada_jadwal']) {
                        if ($info['terisi'] >= $info['total']) {
                            $warna = 'cal-cell-terisi';
                        } elseif ($info['lewat']) {
                            $warna = 'cal-cell-lewat';
                        } else {
                            $warna = 'cal-cell-terjadwal';
                        }
                    }
                @endphp
                <button type="button" data-tgl="{{ $tgl }}" @click="pilih = pilih === '{{ $tgl }}' ? null : '{{ $tgl }}'"
                    {{ !$info['ada_jadwal'] ? 'disabled' : '' }}
                    class="relative aspect-square rounded-lg text-sm font-semibold flex items-center justify-center transition-all {{ $warna }} {{ $info['ada_jadwal'] ? 'cursor-pointer hover:shadow-xs' : 'cursor-default opacity-80' }}">
                    {{ (int) substr($tgl, -2) }}
                    @if($info['ada_jadwal'] && $info['lewat'] && $info['terisi'] < $info['total'])
                        <span class="group absolute -top-1.5 -right-1.5">
                            <i class="fa-solid fa-circle-info text-xs text-amber-500 rounded-full"></i>
                            <span class="cal-tooltip pointer-events-none absolute bottom-full right-0 z-30 mb-1.5 hidden w-64 rounded-xl p-3 text-left text-xs font-normal normal-case shadow-xl group-hover:block transition-all">
                                <span class="block font-bold cal-tooltip-title mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                                    <span>Belum diisi:</span>
                                </span>
                                <span class="cal-tooltip-text leading-relaxed block">{{ implode(', ', $info['kurang']) }}</span>
                            </span>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs font-medium" style="color: var(--text-secondary);">
            <span class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 rounded cal-cell-terisi"></span> Lengkap
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 rounded cal-cell-lewat"></span> Ada yang belum diisi
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 rounded cal-cell-terjadwal"></span> Terjadwal, belum jatuh tempo
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 rounded cal-cell-empty"></span> Tidak ada jadwal
            </span>
        </div>

        @foreach($kalenderJurnal as $tgl => $info)
            @continue(!$info['ada_jadwal'])
            <div x-show="pilih === '{{ $tgl }}'" x-cloak style="display: none;" class="cal-detail-container mt-5 rounded-xl p-4 shadow-xs">
                <h6 class="font-bold mb-2.5 flex items-center gap-2" style="color: var(--text-primary);">
                    <i class="fa-regular fa-calendar-check text-blue-500"></i>
                    <span>{{ \Carbon\Carbon::parse($tgl)->locale('id')->translatedFormat('l, d F Y') }}</span>
                </h6>
                <ul class="space-y-2 text-sm">
                    @foreach($info['detail'] as $d)
                        <li class="cal-detail-item flex items-center justify-between gap-3 p-2.5 rounded-lg shadow-xs">
                            <span class="font-medium" style="color: var(--text-primary);">{{ $d['label'] }}</span>
                            @if($d['terisi'])
                                <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 text-xs font-semibold text-right"><i class="fa-solid fa-circle-check mr-1.5"></i>Terisi{{ $d['materi'] ? ' — '.$d['materi'] : '' }}</span>
                            @else
                                <button type="button" onclick="bukaJurnalTanggal('{{ $tgl }}', {{ $d['id'] }})" class="rounded-lg border border-rose-300 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors whitespace-nowrap shadow-xs"><i class="fa-solid fa-pen mr-1"></i>Lengkapi Jurnal</button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
