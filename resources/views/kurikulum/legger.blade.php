<div id="pane-kurikulum-legger" class="pane-content fade-transition">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h4 class="font-bold m-0 text-slate-100">Matriks Matrik Legger Akhir Semester</h4>
            <p class="text-slate-400 small m-0">Peninjauan terperinci berdasarkan hierarki
                struktural nilai.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="approveLegger()" id="btn-approve-legger"
                class="btn btn-success btn-sm rounded-lg font-bold">
                <i class="fa-solid fa-file-signature mr-1"></i> Validasi & Kunci Legger
            </button>
            <button onclick="triggerToast('Legger diexport ke CSV')"
                class="btn btn-outline-secondary btn-sm rounded-lg">
                <i class="fa-solid fa-file-export"></i> Export
            </button>
        </div>
    </div>

    <!-- Hierarchical Navigation Filters (HTML Form Element) -->
    <form onsubmit="event.preventDefault(); filterAcademicGrid();"
        class="row g-2 mb-4 bg-slate-800/50 p-3 rounded-lg border text-slate-100">
        <div class="col-6 col-md-3">
            <label class="form-label small font-semibold">Tahun Ajaran</label>
            <select id="filter-academic-year" class="form-select form-select-sm"
                onchange="filterAcademicGrid()">
                <option value="2026_2027">T.A 2026/2027</option>
                <option value="2025_2026">T.A 2025/2026</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small font-semibold">Semester</label>
            <select id="filter-semester" class="form-select form-select-sm"
                onchange="filterAcademicGrid()">
                <option value="ganjil">Ganjil (1)</option>
                <option value="genap">Genap (2)</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small font-semibold">Kelas Rombel</label>
            <select id="filter-class" class="form-select form-select-sm"
                onchange="filterAcademicGrid()">
                <option value="all">Semua Kelas</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small font-semibold">Mata Pelajaran</label>
            <select id="filter-subject" class="form-select form-select-sm"
                onchange="filterAcademicGrid()">
                <option value="all">Semua Mapel (Legger Rerata)</option>
                @foreach($mataPelajarans as $mapel)
                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
        <div class="flex justify-between items-center mb-3">
            <span class="small font-bold uppercase text-slate-400"
                id="legger-grid-heading">Matriks Legger — Semua Kelas</span>
            <span class="badge bg-warning badge-pill-custom" id="legger-status-badge">Draft
                (Menunggu Validasi Waka)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover table-striped align-middle"
                id="legger-validation-table">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-100" id="legger-validation-thead">
                    <tr>
                        <th>Siswa</th>
                        <th class="text-center">Kelas</th>
                        @foreach($mataPelajarans as $mapel)
                            <th class="text-center legger-mapel-col" data-mapel-id="{{ $mapel->id }}">{{ Str::limit($mapel->nama_mapel, 20) }}</th>
                        @endforeach
                        <th class="text-center">Rerata Rapor</th>
                        <th>Status Ketuntasan</th>
                    </tr>
                </thead>
                <tbody id="legger-validation-tbody">
                    @foreach($siswas as $siswa)
                        @php
                            // Ambil nilai aktual dari database per mata pelajaran
                            $nilaiPerMapel = [];
                            $totalNilai = 0;
                            $jumlahMapelDenganNilai = 0;

                            foreach ($mataPelajarans as $mapel) {
                                $nilaiAkhir = isset($nilaiAkhirMap[$siswa->id.'-'.$mapel->id]) ? round($nilaiAkhirMap[$siswa->id.'-'.$mapel->id]->nilai_akhir) : null;
                                $nilaiPerMapel[$mapel->id] = $nilaiAkhir;

                                if ($nilaiAkhir !== null) {
                                    $totalNilai += $nilaiAkhir;
                                    $jumlahMapelDenganNilai++;
                                }
                            }

                            $avg = $jumlahMapelDenganNilai > 0
                                ? number_format($totalNilai / $jumlahMapelDenganNilai, 1)
                                : '-';
                            $isTuntas = $jumlahMapelDenganNilai > 0 && ($totalNilai / $jumlahMapelDenganNilai) >= setting('kktp_threshold', 75);
                        @endphp
                        <tr class="legger-row" data-kelas-id="{{ $siswa->kelas_id }}">
                            <td class="font-bold">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: {{ $siswa->nis }}</span></td>
                            <td class="text-center small text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            @foreach($mataPelajarans as $mapel)
                                <td class="text-center font-monospace legger-mapel-cell" data-mapel-id="{{ $mapel->id }}">
                                    @if($nilaiPerMapel[$mapel->id] !== null)
                                        <span class="{{ $nilaiPerMapel[$mapel->id] < setting('kktp_threshold', 75) ? 'text-danger font-bold' : '' }}">{{ $nilaiPerMapel[$mapel->id] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center font-bold text-primary font-monospace">{{ $avg }}</td>
                            <td>
                                @if($jumlahMapelDenganNilai === 0)
                                    <span class="badge bg-secondary-subtle text-slate-400 badge-pill-custom">Belum Ada Nilai</span>
                                @elseif($isTuntas)
                                    <span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">Tuntas Rapor</span>
                                @else
                                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 badge-pill-custom">Belum Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-700/60">
            <span id="legger-page-info" class="text-sm text-slate-400">Menampilkan 0 dari 0 data</span>
            <div class="flex gap-1" id="legger-page-buttons">
                <!-- Buttons injected by JS -->
            </div>
        </div>
    </div>

    <!-- AUDIT LOG PANEL -->
    <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 mt-4 text-slate-100">
        <h6 class="font-bold text-slate-100 mb-3"><i
                class="fa-solid fa-list-check text-info mr-1"></i> Log Audit Perubahan Nilai &
            Catatan</h6>
        <div class="overflow-x-auto" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-hover table-striped align-middle table-sm"
                style="font-size: 11px;">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th>Waktu</th>
                        <th>Aktor</th>
                        <th>Deskripsi Aksi</th>
                        <th>Status/Impact</th>
                    </tr>
                </thead>
                <tbody id="academic-audit-log-tbody">
                    <!-- Dynamic audit logs -->
                </tbody>
            </table>
        </div>
    </div>
</div>
