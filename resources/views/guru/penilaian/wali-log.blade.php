<div id="pane-guru-wali-log" class="pane-content hidden-pane fade-transition space-y-4" x-data="{ kelas: 'semua', cari: '' }">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Log Perubahan Nilai</h4>
        <p class="text-slate-400 text-sm">Riwayat setiap perubahan nilai N (Nilai Formatif Lingkup Materi maupun hasil remedial) untuk siswa di kelas binaan Anda &mdash; kapan diubah, dari berapa ke berapa, dan oleh guru mana.</p>
    </div>

    @if($logPerubahanNilaiBinaan->isEmpty())
        <p class="text-slate-400 text-sm">Belum ada perubahan nilai yang tercatat untuk kelas binaan Anda.</p>
    @else
        @php $daftarKelasLog = $kelasBinaan->sortBy('nama_kelas', SORT_NATURAL)->values(); @endphp
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            @if($daftarKelasLog->count() > 1)
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-400 whitespace-nowrap"><i class="fa-solid fa-people-roof mr-1"></i> Kelas</label>
                    <select x-model="kelas" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
                        <option value="semua">Semua Kelas</option>
                        @foreach($daftarKelasLog as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="relative flex-1 sm:max-w-xs">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" x-model="cari" placeholder="Cari nama siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 py-1.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500">
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-700/60">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                    <tr>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Siswa</th>
                        <th class="px-3 py-2">Mapel &amp; N</th>
                        <th class="px-3 py-2 text-center">Jenis</th>
                        <th class="px-3 py-2 text-center">Nilai</th>
                        <th class="px-3 py-2">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @foreach($logPerubahanNilaiBinaan as $log)
                        @php
                            $siswaLog = $log->nilaiLm->siswa ?? null;
                            $tpLog = $log->nilaiLm->tujuanPembelajaran ?? null;
                        @endphp
                        @continue(!$siswaLog || !$tpLog)
                        <tr x-show="(kelas === 'semua' || kelas == {{ $siswaLog->kelas_id }}) && (!cari || {{ \Illuminate\Support\Js::from(mb_strtolower($siswaLog->nama)) }}.includes(cari.toLowerCase()))">
                            <td class="px-3 py-2 text-slate-400 whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-3 py-2 font-semibold text-slate-100">{{ $siswaLog->nama }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">{{ $siswaLog->kelas->nama_kelas ?? '' }}</span></td>
                            <td class="px-3 py-2 text-slate-300">{{ $tpLog->mataPelajaran->nama_mapel ?? '-' }}<span class="block text-slate-400" style="font-size: 10px;">{{ $tpLog->kode ?: '' }} {{ $tpLog->deskripsi }}</span></td>
                            <td class="px-3 py-2 text-center">
                                @if($log->kolom === 'nilai_remedial')
                                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-amber-400" style="font-size: 11px;">Remedial</span>
                                @else
                                    <span class="rounded-full bg-slate-700 px-2 py-0.5 text-slate-300" style="font-size: 11px;">Nilai N</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center font-semibold text-slate-100">{{ $log->nilai_lama ?? '-' }} <i class="fa-solid fa-arrow-right text-slate-500 mx-1" style="font-size: 10px;"></i> {{ $log->nilai_baru ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-300">{{ $log->guru->nama ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
