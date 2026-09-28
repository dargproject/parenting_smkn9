<div id="pane-guru-mapel-riwayat" class="pane-content hidden-pane fade-transition" x-data="{ pilih: null }">
    <div class="mb-3">
        <h4 class="font-bold text-slate-100 mb-1">Riwayat Jurnal Mengajar</h4>
        <p class="text-slate-400 text-sm">Kalender pengisian jurnal mengajar Anda. Arahkan kursor ke simbol <i class="fa-solid fa-circle-info text-amber-400"></i> untuk melihat kekurangannya, atau klik tanggal untuk melihat rinciannya.</p>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-6 text-slate-100">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('guru.portal', ['jurnal_bulan' => $bulanSebelumnya]) }}" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-slate-300 hover:border-blue-500"><i class="fa-solid fa-chevron-left"></i></a>
            <span class="font-bold text-slate-100">{{ ucfirst($namaBulanTerpilih) }}</span>
            <a href="{{ route('guru.portal', ['jurnal_bulan' => $bulanBerikutnya]) }}" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-slate-300 hover:border-blue-500"><i class="fa-solid fa-chevron-right"></i></a>
        </div>

        <div class="grid grid-cols-7 gap-1.5 text-center text-xs text-slate-400 mb-2">
            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)
                <div class="font-semibold">{{ $h }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-1.5">
            @for($i = 0; $i < $offsetAwalKalender; $i++)
                <div></div>
            @endfor
            @foreach($kalenderJurnal as $tgl => $info)
                @php
                    $warna = 'bg-slate-900/60 text-slate-500 border border-transparent';
                    if ($info['ada_jadwal']) {
                        if ($info['terisi'] >= $info['total']) {
                            $warna = 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
                        } elseif ($info['lewat']) {
                            $warna = 'bg-rose-500/20 text-rose-300 border border-rose-500/40';
                        } else {
                            $warna = 'bg-slate-700/60 text-slate-200 border border-slate-600';
                        }
                    }
                @endphp
                <button type="button" data-tgl="{{ $tgl }}" @click="pilih = pilih === '{{ $tgl }}' ? null : '{{ $tgl }}'"
                    {{ !$info['ada_jadwal'] ? 'disabled' : '' }}
                    class="relative aspect-square rounded-lg text-sm font-semibold flex items-center justify-center {{ $warna }} {{ $info['ada_jadwal'] ? 'cursor-pointer hover:opacity-80' : 'cursor-default' }}">
                    {{ (int) substr($tgl, -2) }}
                    @if($info['ada_jadwal'] && $info['lewat'] && $info['terisi'] < $info['total'])
                        <span class="group absolute -top-1.5 -right-1.5">
                            <i class="fa-solid fa-circle-info text-xs text-amber-400 bg-slate-900 rounded-full"></i>
                            <span class="pointer-events-none absolute bottom-full right-0 z-20 mb-1 hidden w-56 rounded-lg border border-slate-700 bg-slate-900 p-2 text-left text-xs font-normal normal-case text-slate-200 shadow-lg group-hover:block">
                                <span class="block font-semibold text-amber-400 mb-1">Belum diisi:</span>
                                {{ implode(', ', $info['kurang']) }}
                            </span>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-500/40 border border-emerald-500"></span> Lengkap</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-500/40 border border-rose-500"></span> Ada yang belum diisi</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-slate-700/60 border border-slate-600"></span> Terjadwal, belum jatuh tempo</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-slate-900/60 border border-slate-700"></span> Tidak ada jadwal</span>
        </div>

        @foreach($kalenderJurnal as $tgl => $info)
            @continue(!$info['ada_jadwal'])
            <div x-show="pilih === '{{ $tgl }}'" style="display: none;" class="mt-5 rounded-xl border border-slate-700/60 bg-slate-900 p-4">
                <h6 class="font-bold text-slate-100 mb-2">{{ \Carbon\Carbon::parse($tgl)->locale('id')->translatedFormat('l, d F Y') }}</h6>
                <ul class="space-y-1.5 text-sm">
                    @foreach($info['detail'] as $d)
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-slate-300">{{ $d['label'] }}</span>
                            @if($d['terisi'])
                                <span class="text-emerald-400 text-xs font-semibold text-right"><i class="fa-solid fa-circle-check mr-1"></i>Terisi{{ $d['materi'] ? ' — '.$d['materi'] : '' }}</span>
                            @else
                                <button type="button" onclick="bukaJurnalTanggal('{{ $tgl }}', {{ $d['id'] }})" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-400 hover:bg-rose-500/20 whitespace-nowrap"><i class="fa-solid fa-pen mr-1"></i>Lengkapi Jurnal</button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
