@php
    $user = Auth::user();
    $roles = session('roles', []);
    $initials = $user ? strtoupper(substr($user->nama, 0, 2)) : 'U';
    $primaryRole = session('role', 'Guru');
    $tatibRendered = false;
@endphp
<nav id="sidebar-wrapper" class="fixed inset-y-0 left-0 z-[9999] w-64 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out bg-slate-800/80 border-r border-slate-700/60 flex flex-col flex-shrink-0 text-slate-100">
    <div class="sidebar-heading border-b border-slate-700/60 border-slate-700/60 d-flex items-center gap-2 py-4">
        <i class="fa-solid fa-graduation-cap text-info fs-3"></i>
        <span class="fs-5 font-bold tracking-tight">SI SMK Negeri 9</span>
    </div>

    <!-- User summary inside sidebar -->
    <div class="p-3 border-b border-slate-700/60 border-slate-700/60 text-center bg-slate-800/50">
        <div class="avatar-circle mx-auto mb-2 uppercase fs-5" id="sb-user-avatar">{{ $initials }}</div>
        <h6 class="m-0 font-bold small text-truncate" id="sb-user-name">{{ $user->nama ?? 'User' }}</h6>
        <span class="badge bg-slate-700/50 text-slate-400 font-semibold uppercase mt-1"
            style="font-size: 9px;" id="sb-user-role">{{ str_replace('_', ' ', $primaryRole) }}</span>
    </div>

    <!-- Sidebar Menus -->
    <div class="list-group list-group-flush flex-grow-1 overflow-y-auto" id="sidebar-menu-list">
        @if(in_array('admin', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Administrator</div>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-chart-line w-6 text-center mr-2"></i> <span>Dashboard Admin</span></a>
            <a href="{{ route('admin.settings.edit') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-gear w-6 text-center mr-2"></i> <span>Pengaturan Sekolah</span></a>
            <a href="{{ route('admin.tahun-ajaran.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-calendar-days w-6 text-center mr-2"></i> <span>Tahun Ajaran</span></a>
            <a href="{{ route('admin.guru.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-chalkboard-user w-6 text-center mr-2"></i> <span>Guru & Staf</span></a>
            <a href="{{ route('admin.siswa.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-user-graduate w-6 text-center mr-2"></i> <span>Siswa</span></a>
            <a href="{{ route('admin.kelas.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-school w-6 text-center mr-2"></i> <span>Master Kelas</span></a>
            <a href="{{ route('admin.mata-pelajaran.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-book w-6 text-center mr-2"></i> <span>Mata Pelajaran</span></a>
            <a href="{{ route('admin.rombel.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-people-roof w-6 text-center mr-2"></i> <span>Plotting Rombel</span></a>
            <a href="{{ route('admin.master-pelanggaran.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-triangle-exclamation w-6 text-center mr-2"></i> <span>Katalog Pelanggaran</span></a>
            <a href="{{ route('admin.kategori-pengumuman.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-tags w-6 text-center mr-2"></i> <span>Kategori Pengumuman</span></a>
            <a href="{{ route('admin.kategori-kasus.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-folder-tree w-6 text-center mr-2"></i> <span>Kategori Kasus BK</span></a>
            <a href="{{ route('admin.import.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-file-import w-6 text-center mr-2"></i> <span>Import Data</span></a>
        @endif

        @if(in_array('kepsek', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Kepala Sekolah</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kepsek-dashboard', this)"><i class="fa-solid fa-chart-line w-6 text-center mr-2"></i> <span>Dashboard Global</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kepsek-akademik', this)"><i class="fa-solid fa-square-poll-vertical w-6 text-center mr-2"></i> <span>Pantau Akademik</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kepsek-kesiswaan', this)"><i class="fa-solid fa-shield-halved w-6 text-center mr-2"></i> <span>Pantau Kesiswaan</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kepsek-laporan', this)"><i class="fa-solid fa-file-contract w-6 text-center mr-2"></i> <span>Laporan Eksekutif</span></a>
        @endif

        @if(in_array('waka_kurikulum', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Waka Kurikulum</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kurikulum-legger', this)"><i class="fa-solid fa-file-signature w-6 text-center mr-2"></i> <span>Validasi Legger</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kurikulum-struktur', this)"><i class="fa-solid fa-sitemap w-6 text-center mr-2"></i> <span>Struktur Kurikulum</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kurikulum-wali', this)"><i class="fa-solid fa-user-tie w-6 text-center mr-2"></i> <span>Wali Kelas &amp; Guru Wali</span></a>
            {{-- <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kurikulum-rapor', this)"><i class="fa-solid fa-file-pdf w-6 text-center mr-2"></i> <span>Cetak e-Rapor</span></a> --}}
        @endif

        @if(in_array('waka_kesiswaan', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Waka Kesiswaan</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kesiswaan-absensi', this)"><i class="fa-solid fa-user-clock w-6 text-center mr-2"></i> <span>Absensi Bermasalah</span></a>

            @include('components.sidebar-tatib')
            @php $tatibRendered = true; @endphp
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kesiswaan-petugas', this)"><i class="fa-solid fa-user-shield w-6 text-center mr-2"></i> <span>Petugas Tatib</span></a>

            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-kesiswaan-ortu', this)"><i class="fa-solid fa-handshake w-6 text-center mr-2"></i> <span>Panggilan Ortu</span></a>
        @endif

        @if(in_array('guru_bk', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Bimbingan Konseling</div>
            <a href="{{ route('guru.bk.dashboard') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> <span>Dashboard BK</span></a>
            <a href="{{ route('guru.bk.kasus.index') }}" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors"><i class="fa-solid fa-folder-open w-6 text-center mr-2"></i> <span>Kasus BK</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-bk-asesmen', this)"><i class="fa-solid fa-clipboard-question w-6 text-center mr-2"></i> <span>Asesmen Psikologis</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-bk-kanban', this)"><i class="fa-solid fa-list-check w-6 text-center mr-2"></i> <span>Kanban Tindak Lanjut</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-bk-riwayat', this)"><i class="fa-solid fa-address-card w-6 text-center mr-2"></i> <span>Jejak Rekam Siswa</span></a>
        @endif

        @if(in_array('wali_kelas', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Wali Kelas</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-wali-dashboard', this)"><i class="fa-solid fa-users w-6 text-center mr-2"></i> <span>Dashboard Kelas</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-wali-presensi', this)"><i class="fa-solid fa-calendar-check w-6 text-center mr-2"></i> <span>Rekap Presensi Mapel</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-wali-catatan', this)"><i class="fa-solid fa-pen-clip w-6 text-center mr-2"></i> <span>Catatan Wali Kelas</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-wali-chat', this)"><i class="fa-solid fa-comments w-6 text-center mr-2"></i> <span>Komunikasi Ortu</span></a>
        @endif

        @if(in_array('guru_wali', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Guru Wali Akademik</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-wali-dashboard', this)"><i class="fa-solid fa-chart-simple w-6 text-center mr-2"></i> <span>Dashboard Akademik</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-wali-nilai-matrix', this)"><i class="fa-solid fa-table-cells w-6 text-center mr-2"></i> <span>Rekap Nilai Rombel</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-wali-log', this)"><i class="fa-solid fa-clock-rotate-left w-6 text-center mr-2"></i> <span>Log Perubahan Nilai</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-wali-rilis', this)"><i class="fa-solid fa-lock w-6 text-center mr-2"></i> <span>Finalisasi & Rilis Nilai</span></a>
        @endif

        @if(in_array('tatib', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Petugas Tatib</div>
            @unless($tatibRendered)
                @include('components.sidebar-tatib')
                @php $tatibRendered = true; @endphp
            @endunless
        @endif

        @if(in_array('guru_mapel', $roles))
            <div class="px-3 py-2 text-slate-400 small font-bold uppercase tracking-wider mt-2">Guru Mapel</div>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-mapel-penilaian', this)"><i class="fa-solid fa-square-poll-vertical w-6 text-center mr-2"></i> <span>Input Nilai Sumatif</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-mapel-dashboard', this)"><i class="fa-solid fa-book-open w-6 text-center mr-2"></i> <span>Jurnal & Presensi</span></a>
            <a href="#" class="flex items-center px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors" onclick="gotoPane('pane-guru-mapel-riwayat', this)"><i class="fa-solid fa-calendar-days w-6 text-center mr-2"></i> <span>Riwayat Jurnal</span></a>

            @unless($tatibRendered)
                @include('components.sidebar-tatib')
            @endunless
        @endif
    </div>

</nav>
<script>try { if (window.innerWidth >= 768 && localStorage.getItem('sidebarCollapsed') === '1') document.getElementById('sidebar-wrapper').classList.add('sidebar-collapsed'); } catch (e) {}</script>
