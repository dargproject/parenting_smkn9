<div id="pane-guru-mapel-penilaian" class="pane-content hidden-pane fade-transition space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Input Nilai Sumatif</h4>
        <p class="text-slate-400 text-sm">Input nilai Sumatif Lingkup Materi (LM), Sumatif Akhir Semester (SAS), catatan capaian kompetensi, dan nilai PKL/UKK untuk mapel yang Anda ampu.</p>
    </div>

    @if(!$tahunAjaranAktif)
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-400">Belum ada tahun ajaran aktif. Hubungi admin untuk mengaktifkan tahun ajaran terlebih dahulu.</div>
    @elseif($mapelBinaan->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum ditugaskan mengampu mata pelajaran apapun.</p>
    @else
        @php
            $jadwalBinaan = $jadwalPelajarans->where('guru_id', $guru->id)->unique(fn ($j) => $j->mata_pelajaran_id.'-'.$j->kelas_id);
        @endphp

        @forelse($jadwalBinaan as $jadwal)
            @php
                $mapel = $jadwal->mataPelajaran;
                $kelas = $jadwal->kelas;
                $siswaKelas = $siswas->where('kelas_id', $kelas?->id)->values();
                $tpMapel = $tujuanPembelajarans->where('mata_pelajaran_id', $mapel?->id)->values();
            @endphp
            @continue(!$mapel || !$kelas)

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 space-y-5">
                <h5 class="text-lg font-bold text-slate-100">{{ $mapel->nama_mapel }} &middot; {{ $kelas->nama_kelas }}</h5>

                {{-- Kelola Tujuan Pembelajaran --}}
                <div class="rounded-xl border border-slate-700/60 bg-slate-900 p-4">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm">Tujuan Pembelajaran (TP) Semester Ini</h6>
                    @if($tpMapel->isNotEmpty())
                        <div class="flex flex-col gap-1 mb-3">
                            @foreach($tpMapel as $tp)
                                <div class="flex items-center justify-between text-sm text-slate-400">
                                    <span><span class="font-semibold text-slate-100">{{ $tp->kode ?: 'TP' }}</span> &mdash; {{ $tp->deskripsi }}</span>
                                    <form method="POST" action="{{ route('guru.penilaian.tp.destroy', $tp) }}" onsubmit="return confirm('Hapus TP ini? Nilai LM terkait juga akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-400 hover:text-rose-300 text-xs">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-400 text-sm mb-3">Belum ada TP untuk mapel ini.</p>
                    @endif
                    <form method="POST" action="{{ route('guru.penilaian.tp.store') }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                        <input type="text" name="kode" placeholder="Kode (Nilai 1 / N1 )" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 sm:w-40">
                        <input type="text" name="deskripsi" required placeholder="Deskripsi TP, mis. Konfigurasi Router Mikrotik" class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 whitespace-nowrap">+ Tambah TP</button>
                    </form>
                </div>

                @if($tpMapel->isNotEmpty())
                {{-- Nilai LM --}}
                <form method="POST" action="{{ route('guru.penilaian.nilai-lm.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm">Nilai Sumatif Lingkup Materi</h6>
                    <div class="overflow-x-auto rounded-xl border border-slate-700/60">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Nama Siswa</th>
                                    @foreach($tpMapel as $tp)
                                        <th class="px-3 py-2 text-center">{{ $tp->kode ?: 'TP'.$loop->iteration }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @foreach($siswaKelas as $siswa)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                                        @foreach($tpMapel as $tp)
                                            @php $nl = $nilaiLmBinaan[$siswa->id.'-'.$tp->id] ?? null; @endphp
                                            <td class="px-3 py-2">
                                                <input type="number" min="0" max="100" name="nilai[{{ $siswa->id }}][{{ $tp->id }}]" value="{{ $nl->nilai ?? '' }}" class="w-16 rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 text-center">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Nilai LM</button>
                    </div>
                </form>
                @endif

                {{-- Nilai SAS --}}
                <form method="POST" action="{{ route('guru.penilaian.nilai-sas.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm">Nilai Sumatif Akhir Semester (opsional)</h6>
                    <div class="overflow-x-auto rounded-xl border border-slate-700/60">
                        <table class="w-full min-w-[400px] text-left text-sm">
                            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                                <tr><th class="px-3 py-2">Nama Siswa</th><th class="px-3 py-2 text-center">Nilai SAS</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @foreach($siswaKelas as $siswa)
                                    @php $sas = $nilaiSasBinaan[$siswa->id.'-'.$mapel->id] ?? null; @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" min="0" max="100" name="nilai_sas[{{ $siswa->id }}]" value="{{ $sas->nilai ?? '' }}" class="w-20 rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 text-center">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Nilai SAS</button>
                    </div>
                </form>

                {{-- Catatan Capaian Kompetensi --}}
                {{-- <form method="POST" action="{{ route('guru.penilaian.catatan-kompetensi.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm">Catatan Capaian Kompetensi</h6>
                    <div class="flex flex-col gap-2">
                        @foreach($siswaKelas as $siswa)
                            @php $ck = $catatanKompetensiBinaan[$siswa->id.'-'.$mapel->id] ?? null; @endphp
                            <div class="flex flex-col sm:flex-row gap-2 sm:items-start">
                                <span class="text-sm font-semibold text-slate-100 sm:w-40 sm:pt-2">{{ $siswa->nama }}</span>
                                <textarea name="catatan[{{ $siswa->id }}]" rows="2" placeholder="Materi yang sudah dikuasai / masih perlu bimbingan..." class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ $ck->catatan ?? '' }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Catatan</button>
                    </div>
                </form> --}}

                @if($mapel->kategori === 'Kejuruan')
                {{-- PKL/UKK --}}
                {{-- <form method="POST" action="{{ route('guru.penilaian.nilai-pkl-ukk.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm">Nilai PKL &amp; UKK (Mapel Kejuruan)</h6>
                    <div class="overflow-x-auto rounded-xl border border-slate-700/60">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Nama Siswa</th>
                                    <th class="px-3 py-2 text-center">Nilai PKL</th>
                                    <th class="px-3 py-2 text-center">Nilai UKK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @foreach($siswaKelas as $siswa)
                                    @php
                                        $pkl = $nilaiPklUkkBinaan[$siswa->id.'-'.$mapel->id.'-pkl'] ?? null;
                                        $ukk = $nilaiPklUkkBinaan[$siswa->id.'-'.$mapel->id.'-ukk'] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" min="0" max="100" name="nilai[{{ $siswa->id }}][pkl]" value="{{ $pkl->nilai ?? '' }}" class="w-20 rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 text-center">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" min="0" max="100" name="nilai[{{ $siswa->id }}][ukk]" value="{{ $ukk->nilai ?? '' }}" class="w-20 rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 text-center">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Nilai PKL/UKK</button>
                    </div>
                </form> --}}
                @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">Belum ada jadwal mengajar tercatat untuk Anda.</p>
        @endforelse
    @endif
</div>
