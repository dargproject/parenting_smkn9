@extends('layouts.app')

@section('content')

<div class="mb-4">
    @include('admin.partials.flash')
</div>

@if($guru->roles->contains('name', 'waka_kurikulum'))
    <!-- ========================================== -->
    <!-- WAKA KURIKULUM VIEWS -->
    <!-- ========================================== -->
    @include('kurikulum.legger')
    @include('kurikulum.struktur')
    @include('kurikulum.wali')
    @include('kurikulum.rapor')
@endif

@if($guru->roles->contains('name', 'waka_kesiswaan'))
    <!-- ========================================== -->
    <!-- WAKA KESISWAAN VIEWS -->
    <!-- ========================================== -->
    @include('kesiswaan.absensi')
    @include('kesiswaan.ortu')
    @include('kesiswaan.petugas-tatib')
@endif

@if($guru->roles->contains('name', 'waka_kesiswaan') || $guru->roles->contains('name', 'guru_mapel') || $guru->roles->contains('name', 'tatib'))
    <!-- ========================================== -->
    <!-- TATIB (KESISWAAN & GURU MAPEL) VIEWS -->
    <!-- ========================================== -->
    @include('kesiswaan.tatib-jenis')
    @include('kesiswaan.tatib-catat')
    @include('kesiswaan.tatib-rekap')
@endif

@if($guru->roles->contains('name', 'guru_bk'))
    <!-- ========================================== -->
    <!-- BK (BIMBINGAN KONSELING) VIEWS -->
    <!-- ========================================== -->
    <div id="pane-bk-asesmen" class="pane-content hidden-pane fade-transition" x-data="{ modal: false, f: { siswa_id: '', tingkat_stres: 5, minat_karir: '', catatan: '' },
            baru() { this.f = { siswa_id: '', tingkat_stres: 5, minat_karir: '', catatan: '' }; this.modal = true; },
            ubah(d) { this.f = { siswa_id: d.siswa, tingkat_stres: d.stres || 5, minat_karir: d.minat || '', catatan: d.catatan || '' }; this.modal = true; } }">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Asesmen Psikologis & Minat Bakat</h4>
                <p class="text-slate-400 small m-0">Evaluasi berkala tingkat stress akademik dan
                    pemetaan karir siswa.</p>
            </div>
            <button type="button" @click="baru()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-plus mr-1"></i> Asesmen Baru
            </button>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th class="text-center">Stress Level</th>
                            <th>Minat Lanjutan</th>
                            <th>Catatan BK</th>
                            <th>Kerahasiaan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bk-asesmen-tbody">
                        @foreach($siswas as $siswa)
                            @php
                                $asesmen = $asesmenBkMap[$siswa->id] ?? null;
                                $badgeClass = 'bg-success';
                                if (($asesmen->tingkat_stres ?? 0) >= 8) $badgeClass = 'bg-danger';
                                elseif (($asesmen->tingkat_stres ?? 0) >= 5) $badgeClass = 'bg-warning text-slate-100';
                            @endphp
                            <tr>
                                <td class="font-bold">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">{{ $siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge {{ $badgeClass }} badge-pill-custom">Stress Level: {{ $asesmen->tingkat_stres ?? '-' }}/10</span></td>
                                <td><span class="badge bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 badge-pill-custom">{{ $asesmen->minat_karir ?? 'Belum ditentukan' }}</span></td>
                                <td class="small text-slate-400 italic">"{{ $asesmen->catatan ?? '-' }}"</td>
                                <td><span class="confidential-badge"><i class="fa-solid fa-lock"></i> RAHASIA</span></td>
                                <td class="text-center">
                                    <button type="button" @click="ubah({{ Illuminate\Support\Js::from(['siswa' => $siswa->id, 'stres' => $asesmen->tingkat_stres ?? null, 'minat' => $asesmen->minat_karir ?? '', 'catatan' => $asesmen->catatan ?? '']) }})" class="btn btn-outline-primary btn-xs py-0.5 px-2" style="font-size: 11px;" title="Ubah asesmen"><i class="fa-solid fa-pen"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="modal" style="display: none; z-index: 10000;" class="fixed inset-0 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="modal = false">
            <div class="solid-panel w-full max-w-md rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl p-5 max-h-[90vh] overflow-y-auto" @click.outside="modal = false">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="font-bold text-slate-100 m-0">Asesmen BK</h5>
                    <button type="button" @click="modal = false" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('guru.bk.asesmen.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                        @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'bind' => 'f.siswa_id'])
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Tingkat Stres: <span class="font-bold text-slate-100" x-text="f.tingkat_stres"></span>/10</label>
                        <input type="range" name="tingkat_stres" min="1" max="10" x-model="f.tingkat_stres" class="w-full">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Minat Lanjutan</label>
                        <select name="minat_karir" x-model="f.minat_karir" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                            <option value="">Belum ditentukan</option>
                            <option>Bekerja di Industri</option>
                            <option>Melanjutkan Kuliah</option>
                            <option>Wirausaha Mandiri</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Catatan BK</label>
                        <textarea name="catatan" rows="3" x-model="f.catatan" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modal = false" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Asesmen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="pane-bk-kanban" class="pane-content hidden-pane fade-transition">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Kanban Tindak Lanjut Konseling</h4>
                <p class="text-slate-400 small m-0">Alur penanganan kasus BK secara konseptual.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors"
                    onclick="document.getElementById('modal-kasus-baru-kanban').style.display='flex'">
                    <i class="fa-solid fa-plus mr-1"></i> Kasus Baru
                </button>
                <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>
                    RAHASIA BK - Akses Terbatas</span>
            </div>
        </div>

        @php
            $kolomKanban = [
                'antrean' => ['label' => 'Antrean Masuk', 'next' => 'proses', 'aksi' => 'Proses Kasus', 'icon' => 'fa-arrow-right'],
                'proses' => ['label' => 'Sedang Diproses', 'next' => 'selesai', 'aksi' => 'Tutup Kasus', 'icon' => 'fa-check'],
                'selesai' => ['label' => 'Selesai (Ditutup)', 'next' => null, 'aksi' => null, 'icon' => null],
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
            @foreach($kolomKanban as $statusKolom => $kolom)
                <div class="col-span-1">
                    <div class="kanban-col">
                        <h6 class="font-bold text-slate-400 mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">{{ $kolom['label'] }}</h6>
                        <div id="kanban-{{ $statusKolom }}" class="flex flex-col gap-2">
                            @foreach($kasusBks->where('status', $statusKolom) as $kasus)
                                <div class="kanban-card text-slate-100">
                                    <a href="{{ route('guru.bk.kasus.show', $kasus) }}" class="block hover:opacity-80">
                                        <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase mb-1" style="font-size: 8px;">{{ $kasus->kategori }}</span>
                                        <h6 class="font-bold text-slate-100 mb-1" style="font-size: 13px;">{{ $kasus->judul }}</h6>
                                        <p class="text-slate-400 mb-2" style="font-size: 11px;">Siswa: {{ $kasus->siswa->nama ?? '-' }}</p>
                                    </a>
                                    @if($kolom['next'])
                                        <form method="POST" action="{{ route('guru.bk.kasus.status', $kasus) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $kolom['next'] }}">
                                            <button type="submit" class="btn btn-outline-primary btn-sm w-full py-1 font-semibold" style="font-size: 10px;">{{ $kolom['aksi'] }} <i class="fa-solid {{ $kolom['icon'] }}"></i></button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary-subtle text-slate-400 w-full block text-center py-1">Kasus Ditutup</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @include('guru.bk.kasus._form-modal', ['formId' => 'modal-kasus-baru-kanban', 'siswas' => $siswas, 'kategoriKasusList' => $kategoriKasusList])

    <div id="pane-bk-riwayat" class="pane-content hidden-pane fade-transition">
        <h4 class="font-bold text-slate-100 mb-2">Cari Jejak Rekam Siswa</h4>
        <p class="text-slate-400 small mb-4">Lacak seluruh riwayat penanganan, poin pelanggaran, dan
            catatan prestasi siswa.</p>

        <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="col-md-9">
                    <select class="form-select" id="bk-search-student-dropdown" onchange="loadStudentHistoryBK()">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $siswa)
                            <option value="{{ $siswa->id }}">{{ $siswa->nama }} &middot; {{ $siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $siswa->nis }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="loadStudentHistoryBK()"
                        class="btn btn-brand-primary w-full">Buka Rekam Jejak</button>
                </div>
            </div>
        </div>

        <!-- Timeline and history section -->
        <div id="bk-student-history-result" class="hidden-pane text-slate-100">
            <!-- Card form inside BK Individual history to write dynamic counseling logs -->
            <form onsubmit="event.preventDefault(); submitIndividualBKLog();"
                class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4">
                <h6 class="font-bold text-slate-100 mb-3"><i
                        class="fa-solid fa-pen-nib text-info mr-1"></i> Tambah Catatan Konseling
                    Individu</h6>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="col-md-8">
                        <input type="text" id="bk-indiv-log-title"
                            class="form-control form-control-sm"
                            placeholder="Topik/Judul Konseling..." required>
                    </div>
                    <div class="col-span-1">
                        <select id="bk-indiv-log-urgency" class="form-select form-select-sm">
                            <option value="">Tanpa Kategori</option>
                            @foreach($kategoriKasusList as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-10">
                        <textarea id="bk-indiv-log-desc" rows="1"
                            class="form-control form-control-sm" placeholder="Rincian bimbingan..."
                            required></textarea>
                    </div>
                    <div class="col-md-2">
                        <button type="submit"
                            class="btn btn-brand-primary btn-sm w-full font-bold">Tambah Log</button>
                    </div>
                </div>
            </form>

            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-md-6 border-r border-slate-700/60">
                        <div class="flex items-center justify-between">
                            <h6 class="font-bold mb-2">Profil Siswa</h6>
                            <a id="bk-hist-profile-link" href="#" class="text-xs text-blue-400 hover:text-blue-300">Profil Lengkap &rarr;</a>
                        </div>
                        <h5 class="font-bold text-slate-100 m-0" id="bk-hist-name">Andi Susanto</h5>
                        <p class="text-slate-400 small m-0" id="bk-hist-class">Kelas XI TKJ 1 | NIS
                            1001</p>
                        <hr class="my-2">
                        <div class="flex justify-between small text-slate-400">
                            <span>Poin Pelanggaran:</span>
                            <span class="font-bold text-danger" id="bk-hist-points">10 Poin</span>
                        </div>
                        <div class="flex justify-between small text-slate-400">
                            <span>Persentase Kehadiran:</span>
                            <span class="font-bold text-success" id="bk-hist-attendance">98.2%</span>
                        </div>
                    </div>
                    <div class="col-md-6 ps-md-4">
                        <h6 class="font-bold mb-2">Catatan Konselor & Asesmen Terakhir</h6>
                        <p class="small text-slate-400 italic" id="bk-hist-notes">"Tingkat stres
                            sedang, perlu pembinaan terkait motivasi jurusan kejuruan."</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80">
                <h6 class="font-bold mb-4">Timeline Perilaku & Pembinaan</h6>
                <div class="position-relative ps-4" style="border-left: 2px solid #E2E8F0;"
                    id="bk-timeline-container">
                    <!-- Timeline elements injected -->
                </div>
            </div>
        </div>
    </div>
@endif

@if($guru->roles->contains('name', 'wali_kelas'))
    <!-- ========================================== -->
    <!-- WALI KELAS VIEWS -->
    <!-- ========================================== -->
    @php $jumlahSiswaWali = $siswaWali->groupBy('kelas_id')->map->count(); @endphp
    @php
        $totalPresensiWali = $rekapPresensiWali->sum('total');
        $totalHadirWali = $rekapPresensiWali->sum(fn ($r) => $r['jumlah']['H']);
        $rerataKehadiranWali = $totalPresensiWali > 0 ? round($totalHadirWali / $totalPresensiWali * 100, 1) : null;
        $jumlahHariIniWali = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
        foreach ($presensiHariDipilihWali as $p) {
            $jumlahHariIniWali[$p->status]++;
        }
        $perluPerhatianWali = $siswaWali
            ->map(fn ($s) => (object) ['siswa' => $s, 'alpa' => $rekapPresensiWali[$s->id]['jumlah']['A'] ?? 0])
            ->filter(fn ($r) => $r->alpa >= 2)
            ->sortByDesc('alpa')
            ->take(5)
            ->values();
    @endphp
    <div id="pane-wali-dashboard" class="pane-content hidden-pane fade-transition">
        @if($siswaPeringatanMingguan->isNotEmpty())
            <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 mb-4">
                <p class="font-bold text-rose-400 flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Peringatan Alpa Mingguan (&ge; {{ $ambangAlpaMingguan }}x minggu ini)</span>
                </p>
                <ul class="space-y-1">
                    @foreach($siswaPeringatanMingguan as $r)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-slate-100 font-semibold">{{ $r->siswa->nama }}</span>
                            <span class="text-rose-400 font-bold">{{ $r->alpa }}x Alpa Penuh minggu ini</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card bg-dark text-white border-0 rounded-xl p-4 shadow-sm mb-4">
            <div class="flex items-center">
                <div class="col-md-8">
                    <h5 class="m-0 text-slate-400 small uppercase tracking-wider">Perkembangan Kelas Wali</h5>
                    <h3 class="font-bold m-0 mt-1">{{ $kelasWaliList->pluck('nama_kelas')->implode(', ') ?: 'Belum ada kelas' }}</h3>
                    <p class="m-0 text-slate-400 mt-1 small">Wali Kelas: <span>{{ $guru->nama }}</span> | Total: {{ $siswaWali->count() }} Siswa</p>
                </div>
                <div class="col-span-1 md:text-right mt-3 md:mt-0">
                    <span class="text-slate-400 small block">Rerata Kehadiran Keseluruhan</span>
                    <h2 class="font-bold text-success m-0">{{ $rerataKehadiranWali !== null ? $rerataKehadiranWali.'%' : '-' }}</h2>
                </div>
            </div>
        </div>

        @if($kelasWaliList->isEmpty())
            <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai wali kelas untuk kelas manapun. Hubungi Waka Kurikulum.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100">
                <div class="col-md-6">
                    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full">
                        <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-calendar-check text-success mr-1"></i> Rangkuman Presensi Rombel
                            @if($tanggalPresensiTerbaruWali)
                                <span class="block text-slate-400 font-normal text-xs mt-0.5">Data terbaru: {{ \Carbon\Carbon::parse($tanggalPresensiTerbaruWali)->locale('id')->translatedFormat('l, d F Y') }}</span>
                            @endif
                        </h6>
                        @if(!$tanggalPresensiTerbaruWali)
                            <p class="text-slate-400 text-sm m-0">Belum ada presensi tercatat untuk kelas ini.</p>
                        @else
                            <div class="flex flex-col gap-2">
                                @foreach([['H', 'Hadir', 'emerald'], ['S', 'Sakit', 'cyan'], ['I', 'Izin', 'amber'], ['A', 'Alpa', 'rose']] as [$kode, $label, $warna])
                                    <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2">
                                        <span class="text-sm text-slate-300">{{ $label }}</span>
                                        <span class="text-sm font-bold @if($warna === 'emerald') text-emerald-400 @elseif($warna === 'cyan') text-cyan-400 @elseif($warna === 'amber') text-amber-400 @else text-rose-400 @endif">{{ $jumlahHariIniWali[$kode] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full border-t border-slate-700/60 border-rose-500 border-4">
                        <h6 class="font-bold text-danger mb-3"><i class="fa-solid fa-circle-exclamation mr-1"></i> Perlu Perhatian Khusus (Alpa &ge; 2)</h6>
                        <ul class="list-group list-group-flush">
                            @forelse($perluPerhatianWali as $r)
                                <li class="list-group-item flex justify-between items-center px-0 py-2">
                                    <span class="font-bold small" style="font-size: 13px;">{{ $r->siswa->nama }}</span>
                                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 font-semibold" style="font-size: 10px;">{{ $r->alpa }}x Alpa</span>
                                </li>
                            @empty
                                <p class="text-slate-400 text-sm m-0">Tidak ada siswa dengan alpa berulang saat ini.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="pane-wali-presensi" class="pane-content hidden-pane fade-transition" x-data="kelasXData('wk-presensi:{{ $guru->id }}', @js($kelasWaliList->pluck('id')))">
        <div class="mb-3">
            <h4 class="font-bold m-0 text-slate-100">Rekapitulasi Presensi Rombel</h4>
            <p class="text-slate-400 small m-0">Rekap kehadiran siswa dari presensi yang dicatat guru mapel pada Jurnal Mengajar.</p>
        </div>

        @if($kelasWaliList->isEmpty())
            <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai wali kelas untuk kelas manapun. Hubungi Waka Kurikulum.</p>
        @else
            <div class="mb-3">@include('guru.partials.pilih-kelas', ['daftarKelas' => $kelasWaliList, 'jumlahSiswa' => $jumlahSiswaWali])</div>

            <!-- Ringkasan Kehadiran per Siswa -->
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100 mb-4">
                <h6 class="font-bold text-slate-100 mb-1"><i class="fa-solid fa-chart-simple mr-1 text-primary"></i> Ringkasan Kehadiran per Siswa</h6>
                <p class="text-slate-400 text-xs mb-3">"Per Mata Pelajaran" menjumlahkan tiap jadwal secara terpisah. "Per Hari" memberi satu status untuk satu hari: <b>Hadir/Sakit/Izin/Alpa Penuh</b> bila semua jadwal hari itu berstatus sama, atau <b>Campuran</b> bila hasilnya beda-beda (mis. hadir di satu mapel, alpa di mapel lain).</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400 table-bordered text-center" style="font-size: 13px;">
                        <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                            <tr>
                                <th class="text-start" rowspan="2" style="min-width: 170px;">Nama Siswa</th>
                                <th colspan="5">Per Mata Pelajaran</th>
                                <th colspan="5">Per Hari</th>
                            </tr>
                            <tr>
                                <th>Hadir</th>
                                <th>Sakit</th>
                                <th>Izin</th>
                                <th>Alpa</th>
                                <th>Persentase</th>
                                <th>Hadir Penuh</th>
                                <th>Sakit Penuh</th>
                                <th>Izin Penuh</th>
                                <th>Campuran</th>
                                <th>Alpa Penuh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswaWali as $siswa)
                                @php
                                    $r = $rekapPresensiWali[$siswa->id] ?? ['jumlah' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0], 'total' => 0, 'persen' => null];
                                    $h = $rekapHarianWali[$siswa->id] ?? ['penuh' => 0, 'sakit' => 0, 'izin' => 0, 'sebagian' => 0, 'alpa' => 0, 'total_hari' => 0];
                                @endphp
                                <tr x-show="kelas == {{ $siswa->kelas_id }}">
                                    <td class="text-start font-bold text-slate-100">{{ $siswa->nama }}</td>
                                    <td class="text-emerald-400 font-semibold">{{ $r['jumlah']['H'] }}</td>
                                    <td class="text-cyan-400 font-semibold">{{ $r['jumlah']['S'] }}</td>
                                    <td class="text-amber-400 font-semibold">{{ $r['jumlah']['I'] }}</td>
                                    <td class="text-rose-400 font-semibold">{{ $r['jumlah']['A'] }}</td>
                                    <td class="font-bold text-slate-100">{{ $r['persen'] !== null ? $r['persen'].'%' : '-' }}</td>
                                    <td class="text-emerald-400 font-semibold border-l border-slate-700/60">{{ $h['penuh'] }}</td>
                                    <td class="text-cyan-400 font-semibold">{{ $h['sakit'] }}</td>
                                    <td class="text-amber-400 font-semibold">{{ $h['izin'] }}</td>
                                    <td class="text-orange-400 font-semibold">{{ $h['sebagian'] }}</td>
                                    <td class="text-rose-400 font-semibold">{{ $h['alpa'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="py-3 text-slate-400">Belum ada siswa di kelas ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Harian -->
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
                    <h6 class="font-bold text-slate-100 m-0"><i class="fa-solid fa-table-cells mr-1 text-primary"></i> Detail Kehadiran Harian</h6>
                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm text-slate-400">Tanggal:</label>
                        <input type="date" name="presensi_tanggal" value="{{ $tanggalPresensiDipilih }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
                    </form>
                </div>

                @if($jadwalHariPresensiDipilih->isEmpty())
                    <p class="text-slate-400 text-sm m-0">Tidak ada jadwal pelajaran pada hari itu untuk kelas Anda.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400 table-bordered text-center" style="font-size: 13px;">
                            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                                <tr>
                                    <th class="text-start" style="min-width: 170px;">Nama Siswa</th>
                                    @foreach($jadwalHariPresensiDipilih as $jadwal)
                                        <th>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}<br><span class="text-slate-400" style="font-size: 10px;">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswaWali as $siswa)
                                    <tr x-show="kelas == {{ $siswa->kelas_id }}">
                                        <td class="text-start font-bold text-slate-100">{{ $siswa->nama }}</td>
                                        @foreach($jadwalHariPresensiDipilih as $jadwal)
                                            @php $status = $presensiHariDipilihWali->first(fn ($p) => $p->siswa_id === $siswa->id && $p->jadwal_pelajaran_id === $jadwal->id); @endphp
                                            <td>
                                                @if(!$status)
                                                    <span class="text-slate-500">-</span>
                                                @else
                                                    @php $warna = ['H' => 'text-emerald-400', 'S' => 'text-cyan-400', 'I' => 'text-amber-400', 'A' => 'text-rose-400'][$status->status]; @endphp
                                                    <span class="font-bold {{ $warna }}">{{ $status->status }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div id="pane-wali-catatan" class="pane-content hidden-pane fade-transition" x-data="kelasXData('wk-catatan:{{ $guru->id }}', @js($kelasWaliList->pluck('id')))">
        <div class="mb-3">
            <h4 class="font-bold m-0 text-slate-100">Catatan Wali Kelas</h4>
            <p class="text-slate-400 small m-0">Catatan perkembangan karakter siswa di kelas perwalian Anda, untuk dicantumkan pada e-Rapor. Rekap Sakit/Izin/Alpa dihitung otomatis dari presensi (lihat menu Rekap Presensi Mapel untuk rinciannya).</p>
        </div>

        @if($siswaWali->isEmpty())
            <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai wali kelas untuk kelas manapun. Hubungi Waka Kurikulum.</p>
        @else
            <div class="mb-3">@include('guru.partials.pilih-kelas', ['daftarKelas' => $kelasWaliList, 'jumlahSiswa' => $jumlahSiswaWali, 'cari' => true])</div>
            <form method="POST" action="{{ route('guru.wali-kelas.catatan.store') }}" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 overflow-hidden">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Siswa</th>
                                <th class="px-3 py-2">Catatan Karakter / Perkembangan</th>
                                <th class="px-2 py-2 text-center">Sakit</th>
                                <th class="px-2 py-2 text-center">Izin</th>
                                <th class="px-2 py-2 text-center">Alpa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            @foreach($siswaWali as $siswa)
                                @php
                                    $cw = $catatanWaliKelasWali[$siswa->id] ?? null;
                                    $h = $rekapHarianWali[$siswa->id] ?? ['sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                @endphp
                                <tr x-show="kelas == {{ $siswa->kelas_id }} && (!cari || {{ \Illuminate\Support\Js::from(mb_strtolower($siswa->nama)) }}.includes(cari.toLowerCase()))">
                                    <td class="px-3 py-2 align-top font-semibold text-slate-100">{{ $siswa->nama }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">{{ $siswa->kelas->nama_kelas ?? '' }} &middot; NIS {{ $siswa->nis }}</span></td>
                                    <td class="px-3 py-2">
                                        <textarea name="catatan[{{ $siswa->id }}][catatan_karakter]" rows="2" placeholder="Sikap, kedisiplinan, dan perkembangan siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ $cw->catatan_karakter ?? '' }}</textarea>
                                    </td>
                                    <td class="px-2 py-2 align-top text-center text-cyan-400 font-semibold" title="Dihitung otomatis dari presensi, tidak bisa diketik manual">{{ $h['sakit'] }}</td>
                                    <td class="px-2 py-2 align-top text-center text-amber-400 font-semibold" title="Dihitung otomatis dari presensi, tidak bisa diketik manual">{{ $h['izin'] }}</td>
                                    <td class="px-2 py-2 align-top text-center text-rose-400 font-semibold" title="Dihitung otomatis dari presensi, tidak bisa diketik manual">{{ $h['alpa'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-slate-700/60 bg-slate-900 px-3 py-2">
                    <span class="text-xs text-slate-400">{{ $siswaWali->count() }} siswa</span>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Semua Catatan</button>
                </div>
            </form>
        @endif
    </div>
    <div id="pane-wali-chat" class="pane-content hidden-pane fade-transition" x-data="{...kelasXData('wk-pesan:{{ $guru->id }}', @js($kelasWaliList->pluck('id'))), pilih: null}">
        <div class="mb-3">
            <h4 class="font-bold m-0 text-slate-100">Komunikasi Orang Tua</h4>
            <p class="text-slate-400 small m-0">Kirim catatan/pesan ke orang tua siswa kelas perwalian Anda. Pesan tersimpan dan tampil di portal orang tua siswa terkait. Untuk percakapan langsung, gunakan tombol WhatsApp/Telepon.</p>
        </div>

        @if($siswaWali->isEmpty())
            <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai wali kelas untuk kelas manapun. Hubungi Waka Kurikulum.</p>
        @else
            <div class="mb-3">@include('guru.partials.pilih-kelas', ['daftarKelas' => $kelasWaliList, 'cari' => true])</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <div class="col-span-1">
                    <div class="rounded-xl border border-slate-700/60 bg-slate-800/80 overflow-hidden">
                        <div class="max-h-[480px] overflow-y-auto divide-y divide-slate-700/60">
                            @foreach($siswaWali as $siswa)
                                @php $jumlahPesan = ($pesanWaliKelasWali[$siswa->id] ?? collect())->count(); @endphp
                                <button type="button" data-siswa="{{ $siswa->id }}" @click="pilih = {{ $siswa->id }}"
                                    x-show="kelas == {{ $siswa->kelas_id }} && (!cari || {{ \Illuminate\Support\Js::from(mb_strtolower($siswa->nama)) }}.includes(cari.toLowerCase()))"
                                    :class="pilih === {{ $siswa->id }} ? 'bg-blue-600/20' : 'hover:bg-slate-700/40'"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2.5 text-left transition">
                                    <div class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-100">{{ $siswa->nama }}</span>
                                        <span class="block text-slate-400" style="font-size: 11px;">NIS {{ $siswa->nis }} &middot; {{ $siswa->no_hp_ortu ? 'HP terdaftar' : 'Belum ada No. HP ortu' }}</span>
                                    </div>
                                    @if($jumlahPesan > 0)
                                        <span class="flex-shrink-0 rounded-full bg-slate-700 px-2 py-0.5 text-slate-300" style="font-size: 10px;">{{ $jumlahPesan }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    @foreach($siswaWali as $siswa)
                        @php
                            $riwayatPesan = $pesanWaliKelasWali[$siswa->id] ?? collect();
                            $nomorWa = $siswa->no_hp_ortu ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $siswa->no_hp_ortu)) : null;
                        @endphp
                        <div x-show="pilih === {{ $siswa->id }}" x-cloak data-siswa-panel="{{ $siswa->id }}" class="rounded-xl border border-slate-700/60 bg-slate-800/80 p-4 text-slate-100">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-700/60 pb-3 mb-3">
                                <div>
                                    <h6 class="font-bold text-slate-100 m-0">{{ $siswa->nama }}</h6>
                                    <span class="text-slate-400" style="font-size: 11px;">{{ $siswa->kelas->nama_kelas ?? '' }} &middot; Orang tua/wali dari {{ $siswa->nama }}</span>
                                </div>
                                <div class="flex gap-2">
                                    @if($nomorWa)
                                        <a href="https://wa.me/{{ $nomorWa }}" target="_blank" rel="noopener" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500"><i class="fa-brands fa-whatsapp mr-1"></i> WhatsApp</a>
                                        {{-- <a href="tel:{{ $siswa->no_hp_ortu }}" class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-600"><i class="fa-solid fa-phone mr-1"></i> Telepon</a> --}}
                                    @else
                                        <span class="text-amber-400 text-xs italic">No. HP orang tua belum diisi di data siswa.</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('guru.wali-kelas.pesan.store') }}" class="mb-4">
                                @csrf
                                <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                <textarea name="pesan" rows="3" required maxlength="2000" placeholder="Tulis pesan/catatan untuk orang tua {{ $siswa->nama }}..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-paper-plane mr-1"></i> Kirim ke Orang Tua</button>
                                </div>
                            </form>

                            <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Riwayat Pesan Terkirim</h6>
                            <div class="flex flex-col gap-2 max-h-64 overflow-y-auto">
                                @forelse($riwayatPesan as $pesan)
                                    <div class="rounded-lg bg-slate-900/60 px-3 py-2">
                                        <p class="text-sm text-slate-100 m-0">{{ $pesan->pesan }}</p>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-slate-500" style="font-size: 10px;">{{ $pesan->created_at->translatedFormat('d M Y, H:i') }}</span>
                                            @if($pesan->dibaca_at)
                                                <span class="text-emerald-400" style="font-size: 10px;"><i class="fa-solid fa-check-double mr-1"></i>Dibaca {{ $pesan->dibaca_at->translatedFormat('d M, H:i') }}</span>
                                            @else
                                                <span class="text-slate-500" style="font-size: 10px;"><i class="fa-solid fa-check mr-1"></i>Belum dibaca</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-slate-400 text-sm italic m-0">Belum ada pesan terkirim ke orang tua siswa ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach

                    <div x-show="!pilih" class="rounded-xl border border-dashed border-slate-700/60 p-8 text-center text-slate-400 text-sm">
                        <i class="fa-solid fa-hand-point-left mr-1"></i> Pilih siswa di daftar untuk mengirim pesan atau melihat riwayat.
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

@if($guru->roles->contains('name', 'guru_wali'))
    <!-- ========================================== -->
    <!-- GURU WALI (ACADEMIC ADVISOR) VIEWS -->
    <!-- ========================================== -->
    <div id="pane-guru-wali-dashboard" class="pane-content hidden-pane fade-transition">
        <div class="card bg-brand-navy text-white border-0 rounded-xl p-4 shadow-sm mb-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h5 class="m-0 text-slate-400 small uppercase tracking-wider">Perkembangan
                        Akademik Kelas Binaan</h5>
                    <h3 class="font-bold m-0 mt-1">
                        @if($kelasBinaan->count() === 1)
                            {{ $kelasBinaan->first()->nama_kelas }} (Jurusan {{ $kelasBinaan->first()->jurusan }})
                        @elseif($kelasBinaan->count() > 1)
                            {{ $kelasBinaan->count() }} Kelas Binaan: {{ $kelasBinaan->pluck('nama_kelas')->implode(', ') }}
                        @else
                            Belum ada kelas binaan
                        @endif
                    </h3>
                    <p class="m-0 text-slate-400 mt-1 small">Guru Wali: <span
                            id="gw-dashboard-name">{{ $guru->nama }}</span> | {{ $siswaBinaan->count() }} Siswa | Target KKTP: {{ setting('kktp_threshold', 75) }}
                    </p>
                </div>
                <div class="flex-shrink-0 sm:text-right">
                    <span class="text-slate-400 small block">Rerata Nilai Rombel</span>
                    <h2 class="font-bold text-success m-0" id="gw-dashboard-average-grade">{{ $rerataBinaan ?? '-' }}</h2>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-slate-100">
            <div class="min-w-0">
                <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full">
                    <h6 class="font-bold text-slate-100 mb-3"><i
                            class="fa-solid fa-trophy text-warning mr-1"></i> Peringkat Paralel
                        Kelas (Top 3 Akademik)</h6>
                    <div class="flex flex-col gap-2" id="gw-top-students">
                        @forelse($peringkatBinaan as $i => $top)
                            <div class="flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-2">
                                <span class="text-sm font-semibold text-slate-100">{{ $i + 1 }}. {{ $top->nama }}</span>
                                <span class="text-sm font-bold text-emerald-400">{{ round($top->rata, 1) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400 small m-0">Belum ada nilai tercatat untuk kelas binaan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="min-w-0">
                <div
                    class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full border-t border-slate-700/60 border-amber-500 border-4">
                    <h6 class="font-bold text-warning mb-3"><i
                            class="fa-solid fa-graduation-cap mr-1"></i> Siswa Dibawah KKTP (Perlu
                        Remedial)</h6>
                    @php
                        $kktpDashboard = setting('kktp_threshold', 75);
                        $remedialPerSiswa = $siswaBinaan->map(function ($siswa) use ($nilaiAkhirMap, $mataPelajarans, $kktpDashboard) {
                            $mapelKurang = $nilaiAkhirMap
                                ->filter(fn ($r) => $r->siswa_id === $siswa->id && $r->nilai_akhir !== null && $r->nilai_akhir < $kktpDashboard)
                                ->map(fn ($r) => $mataPelajarans->firstWhere('id', $r->mata_pelajaran_id)?->nama_mapel)
                                ->filter()
                                ->values();

                            return ['siswa' => $siswa, 'mapel' => $mapelKurang];
                        })->filter(fn ($row) => $row['mapel']->isNotEmpty())->values();
                    @endphp
                    <ul class="list-group list-group-flush" id="gw-remedial-list">
                        @forelse($remedialPerSiswa as $row)
                            <li class="list-group-item flex justify-between items-center px-0 py-2">
                                <div>
                                    <span class="font-bold block small" style="font-size: 13px;">{{ $row['siswa']->nama }}</span>
                                    <span class="text-slate-400" style="font-size: 10px;">Gagal KKTP: {{ $row['mapel']->implode(', ') }}</span>
                                </div>
                                <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 font-semibold" style="font-size: 10px;">Remedial</span>
                            </li>
                        @empty
                            <p class="text-slate-400 small m-0">Tidak ada siswa yang di bawah KKTP saat ini.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @include('guru.penilaian.wali-matrix')
    @include('guru.penilaian.wali-log')
    @include('guru.penilaian.wali-rilis')
@endif

@if($guru->roles->contains('name', 'guru_mapel'))
    <!-- ========================================== -->
    <!-- GURU MATA PELAJARAN (GURU MAPEL) VIEWS -->
    <!-- ========================================== -->
    @include('guru.penilaian.mapel-input')
    @include('guru.jurnal.form')
    @include('guru.jurnal.riwayat')
@endif

@endsection
