@php $kelasMatrixList = collect($matrixPerKelas)->pluck('kelas')->sortBy('nama_kelas', SORT_NATURAL)->values(); @endphp
<div id="pane-guru-wali-nilai-matrix" class="pane-content hidden-pane fade-transition space-y-6" x-data="kelasXData('gw-matrix:{{ $guru->id }}', @js($kelasMatrixList->pluck('id')))">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Rekap Nilai Rombel</h4>
        <p class="text-slate-400 text-sm">Matrix nilai akhir seluruh siswa &times; mata pelajaran untuk kelas binaan Anda, beserta status progres input tiap guru mapel.</p>
    </div>

    @if($kelasMatrixList->count() > 1)
        @include('guru.partials.pilih-kelas', ['daftarKelas' => $kelasMatrixList])
    @endif

    @forelse($matrixPerKelas as $kelasData)
        @php $kelas = $kelasData['kelas']; @endphp
        <div x-show="kelas == {{ $kelas->id }}" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 space-y-4">
            <h5 class="text-lg font-bold text-slate-100">{{ $kelas->nama_kelas }}</h5>

            @if($kelasData['mapel']->isEmpty())
                <p class="text-slate-400 text-sm">Belum ada jadwal mata pelajaran untuk kelas ini pada tahun ajaran berjalan.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($kelasData['mapel'] as $mp)
                        @php $lengkap = $mp['total'] > 0 && $mp['submitted'] >= $mp['total']; @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $lengkap ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                            {{ $mp['mapel']->nama_mapel }}: {{ $mp['submitted'] }}/{{ $mp['total'] }} siswa dinilai
                        </span>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('guru.wali.catatan-akademik.store') }}" class="space-y-2">
                @csrf
                <div class="overflow-x-auto rounded-xl border border-slate-700/60">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Nama Siswa</th>
                                @foreach($kelasData['mapel'] as $mp)
                                    <th class="px-3 py-2 text-center">{{ $mp['mapel']->nama_mapel }}</th>
                                @endforeach
                                <th class="px-3 py-2" style="min-width: 240px;">Catatan Akademik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            @foreach($kelasData['rows'] as $row)
                                <tr>
                                    <td class="px-3 py-2 font-semibold text-slate-100">{{ $row['siswa']->nama }}</td>
                                    @foreach($kelasData['mapel'] as $mp)
                                        @php $cell = $row['nilai'][$mp['mapel']->id] ?? ['na' => null, 'status' => 'belum ada nilai']; @endphp
                                        <td class="px-3 py-2 text-center">
                                            @if($cell['na'] !== null)
                                                <span class="font-bold text-blue-400">{{ $cell['na'] }}</span>
                                                <span class="block text-xs {{ $cell['status'] === 'tuntas' ? 'text-emerald-400' : 'text-rose-400' }}">{{ ucfirst($cell['status']) }}</span>
                                            @else
                                                <span class="text-slate-500 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2">
                                        <textarea name="catatan[{{ $row['siswa']->id }}]" rows="2" placeholder="Perkembangan akademik, saran remedial/pengayaan..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 placeholder:text-slate-500">{{ $catatanAkademikMap[$row['siswa']->id]->catatan ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Catatan Akademik</button>
                </div>
                </form>
            @endif
        </div>
    @empty
        <p class="text-slate-400 text-sm">Anda belum menjadi guru wali (akademik) untuk kelas manapun.</p>
    @endforelse
</div>
