@php
    $kelasRilisList = $kelasBinaan->sortBy('nama_kelas', SORT_NATURAL)->values();
    $jumlahSiswaRilis = $siswaBinaan->groupBy('kelas_id')->map->count();
@endphp
<div id="pane-guru-wali-rilis" class="pane-content hidden-pane fade-transition space-y-4" x-data="kelasXData('gw-rilis:{{ $guru->id }}', @js($kelasRilisList->pluck('id')), function () { this.$el.querySelectorAll('.rilis-checkbox').forEach(c => c.checked = false); })">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Finalisasi &amp; Rilis Nilai</h4>
        <p class="text-slate-400 text-sm">Kunci nilai semester dan terbitkan ringkasan nilai ke dashboard orang tua. Hanya siswa dengan nilai SAS lengkap di seluruh mata pelajaran yang bisa dirilis.</p>
    </div>

    @if($siswaBinaan->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum menjadi guru wali (akademik) untuk kelas manapun.</p>
    @else
        @include('guru.partials.pilih-kelas', ['daftarKelas' => $kelasRilisList, 'jumlahSiswa' => $jumlahSiswaRilis, 'cari' => true])
        <form method="POST" action="{{ route('guru.wali.rapor.rilis') }}">
            @csrf
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                        <tr>
                            <th class="px-3 py-3"><input type="checkbox" onclick="const on = this.checked; document.querySelectorAll('.rilis-checkbox').forEach(c => { if (c.offsetParent !== null && !c.disabled) c.checked = on; })"></th>
                            <th class="px-3 py-3">Nama Siswa</th>
                            <th class="px-3 py-3">Kelas</th>
                            <th class="px-3 py-3 text-center">Kelengkapan Nilai</th>
                            <th class="px-3 py-3 text-center">Status Rapor</th>
                            <th class="px-3 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($siswaBinaan as $siswa)
                            @php
                                $lengkap = $siswaBinaanLengkap[$siswa->id] ?? false;
                                $rapor = $raporFinalBinaan[$siswa->id] ?? null;
                                $sudahFinal = $rapor && $rapor->status === 'final';
                            @endphp
                            <tr x-show="kelas == {{ $siswa->kelas_id }} && (!cari || {{ \Illuminate\Support\Js::from(mb_strtolower($siswa->nama)) }}.includes(cari.toLowerCase()))">
                                <td class="px-3 py-3">
                                    <input type="checkbox" class="rilis-checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" {{ !$lengkap || $sudahFinal ? 'disabled' : '' }}>
                                </td>
                                <td class="px-3 py-3 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                                <td class="px-3 py-3 text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $lengkap ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">{{ $lengkap ? 'Lengkap' : 'Belum Lengkap' }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sudahFinal ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-500/10 text-slate-400' }}">{{ $sudahFinal ? 'Dirilis' : 'Draft' }}</span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    @if($sudahFinal)
                                        <button type="submit" form="batalkan-{{ $siswa->id }}" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Batalkan Rilis</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end mt-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Rilis Nilai Terpilih</button>
            </div>
        </form>

        @foreach($siswaBinaan as $siswa)
            @if(($raporFinalBinaan[$siswa->id] ?? null)?->status === 'final')
                <form id="batalkan-{{ $siswa->id }}" method="POST" action="{{ route('guru.wali.rapor.batalkan') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                </form>
            @endif
        @endforeach
    @endif
</div>
