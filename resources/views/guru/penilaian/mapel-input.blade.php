<div id="pane-guru-mapel-penilaian" class="pane-content hidden-pane fade-transition space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Input Nilai Sumatif</h4>
        <p class="text-slate-400 text-sm">Pilih kelas dan mata pelajaran yang Anda ampu, lalu input nilai Sumatif Lingkup Materi. Nilai SAS dihitung otomatis dari rata-rata.</p>
    </div>

    @if(!$tahunAjaranAktif)
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-400">Belum ada tahun ajaran aktif. Hubungi admin untuk mengaktifkan tahun ajaran terlebih dahulu.</div>
    @elseif($mapelBinaan->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum ditugaskan mengampu mata pelajaran apapun.</p>
    @else
        @php
            $jadwalBinaan = $kelasMapelBinaan;
        @endphp

        @php
            $kunciValid = $jadwalBinaan->filter(fn ($j) => $j->mataPelajaran && $j->kelas)->map(fn ($j) => $j->mata_pelajaran_id.'-'.$j->kelas_id)->values();
        @endphp
        <div x-data="{ kunci: 'gmSel:{{ $guru->id }}', valid: @js($kunciValid), sel: null, init() { try { const s = localStorage.getItem(this.kunci); this.sel = this.valid.includes(s) ? s : null } catch (e) {} }, pilih(k) { this.sel = k; try { k ? localStorage.setItem(this.kunci, k) : localStorage.removeItem(this.kunci) } catch (e) {} } }" class="space-y-6">

        <div x-show="!sel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($jadwalBinaan as $j)
                @continue(!$j->mataPelajaran || !$j->kelas)
                <button type="button" @click="pilih('{{ $j->mata_pelajaran_id }}-{{ $j->kelas_id }}')" class="text-left rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 hover:border-blue-500 transition">
                    <div class="text-xs text-slate-400 mb-1">{{ $j->mataPelajaran->kategori }}</div>
                    <div class="text-base font-bold text-slate-100">{{ $j->mataPelajaran->nama_mapel }}</div>
                    <div class="text-sm text-slate-300 mt-1"><i class="fa-solid fa-users mr-1"></i> {{ $j->kelas->nama_kelas }} &middot; {{ $siswas->where('kelas_id', $j->kelas_id)->count() }} siswa</div>
                    <div class="text-xs text-blue-400 mt-3 font-semibold">Input nilai &rarr;</div>
                </button>
            @endforeach
        </div>

        @forelse($jadwalBinaan as $jadwal)
            @php
                $mapel = $jadwal->mataPelajaran;
                $kelas = $jadwal->kelas;
                $siswaKelas = $siswas->where('kelas_id', $kelas?->id)->values();
                $tpMapel = $tujuanPembelajarans->where('mata_pelajaran_id', $mapel?->id)->values();
            @endphp
            @continue(!$mapel || !$kelas)

            <div x-show="sel === '{{ $mapel->id }}-{{ $kelas->id }}'" style="display:none" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h5 class="text-lg font-bold text-slate-100">{{ $mapel->nama_mapel }} &middot; {{ $kelas->nama_kelas }}</h5>
                    <button type="button" @click="pilih(null)" class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-arrow-left mr-1"></i> Ganti Kelas/Mapel</button>
                </div>

                {{-- Kelola Tujuan Pembelajaran --}}
                <div class="rounded-xl border border-slate-700/60 bg-slate-900 p-4">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white mr-1">1</span> Daftar Nilai (N) Semester Ini</h6>
                    @if($tpMapel->isNotEmpty())
                        <div class="flex flex-col gap-1 mb-3">
                            @foreach($tpMapel as $tp)
                                <div class="flex items-center justify-between text-sm text-slate-400">
                                    <span><span class="font-semibold text-slate-100">{{ $tp->kode ?: 'N' }}</span> &mdash; {{ $tp->deskripsi }}</span>
                                    <form method="POST" action="{{ route('guru.penilaian.tp.destroy', $tp) }}" onsubmit="return confirm('Hapus N ini? Nilai siswa yang terkait juga akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-400 hover:text-rose-300 text-xs">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-400 text-sm mb-3">Belum ada N. Tambahkan minimal satu N di bawah, lalu kolom nilai siswa akan muncul di langkah 2.</p>
                    @endif
                    <form method="POST" action="{{ route('guru.penilaian.tp.store') }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                        <input type="text" name="kode" placeholder="Kode, mis. N1" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 sm:w-40">
                        <input type="text" name="deskripsi" required placeholder="Deskripsi N, mis. Konfigurasi Router Mikrotik" class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 whitespace-nowrap">+ Tambah N</button>
                    </form>
                </div>

                @if($tpMapel->isNotEmpty())
                {{-- Nilai LM --}}
                <form method="POST" action="{{ route('guru.penilaian.nilai-lm.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-2 text-sm"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white mr-1">2</span> Nilai Sumatif Lingkup Materi</h6>
                    <div class="overflow-x-auto rounded-xl border border-slate-700/60">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Nama Siswa</th>
                                    @foreach($tpMapel as $tp)
                                        <th class="px-3 py-2 text-center">{{ $tp->kode ?: 'N'.$loop->iteration }}</th>
                                    @endforeach
                                    <th class="px-3 py-2 text-center">Nilai (Rata-rata)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @foreach($siswaKelas as $siswa)
                                    @php
                                        $nilaiRow = $tpMapel->map(fn ($tp) => $nilaiLmBinaan[$siswa->id.'-'.$tp->id]->nilai ?? null)->filter(fn ($v) => $v !== null);
                                        $avgAwal = $nilaiRow->isNotEmpty() ? round($nilaiRow->avg()) : '-';
                                    @endphp
                                    <tr x-data="{ avg: @js($avgAwal), hitung() { const a = [...this.$el.querySelectorAll('input[type=number]')].map(i => i.value).filter(v => v !== '').map(Number); this.avg = a.length ? Math.round(a.reduce((x, y) => x + y, 0) / a.length) : '-' } }" @input="hitung()">
                                        <td class="px-3 py-2 font-semibold text-slate-100">{{ $siswa->nama }}</td>
                                        @foreach($tpMapel as $tp)
                                            @php $nl = $nilaiLmBinaan[$siswa->id.'-'.$tp->id] ?? null; @endphp
                                            <td class="px-3 py-2 text-center">
                                                <input type="number" min="0" max="100" name="nilai[{{ $siswa->id }}][{{ $tp->id }}]" value="{{ $nl->nilai ?? '' }}" class="w-16 rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-sm text-slate-100 text-center">
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-2 text-center font-bold text-slate-100" x-text="avg"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Nilai LM</button>
                    </div>
                </form>
                @else
                <div class="rounded-xl border border-dashed border-slate-600 p-4 text-sm text-slate-400">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-600 text-xs text-white mr-1">2</span>
                    Input nilai sumatif ({{ $siswaKelas->count() }} siswa) akan tersedia setelah Anda menambahkan N pada langkah 1.
                </div>
                @endif

                {{-- Catatan tambahan (deskripsi capaian dibuat otomatis) --}}
                <form method="POST" action="{{ route('guru.penilaian.catatan-kompetensi.store') }}">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <h6 class="font-bold text-slate-100 mb-1 text-sm"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white mr-1">3</span> Catatan Tambahan <span class="font-normal text-slate-400">(opsional)</span></h6>
                    <p class="text-xs text-slate-400 mb-2">Deskripsi capaian dibuat otomatis dari nilai per N dan tampil di rapor orang tua. Isi kolom di bawah hanya bila ada catatan khusus.</p>
                    <div class="flex flex-col gap-2">
                        @foreach($siswaKelas as $siswa)
                            @php $ck = $catatanKompetensiBinaan[$siswa->id.'-'.$mapel->id] ?? null; @endphp
                            <div class="flex flex-col sm:flex-row gap-2 sm:items-start">
                                <div class="sm:w-56"><span class="block text-sm font-semibold text-slate-100 sm:pt-2">{{ $siswa->nama }}</span>
                                    @php
                                        $otomatis = app(\App\Services\PenilaianService::class)->deskripsiCapaian($tpMapel->map(fn ($tp) => ($nl = $nilaiLmBinaan[$siswa->id.'-'.$tp->id] ?? null) ? ['nama' => $tp->deskripsi, 'nilai' => $nl->nilai] : null)->filter());
                                    @endphp
                                    <span class="block text-xs text-slate-400">{{ $otomatis ?? 'Belum ada nilai.' }}</span></div>
                                <textarea name="catatan[{{ $siswa->id }}]" rows="2" placeholder="Catatan tambahan (opsional)..." class="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ $ck->catatan ?? '' }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Catatan</button>
                    </div>
                </form>

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
        </div>
    @endif
</div>
