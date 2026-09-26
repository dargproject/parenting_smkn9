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
@endif

@if($guru->roles->contains('name', 'waka_kesiswaan') || $guru->roles->contains('name', 'guru_mapel'))
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
    <div id="pane-bk-asesmen" class="pane-content hidden-pane fade-transition">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Asesmen Psikologis & Minat Bakat</h4>
                <p class="text-slate-400 small m-0">Evaluasi berkala tingkat stress akademik dan
                    pemetaan karir siswa.</p>
            </div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors" data-bs-toggle="modal"
                data-bs-target="#addAsesmenModal">
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
                        </tr>
                    </thead>
                    <tbody id="bk-asesmen-tbody">
                        @foreach($siswas as $siswa)
                            @php
                                $badgeClass = 'bg-success';
                                if ($siswa->counseling_stress >= 8) $badgeClass = 'bg-danger';
                                elseif ($siswa->counseling_stress >= 5) $badgeClass = 'bg-warning text-slate-100';
                            @endphp
                            <tr>
                                <td class="font-bold">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">{{ $siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                <td class="text-center"><span class="badge {{ $badgeClass }} badge-pill-custom">Stress Level: {{ $siswa->counseling_stress }}/10</span></td>
                                <td><span class="badge bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 badge-pill-custom">{{ $siswa->counseling_career ?? 'Belum ditentukan' }}</span></td>
                                <td class="small text-slate-400 italic">"{{ $siswa->counseling_note ?? '-' }}"</td>
                                <td><span class="confidential-badge"><i class="fa-solid fa-lock"></i> RAHASIA</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors"
                    data-bs-toggle="modal" data-bs-target="#addBKCaseModal">
                    <i class="fa-solid fa-plus mr-1"></i> Kasus Baru
                </button>
                <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>
                    RAHASIA BK - Akses Terbatas</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
            <!-- Antrean -->
            <div class="col-span-1">
                <div class="kanban-col">
                    <h6
                        class="font-bold text-slate-400 mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                        Antrean Masuk</h6>
                    <div id="kanban-antrean" class="flex flex-col gap-2">
                        @foreach($kasusBks->where('status', 'antrean') as $kasus)
                            <div class="kanban-card text-slate-100">
                                <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase mb-1" style="font-size: 8px;">{{ $kasus->kategori }}</span>
                                <h6 class="font-bold text-slate-100 mb-1" style="font-size: 13px;">{{ $kasus->judul }}</h6>
                                <p class="text-slate-400 mb-2" style="font-size: 11px;">Siswa: {{ $kasus->siswa->nama ?? '-' }}</p>
                                <button class="btn btn-outline-primary btn-sm w-full py-1 font-semibold" style="font-size: 10px;">Proses Kasus <i class="fa-solid fa-arrow-right"></i></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- Proses -->
            <div class="col-span-1">
                <div class="kanban-col">
                    <h6
                        class="font-bold text-primary mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                        Sedang Diproses</h6>
                    <div id="kanban-proses" class="flex flex-col gap-2">
                        @foreach($kasusBks->where('status', 'proses') as $kasus)
                            <div class="kanban-card text-slate-100">
                                <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase mb-1" style="font-size: 8px;">{{ $kasus->kategori }}</span>
                                <h6 class="font-bold text-slate-100 mb-1" style="font-size: 13px;">{{ $kasus->judul }}</h6>
                                <p class="text-slate-400 mb-2" style="font-size: 11px;">Siswa: {{ $kasus->siswa->nama ?? '-' }}</p>
                                <button class="btn btn-outline-success btn-sm w-full py-1 font-semibold" style="font-size: 10px;">Tutup Kasus <i class="fa-solid fa-check"></i></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- Selesai -->
            <div class="col-span-1">
                <div class="kanban-col">
                    <h6
                        class="font-bold text-success mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                        Selesai (Ditutup)</h6>
                    <div id="kanban-selesai" class="flex flex-col gap-2">
                        @foreach($kasusBks->where('status', 'selesai') as $kasus)
                            <div class="kanban-card text-slate-100">
                                <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase mb-1" style="font-size: 8px;">{{ $kasus->kategori }}</span>
                                <h6 class="font-bold text-slate-100 mb-1" style="font-size: 13px;">{{ $kasus->judul }}</h6>
                                <p class="text-slate-400 mb-2" style="font-size: 11px;">Siswa: {{ $kasus->siswa->nama ?? '-' }}</p>
                                <span class="badge bg-secondary-subtle text-slate-400 w-full block text-center py-1">Kasus Ditutup</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="pane-bk-riwayat" class="pane-content hidden-pane fade-transition">
        <h4 class="font-bold text-slate-100 mb-2">Cari Jejak Rekam Siswa</h4>
        <p class="text-slate-400 small mb-4">Lacak seluruh riwayat penanganan, poin pelanggaran, dan
            catatan prestasi siswa.</p>

        <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="col-md-9">
                    <select class="form-select" id="bk-search-student-dropdown"
                        onchange="loadStudentHistoryBK()">
                        <!-- Option list injected -->
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
                            <option value="Ringan">Poin Ringan</option>
                            <option value="Atribut">Pelanggaran Atribut</option>
                            <option value="Bolos">Sikap Membolos</option>
                            <option value="Penghargaan">Prestasi / Penghargaan</option>
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
                        <h6 class="font-bold mb-2">Profil Siswa</h6>
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
    <div id="pane-wali-dashboard" class="pane-content hidden-pane fade-transition">
        <div class="card bg-dark text-white border-0 rounded-xl p-4 shadow-sm mb-4">
            <div class="flex items-center">
                <div class="col-md-8">
                    <h5 class="m-0 text-slate-400 small uppercase tracking-wider">Perkembangan Kelas
                        Wali</h5>
                    <h3 class="font-bold m-0 mt-1">XI TKJ 1 (Teknik Komputer & Jaringan)</h3>
                    <p class="m-0 text-slate-400 mt-1 small">Wali Kelas: <span
                            id="wali-dashboard-name">Ibu Siti Rahmawati</span> | Total: 36 Siswa</p>
                </div>
                <div class="col-span-1 md:text-right mt-3 md:mt-0">
                    <span class="text-slate-400 small block">Rerata Kehadiran Harian</span>
                    <h2 class="font-bold text-success m-0">97.6%</h2>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100">
            <div class="col-md-6">
                <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full">
                    <h6 class="font-bold text-slate-100 mb-3"><i
                            class="fa-solid fa-calendar-check text-success mr-1"></i> Rangkuman
                        Presensi Rombel (Hari Ini)</h6>
                    <div class="flex flex-col gap-2" id="wali-attendance-summary">
                        <!-- Injected Attendance summary stats -->
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div
                    class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full border-t border-slate-700/60 border-rose-500 border-4">
                    <h6 class="font-bold text-danger mb-3"><i
                            class="fa-solid fa-circle-exclamation mr-1"></i> Perlu Perhatian Khusus
                        (Rujuk ke BK)</h6>
                    <ul class="list-group list-group-flush" id="wali-warning-list">
                        <!-- Warning list of students -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="pane-wali-presensi" class="pane-content hidden-pane fade-transition">
        <div class="flex justify-between items-center mb-3">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Rekapitulasi & Verifikasi Presensi Rombel
                </h4>
                <p class="text-slate-400 small m-0">Konsolidasi otomatis presensi mata pelajaran
                    hari ini (25 Agustus 2026).</p>
            </div>
            <button onclick="triggerToast('Menyinkronkan data presensi KBM...')"
                class="btn btn-outline-secondary btn-sm rounded-lg">
                <i class="fa-solid fa-arrows-rotate"></i> Sinkron Mapel
            </button>
        </div>

        <!-- Wali Exception/Verification Cards Panel -->
        <div
            class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 mb-4 text-slate-100 border-t border-slate-700/60 border-amber-500 border-4">
            <h6 class="font-bold text-slate-100 mb-2"><i
                    class="fa-solid fa-user-shield text-warning mr-1"></i> Verifikasi Ketidakhadiran
                Siswa (Exceptions Review)</h6>
            <p class="text-slate-400 small mb-3">Tinjau ketidakcocokan atau ketidakhadiran pada jam
                pelajaran tertentu.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400 table-sm" style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Mapel / Jam</th>
                            <th>Pengampu</th>
                            <th>Alasan Asli</th>
                            <th>Status Verifikasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="wali-exceptions-tbody">
                        <!-- Dynamic exceptions entries -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Consolidated Aggregation Grid -->
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <h6 class="font-bold text-slate-100 mb-3"><i
                    class="fa-solid fa-table-cells mr-1 text-primary"></i> Matriks Kehadiran Harian
                Rombel (XI TKJ 1)</h6>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400 table-bordered text-center"
                    style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                        <tr>
                            <th class="text-start" style="min-width: 170px;">Nama Siswa</th>
                            <th>Matematika<br><span class="text-slate-400"
                                    style="font-size: 10px;">07:00 - 08:30</span></th>
                            <th>B. Indonesia<br><span class="text-slate-400"
                                    style="font-size: 10px;">08:30 - 10:00</span></th>
                            <th>Jaringan (Prod)<br><span class="text-slate-400"
                                    style="font-size: 10px;">10:15 - 12:15</span></th>
                            <th>Pemrograman<br><span class="text-slate-400"
                                    style="font-size: 10px;">13:00 - 15:00</span></th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody id="wali-aggregated-presensi-tbody">
                        <!-- Dynamically consolidated from raw database -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="pane-wali-catatan" class="pane-content hidden-pane fade-transition">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Input Catatan Perkembangan Raport</h4>
                <p class="text-slate-400 small m-0">Catatan wali kelas untuk dicantumkan pada lembar
                    e-Rapor akhir.</p>
            </div>
            <button onclick="saveWaliNotes()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Semua Catatan
            </button>
        </div>

        <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80">
            <div class="overflow-x-auto">
                <table class="table align-middle table-hover text-slate-100">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Catatan Wali Kelas</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="wali-catatan-tbody">
                        @foreach($siswas as $siswa)
                            <tr>
                                <td class="font-bold text-start">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: {{ $siswa->nis }}</span></td>
                                <td>
                                    <textarea id="walinote-{{ $siswa->nis }}" rows="2" class="form-control form-control-sm text-slate-100" placeholder="Masukkan catatan pembinaan sikap dan kedisiplinan siswa...">{{ $siswa->catatan_wali }}</textarea>
                                </td>
                                <td class="text-center">
                                    <button onclick="openEscalationReferralModal({{ $siswa->nis }}, '{{ $siswa->nama }}')" class="btn btn-warning btn-xs py-1 px-2 rounded-2" style="font-size: 11px;" title="Rujuk Kasus"><i class="fa-solid fa-share-from-square"></i> Rujuk BK</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="pane-wali-chat" class="pane-content hidden-pane fade-transition">
        <h4 class="font-bold text-slate-100 mb-2">Pusat Komunikasi Orang Tua</h4>
        <p class="text-slate-400 small mb-4">Konsultasi langsung dengan wali murid kelas XI TKJ 1.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
            <div class="col-span-1">
                <div class="card border-0 rounded-xl shadow-sm bg-slate-800/80 p-3">
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Pilih Chat Orang Tua:</label>
                        <select id="chat-parent-selector"
                            class="form-select form-select-sm font-semibold text-slate-100"
                            onchange="switchTeacherChatConversation()">
                            <option value="ortu_andi_wali" selected>Bpk. Budi (Ortu Andi Susanto)
                            </option>
                            <option value="ortu_dodi_wali">Ibu Ningsih (Ortu Dodi Hermawan)</option>
                            <option value="ortu_eko_wali">Bpk. Joko (Ortu Eko Saputro)</option>
                        </select>
                    </div>
                    <div class="list-group list-group-flush" id="chat-users-list">
                        <!-- Chat user status selection -->
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 flex flex-col text-slate-100"
                    style="height: 420px;">
                    <div class="flex items-center gap-2 border-b border-slate-700/60 pb-3 mb-3">
                        <div class="avatar-circle font-bold bg-primary text-white"
                            style="width: 38px; height: 38px;">B</div>
                        <div>
                            <h6 class="font-bold text-slate-100 m-0" id="chat-header-name">Bpk. Budi
                                Susanto</h6>
                            <span class="text-success small" style="font-size: 11px;"><i
                                    class="fa-solid fa-circle text-success"
                                    style="font-size: 8px;"></i> Online (Orang Tua Andi)</span>
                        </div>
                    </div>

                    <!-- Message display -->
                    <div class="chat-container flex flex-col flex-grow-1"
                        id="chat-messages-container">
                        <!-- Chat bubbles dynamically injected -->
                    </div>

                    <div class="flex gap-2 mt-3">
                        <input type="text" id="chat-input-text"
                            onkeypress="handleChatKeyPress(event)"
                            class="form-control form-control-sm rounded-pill px-3"
                            placeholder="Ketik pesan konsultasi...">
                        <button onclick="sendMessage()"
                            class="btn btn-brand-primary btn-sm rounded-circle px-3"><i
                                class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
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
                            id="gw-dashboard-name">{{ $guru->nama }}</span> | {{ $siswaBinaan->count() }} Siswa | Target KKM: 75
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
                            class="fa-solid fa-graduation-cap mr-1"></i> Siswa Dibawah KKM (Perlu
                        Remedial)</h6>
                    <ul class="list-group list-group-flush" id="gw-remedial-list">
                        @foreach($siswaBinaan as $siswa)
                            @php
                                // Simulasi nilai untuk menentukan remedial
                                $math = rand(60, 95);
                                $indo = rand(60, 95);
                                $jaringan = rand(60, 95);
                                $pemrograman = rand(60, 95);
                                $remedialSubjects = [];
                                if ($math < 75) $remedialSubjects[] = 'MTK';
                                if ($indo < 75) $remedialSubjects[] = 'B.Indo';
                                if ($jaringan < 75) $remedialSubjects[] = 'Jaringan';
                                if ($pemrograman < 75) $remedialSubjects[] = 'Prog';
                            @endphp
                            @if(count($remedialSubjects) > 0)
                                <li class="list-group-item flex justify-between items-center px-0 py-2">
                                    <div>
                                        <span class="font-bold block small" style="font-size: 13px;">{{ $siswa->nama }}</span>
                                        <span class="text-slate-400" style="font-size: 10px;">Gagal KKM: {{ implode(', ', $remedialSubjects) }}</span>
                                    </div>
                                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 font-semibold" style="font-size: 10px;">Remedial</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="pane-guru-wali-legger" class="pane-content hidden-pane fade-transition">
        <div class="flex justify-between items-center mb-3">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Legger Nilai & Evaluasi Rombel</h4>
                <p class="text-slate-400 small m-0">Tinjau, perbarui, dan validasi sebaran nilai
                    mata pelajaran untuk e-Rapor.</p>
            </div>
            <div class="flex gap-2">
                <select id="gw-filter-class"
                    class="form-select form-select-sm w-auto text-slate-100"
                    onchange="filterAcademicGridGW()">
                    <option value="all">Semua Kelas</option>
                    @foreach($kelasBinaan as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                <select id="gw-filter-subject"
                    class="form-select form-select-sm w-auto text-slate-100"
                    onchange="filterAcademicGridGW()">
                    <option value="Jaringan">Administrasi Jaringan</option>
                    <option value="Pemrograman">Pemrograman Web</option>
                    <option value="Math">Matematika</option>
                    <option value="Indo">B. Indonesia</option>
                </select>
                <button onclick="saveGuruWaliRemarks()"
                    class="btn btn-brand-primary btn-sm rounded-lg">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Catatan
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <h6 class="font-bold text-slate-100 mb-3" id="gw-grid-heading"><i
                    class="fa-solid fa-table-cells mr-1 text-primary"></i> Daftar Nilai Rombel:
                {{ $kelasBinaan->pluck('nama_kelas')->implode(', ') ?: '-' }} | Mapel Binaan</h6>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400 table-sm text-center"
                    style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                        <tr>
                            <th class="text-start" style="min-width: 150px;">Nama Siswa</th>
                            <th>Tugas 1 (20%)</th>
                            <th>Tugas 2 (20%)</th>
                            <th>UTS (30%)</th>
                            <th>UAS (30%)</th>
                            <th>Rerata Akhir</th>
                            <th>Ketuntasan</th>
                            <th>Catatan Guru Wali</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="guru-wali-legger-tbody">
                        @foreach($siswaBinaan as $siswa)
                            @php
                                $score = rand(65, 95);
                                $isTuntas = $score >= 75;
                            @endphp
                            <tr class="gw-legger-row" data-kelas-id="{{ $siswa->kelas_id }}">
                                <td class="font-bold text-start">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: {{ $siswa->nis }}</span></td>
                                <td class="text-center font-monospace">{{ $score - 3 }}</td>
                                <td class="text-center font-monospace">{{ $score - 1 }}</td>
                                <td class="text-center font-monospace">{{ $score + 1 }}</td>
                                <td class="text-center font-bold text-primary font-monospace">
                                    <input type="number" id="gw-grade-{{ $siswa->nis }}" class="form-control form-control-sm text-center font-monospace" style="width: 70px; margin: 0 auto;" value="{{ $score }}">
                                </td>
                                <td>
                                    <span class="badge {{ $isTuntas ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }} badge-pill-custom">
                                        {{ $isTuntas ? 'Tuntas' : 'Remedial' }}
                                    </span>
                                </td>
                                <td>
                                    <textarea id="gw-remark-{{ $siswa->nis }}" rows="1" class="form-control form-control-sm text-slate-400" style="font-size: 12px; min-width: 150px;" readonly placeholder="Klik 'Ubah Catatan'...">{{ $siswa->catatan_akademik }}</textarea>
                                </td>
                                <td class="text-center">
                                    <div class="flex gap-1 justify-center">
                                        <button onclick="openGuruWaliRemarkModal({{ $siswa->nis }}, '{{ $siswa->nama }}')" class="btn btn-brand-primary btn-xs py-1 px-2 rounded-2" title="Kelola Catatan"><i class="fa-solid fa-edit"></i></button>
                                        <button onclick="openEscalationReferralModal({{ $siswa->nis }}, '{{ $siswa->nama }}')" class="btn btn-warning btn-xs py-1 px-2 rounded-2" title="Eskalasi Rujukan"><i class="fa-solid fa-triangle-exclamation"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="pane-guru-wali-rapor" class="pane-content hidden-pane fade-transition">
        <div class="flex justify-between items-center mb-3">
            <div>
                <h4 class="font-bold m-0 text-slate-100">Cetak & Validasi Lembar e-Rapor</h4>
                <p class="text-slate-400 small m-0">Pastikan seluruh nilai mapel tuntas sebelum
                    e-Rapor diterbitkan.</p>
            </div>
            <button onclick="triggerToast('Menyinkronkan data rapor ke server pusdatin...')"
                class="btn btn-outline-secondary btn-sm rounded-lg">
                <i class="fa-solid fa-arrows-rotate"></i> Sinkron Pusat
            </button>
        </div>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400 text-slate-100">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Rerata GPA</th>
                            <th class="text-center">Mapel Remedial</th>
                            <th class="text-center">Status Legger</th>
                            <th class="text-center">Catatan Akademik</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="guru-wali-rapor-tbody">
                        @foreach($siswaBinaan as $siswa)
                            @php
                                // Simulasi nilai untuk menentukan remedial
                                $math = rand(60, 95);
                                $indo = rand(60, 95);
                                $jaringan = rand(60, 95);
                                $pemrograman = rand(60, 95);
                                $remedialCount = 0;
                                if ($math < 75) $remedialCount++;
                                if ($indo < 75) $remedialCount++;
                                if ($jaringan < 75) $remedialCount++;
                                if ($pemrograman < 75) $remedialCount++;
                                $average = number_format(($math + $indo + $jaringan + $pemrograman) / 4, 1);
                            @endphp
                            <tr>
                                <td class="font-bold text-start">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: {{ $siswa->nis }}</span></td>
                                <td class="font-monospace font-bold">{{ $average }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $remedialCount === 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }} badge-pill-custom">
                                        {{ $remedialCount }} Mapel Belum Tuntas
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $remedialCount === 0 ? 'bg-success text-white' : 'bg-danger text-white' }} badge-pill-custom">
                                        {{ $remedialCount === 0 ? 'Siap Terbit' : 'Ditangguhkan' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-slate-400 small italic text-truncate d-inline-block" style="max-width: 180px;">{{ $siswa->catatan_akademik ?? 'Belum ada catatan.' }}</span>
                                </td>
                                <td class="text-center">
                                    <button onclick="downloadReport('{{ $siswa->nama }}')" class="btn btn-outline-primary btn-xs py-1 px-2 text-xs" {{ $remedialCount === 0 ? '' : 'disabled' }}><i class="fa-solid fa-file-pdf"></i> Unduh e-Rapor</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('guru.penilaian.wali-matrix')
    @include('guru.penilaian.wali-catatan')
    @include('guru.penilaian.wali-rilis')
@endif

@if($guru->roles->contains('name', 'guru_mapel'))
    <!-- ========================================== -->
    <!-- GURU MATA PELAJARAN (GURU MAPEL) VIEWS -->
    <!-- ========================================== -->
    @include('guru.penilaian.mapel-input')

    <div id="pane-guru-mapel-dashboard" class="pane-content hidden-pane fade-transition">

        <!-- Jadwal Mengajar Guru Mapel -->
        <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
            <h5 class="font-bold text-slate-100 mb-2"><i
                    class="fa-regular fa-clock text-info mr-1"></i> Jadwal Mengajar Anda (Hari Ini -
                Selasa)</h5>
            <p class="text-slate-400 small mb-3">Silakan pilih jadwal untuk mengisi Jurnal Mengajar
                dan Presensi.</p>
            <div class="overflow-x-auto">
                <table class="table table-hover table-striped align-middle table-sm"
                    style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Jam Ke / Waktu</th>
                            <th>Kelas Rombel</th>
                            <th>Mata Pelajaran</th>
                            <th>Ruang / Lab</th>
                            <th class="text-center">Status Jurnal</th>
                        </tr>
                    </thead>
                    <tbody id="guru-mapel-schedules-tbody">
                        @foreach($jadwalPelajarans->where('guru_id', $guru->id) as $jadwal)
                            @php
                                // Simulasi status jurnal
                                $isLogged = rand(0, 1) == 1;
                                $statusText = $isLogged
                                    ? '<span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Jurnal Terisi</span>'
                                    : '<span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20">Belum di-Jurnal</span>';
                            @endphp
                            <tr>
                                <td class="font-bold">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                                <td>{{ $jadwal->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $jadwal->ruang }}</span></td>
                                <td class="text-center">{!! $statusText !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subject-Based Teaching Journal & Attendance Input (Teacher Role) -->
        <div
            class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100 border-t border-slate-700/60 border-blue-500 border-4">
            <h5 class="font-bold text-slate-100 mb-2"><i
                    class="fa-solid fa-calendar-plus text-primary mr-1"></i> Formulir Jurnal
                Mengajar & Presensi Mapel</h5>
            <p class="text-slate-400 small mb-3">Isi rincian materi KBM dan absensi siswa sesuai
                jadwal pelajaran Anda.</p>

            <form onsubmit="event.preventDefault(); submitSubjectKBMJournal();"
                class="row g-2 mb-3 bg-slate-800/50 p-3 rounded border">
                <div class="col-md-6">
                    <label class="form-label small font-semibold">Pilih Jadwal Mengajar</label>
                    <select id="gw-attn-schedule-select"
                        class="form-select form-select-sm text-slate-100"
                        onchange="loadGWStudentListForAttendance()">
                        <option value="">-- Pilih Rencana Jadwal Mengajar Anda --</option>
                        @foreach($jadwalPelajarans->where('guru_id', $guru->id) as $jadwal)
                            <option value="{{ $jadwal->id }}">{{ $jadwal->hari }} • {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }} | Rombel: {{ $jadwal->kelas->nama_kelas ?? '-' }} ({{ $jadwal->mataPelajaran->nama_mapel ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small font-semibold">Materi Pembelajaran / Kompetensi
                        Dasar</label>
                    <input type="text" id="gw-attn-material" class="form-control form-control-sm"
                        placeholder="Contoh: Konfigurasi routing static, CRUD PHP..." required>
                </div>
                <div class="col-md-6 mt-2">
                    <label class="form-label small font-semibold"><i class="fa-solid fa-camera"></i>
                        Dokumentasi Foto Kelas (Mengajar)</label>
                    <input type="file" id="gw-attn-photo-file"
                        class="form-control form-control-sm text-slate-100"
                        onchange="simulatePhotoUploadPreview()">
                </div>
                <div class="col-md-6 mt-2 flex items-end" id="photo-preview-container"
                    style="display: none !important;">
                    <span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 py-2 w-full border text-center"
                        style="font-size: 11px;">
                        <i class="fa-solid fa-image mr-1"></i> Foto_Mengajar_SMK.jpg (Berhasil
                        Diunggah)
                    </span>
                </div>
            </form>

            <div class="overflow-x-auto mt-3 hidden-pane" id="gw-attn-input-container">
                <h6 class="font-bold small text-slate-100 mb-2"><i
                        class="fa-solid fa-users mr-1 text-primary"></i> Daftar Presensi Rombel</h6>
                <table class="w-full text-left text-sm text-slate-400 table-sm" style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 250px;">Kehadiran</th>
                            <th>Keterangan / Alasan Khusus</th>
                        </tr>
                    </thead>
                    <tbody id="gw-attn-input-tbody">
                        <!-- Injected dynamically -->
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <button type="button" onclick="submitSubjectKBMJournal()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Jurnal & Presensi
                    </button>
                </div>
            </div>
        </div>

        <!-- List of past teaching journals -->
        <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
            <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-book mr-1 text-info"></i>
                Riwayat Jurnal Mengajar Anda</h6>
            <div class="overflow-x-auto">
                <table class="table table-hover table-striped align-middle table-sm"
                    style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Tanggal</th>
                            <th>Materi Pembelajaran</th>
                            <th>Kelas</th>
                            <th>Jam</th>
                            <th>Foto Mengajar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="guru-mapel-journals-tbody">
                        @php
                            $myJournals = \App\Models\JurnalMengajar::with(['jadwalPelajaran.kelas'])->where('guru_id', $guru->id)->get();
                        @endphp
                        @forelse($myJournals as $jurnal)
                            <tr>
                                <td class="font-monospace small">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('Y-m-d') }}</td>
                                <td class="font-bold">{{ $jurnal->materi }}</td>
                                <td>{{ $jurnal->jadwalPelajaran->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $jurnal->jadwalPelajaran->jam_mulai ?? '-' }}</td>
                                <td><span class="badge bg-secondary-subtle text-slate-400 py-1 px-2.5"><i class="fa-solid fa-image"></i> {{ $jurnal->foto ?? 'Tidak ada foto' }}</span></td>
                                <td><span class="badge bg-success badge-pill-custom">Terverifikasi</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-400 small py-3">Belum ada riwayat pengisian jurnal mengajar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
