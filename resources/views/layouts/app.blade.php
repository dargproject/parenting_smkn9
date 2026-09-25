<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>
      Dashboard | SMKN 9 MALANG
    </title>

    <!-- Bootstrap 5 CSS (Restored for existing functionality) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
      }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- ApexCharts (for dashboard statistics/diagrams) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

        <style>
        :root {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-header: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --bg-hover: rgba(0, 0, 0, 0.05);
            --btn-close-filter: none;
            color-scheme: light;
        }

        .dark {
            --bg-body: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.8);
            --bg-card-header: #0f172a;
            --border-color: rgba(51, 65, 85, 0.6);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --bg-hover: rgba(255, 255, 255, 0.05);
            --btn-close-filter: invert(1) grayscale(100%) brightness(200%);
            color-scheme: dark;
        }

        body {
            background-color: var(--bg-body) !important;
            color: var(--text-primary) !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Native <select> dropdown popups render with a browser-controlled (usually light)
           background that our dark-mode text color would otherwise inherit into, making
           options unreadable. Force readable colors on the popup regardless of theme. */
        select {
            color-scheme: light;
        }
        select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
        }

        /* Text Overrides */
        .text-secondary, .text-muted,
        .text-slate-300, .text-slate-400, .text-slate-500, .text-slate-600,
        .text-gray-300, .text-gray-400 {
            color: var(--text-secondary) !important;
        }
        .text-dark, .text-brand-navy, .text-slate-800, .text-slate-100 {
            color: var(--text-primary) !important;
        }
        h1, h2, h3, h4, h5, h6, .fw-bold, .font-bold {
            color: var(--text-primary) !important;
        }

        .avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confidential-badge {
            background-color: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
            border: 1px solid rgba(244, 63, 94, 0.2);
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pill-custom {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* Status Badges */
        .badge.bg-success, .badge.bg-success-subtle, .badge-hadir {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
        }

        .badge.bg-warning, .badge.bg-warning-subtle, .badge-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
            border: 1px solid rgba(245, 158, 11, 0.2) !important;
        }

        .badge.bg-danger, .badge.bg-danger-subtle, .badge-danger {
            background-color: rgba(244, 63, 94, 0.1) !important;
            color: #f43f5e !important;
            border: 1px solid rgba(244, 63, 94, 0.2) !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--text-secondary);
            border-radius: 10px;
        }

        /* Kanban Columns */
        .kanban-col {
            background-color: var(--bg-card);
            border-radius: 14px;
            padding: 12px;
            min-height: 380px;
            max-height: 480px;
            overflow-y: auto;
            border: 1px dashed var(--border-color);
        }

        .kanban-card {
            background: var(--bg-body);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: transform 0.15s ease;
            color: var(--text-primary);
        }

        .kanban-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }

        /* Custom transitions */
        .fade-transition {
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Grid systems and buttons */
        .btn-brand-primary, .btn-primary {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .btn-brand-primary:hover, .btn-primary:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        /* Hide logic */
        .hidden-pane {
            display: none !important;
        }

        /* Table Overrides */
        .table {
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        .table-light, .table thead th {
            background-color: var(--bg-card-header) !important;
            color: var(--text-secondary) !important;
            border-bottom: 1px solid var(--border-color) !important;
            border-top: none !important;
        }
        .table tbody td {
            background-color: transparent !important;
            border-bottom: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        .table-hover tbody tr:hover td {
            background-color: var(--bg-hover) !important;
            color: var(--text-primary) !important;
        }

        /* Form Controls */
        .form-control, .form-select {
            background-color: var(--bg-body) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--bg-body) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25) !important;
            color: var(--text-primary) !important;
        }
        .form-control::placeholder {
            color: var(--text-secondary) !important;
        }

        /* Modals */
        .modal-content {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color) !important;
        }
        .modal-footer {
            border-top: 1px solid var(--border-color) !important;
        }
        .btn-close {
            filter: var(--btn-close-filter);
        }

        /* List groups (Bootstrap paints items white by default, which hides light text in dark mode) */
        .list-group-item {
            background-color: transparent !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }

        /* Dropdowns */
        .dropdown-menu {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
        }
        .dropdown-item {
            color: var(--text-primary) !important;
        }
        .dropdown-item:hover {
            background-color: var(--bg-hover) !important;
            color: var(--text-primary) !important;
        }

        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 1px solid var(--border-color) !important;
        }
        .nav-tabs .nav-link {
            color: var(--text-secondary) !important;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent transparent var(--border-color) !important;
        }
        .nav-tabs .nav-link.active {
            background-color: transparent !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) var(--border-color) var(--bg-body) !important;
        }

        /* Card Overrides */
        .card, .kpi-card, .bg-slate-800\/80 {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
        }

        .border-slate-700\/60 {
            border-color: var(--border-color) !important;
        }

        /* Sidebar & Header Overrides */
        #sidebar-wrapper, .app-header {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }
        .bg-slate-800\/50 {
            background-color: var(--bg-hover) !important;
        }        .bg-slate-900 {
            background-color: var(--bg-card-header) !important;
        }

        /* Keep legacy TailAdmin utility classes readable in both themes. */
        body:not(.dark) .text-gray-900,
        body:not(.dark) .text-gray-800,
        body:not(.dark) .text-gray-700,
        body:not(.dark) .text-gray-600,
        body:not(.dark) .text-gray-500 {
            color: #334155 !important;
        }

        body.dark .text-gray-900,
        body.dark .text-gray-800,
        body.dark .text-gray-700,
        body.dark .text-gray-600,
        body.dark .text-gray-500 {
            color: #cbd5e1 !important;
        }

        body.dark .app-header .text-white,
        body.dark .app-header .dark\:text-white,
        body.dark .app-header .dark\:text-white\/90,
        body.dark #sidebar-wrapper .text-white {
            color: #f8fafc !important;
        }

        body:not(.dark) .text-slate-100,
        body:not(.dark) .dark\:text-slate-100 {
            color: #1e293b !important;
        }

        body.dark .text-slate-100,
        body.dark .dark\:text-slate-100 {
            color: #f8fafc !important;
        }

        body:not(.dark) .app-header .text-white,
        body:not(.dark) .app-header .dark\:text-white,
        body:not(.dark) .app-header .dark\:text-white\/90,
        body:not(.dark) #sidebar-wrapper .text-white {
            color: #1e293b !important;
        }

        body.dark .app-header .text-gray-800,
        body.dark .app-header .text-gray-700,
        body.dark #sidebar-wrapper .text-gray-900 {
            color: #f8fafc !important;
        }

        /* Admin views use Tailwind light utilities alongside dark variants. */
        body:not(.dark) .admin-dashboard .bg-white {
            background-color: #ffffff !important;
        }
        body:not(.dark) .admin-dashboard .text-slate-900,
        body:not(.dark) .admin-dashboard .text-slate-800,
        body:not(.dark) .admin-dashboard .text-slate-700 {
            color: #1e293b !important;
        }
        body:not(.dark) .admin-dashboard .text-slate-500 {
            color: var(--text-secondary) !important;
        }
        body:not(.dark) .admin-dashboard .text-slate-200 {
            color: #334155 !important;
        }

        body.dark .admin-dashboard .bg-white,
        body.dark .admin-dashboard .dark\:bg-slate-800\/80 {
            background-color: rgba(30, 41, 59, 0.8) !important;
        }
        body.dark .admin-dashboard .text-slate-900,
        body.dark .admin-dashboard .text-slate-800,
        body.dark .admin-dashboard .text-slate-700,
        body.dark .admin-dashboard .dark\:text-slate-100,
        body.dark .admin-dashboard .dark\:text-slate-200 {
            color: #f8fafc !important;
        }
        body.dark .admin-dashboard .text-slate-500,
        body.dark .admin-dashboard .dark\:text-slate-400 {
            color: var(--text-secondary) !important;
        }
        body.dark .admin-dashboard .border-slate-200,
        body.dark .admin-dashboard .dark\:border-slate-700\/60,
        body.dark .admin-dashboard .dark\:border-slate-700 {
            border-color: rgba(51, 65, 85, 0.6) !important;
        }

        .admin-dashboard .admin-surface {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }
        .admin-dashboard .admin-inner {
            border-color: var(--border-color) !important;
        }
        .admin-dashboard .admin-title,
        .admin-dashboard .admin-body {
            color: var(--text-primary) !important;
        }
        .admin-dashboard .admin-muted {
            color: var(--text-secondary) !important;
        }
        .admin-dashboard .admin-divider > * {
            border-color: var(--border-color) !important;
        }

        .theme-toast {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        }

        .theme-toast .toast-message {
            color: var(--text-primary) !important;
        }
    </style>
  </head>
  <body
    x-data="{ page: 'dashboard', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
         let stored = localStorage.getItem('darkMode');
         darkMode = stored ? JSON.parse(stored) : false;
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark': darkMode === true}"
  >
    <!-- ===== Preloader Start ===== -->
    @include('partials.preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden relative">

      <!-- Mobile Sidebar Overlay -->
      <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-[9998] hidden md:hidden"></div>

      <!-- ===== Sidebar Start ===== -->
      @include('components.sidebar')
      <!-- ===== Sidebar End ===== -->

      <!-- ===== Content Area Start ===== -->
      <div
        class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto"
      >
        <!-- Small Device Overlay Start -->
        @include('partials.overlay')
        <!-- Small Device Overlay End -->

        <!-- ===== Header Start ===== -->
        @include('components.header')
        <!-- ===== Header End ===== -->

        <!-- ===== Main Content Start ===== -->
        <main class="flex-1 flex flex-col">
          <div
            class="mx-auto max-w-screen-2xl p-4 pb-20 md:p-6 md:pb-6 flex-1 w-full"
          >
            @yield('content')
          </div>

          <!-- ===== Footer Start ===== -->
          @include('partials.footer')
          <!-- ===== Footer End ===== -->
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Registry so ApexCharts instances can be kept in sync with the dark/light mode toggle.
        window.__registeredCharts = window.__registeredCharts || [];
        function registerChart(chart) {
            chart.render();
            window.__registeredCharts.push(chart);
            syncChartTheme(chart);
            return chart;
        }
        function syncChartTheme(chart) {
            const isDark = document.body.classList.contains('dark');
            chart.updateOptions({ theme: { mode: isDark ? 'dark' : 'light' } }, false, false);
        }
        new MutationObserver(() => {
            window.__registeredCharts.forEach(syncChartTheme);
        }).observe(document.body, { attributes: true, attributeFilter: ['class'] });

        function triggerToast(message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'theme-toast px-4 py-3 rounded-lg flex items-center gap-3 transform transition-all duration-300 translate-y-10 opacity-0';
            toast.innerHTML = `
                <i class="fa-solid fa-circle-info text-blue-400"></i>
                <span class="toast-message text-sm font-medium">${message}</span>
            `;
            container.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Dummy functions for mockup interactivity
        function loadStudentHistoryBK() { triggerToast('Memuat riwayat BK siswa...'); }
        function saveWaliNotes() { triggerToast('Catatan wali kelas berhasil disimpan!'); }
        function openEscalationReferralModal(nis, nama) { triggerToast(`Membuka form rujukan untuk ${nama} (${nis})`); }
        function sendMessage() { triggerToast('Pesan berhasil dikirim!'); }
        function saveGuruWaliRemarks() { triggerToast('Catatan akademik berhasil disimpan!'); }
        function openGuruWaliRemarkModal(nis, nama) { triggerToast(`Membuka form catatan untuk ${nama} (${nis})`); }
        function downloadReport(nama) { triggerToast(`Mengunduh e-Rapor untuk ${nama}...`); }
        function submitSubjectKBMJournal() { triggerToast('Jurnal KBM berhasil disubmit!'); }

        // Additional dummy functions
        let currentLeggerPage = 1;
        const rowsPerLeggerPage = 5;

        function filterAcademicGrid(page = 1) {
            currentLeggerPage = page;
            const classId = document.getElementById('filter-class').value;
            const subjectId = document.getElementById('filter-subject').value;

            // Filter Columns (Mata Pelajaran)
            const mapelCols = document.querySelectorAll('.legger-mapel-col');
            const mapelCells = document.querySelectorAll('.legger-mapel-cell');

            mapelCols.forEach(col => {
                if (subjectId === 'all' || col.getAttribute('data-mapel-id') === subjectId) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            });

            mapelCells.forEach(cell => {
                if (subjectId === 'all' || cell.getAttribute('data-mapel-id') === subjectId) {
                    cell.style.display = '';
                } else {
                    cell.style.display = 'none';
                }
            });

            // Filter Rows (Kelas) & Pagination
            const allRows = Array.from(document.querySelectorAll('.legger-row'));
            const matchingRows = allRows.filter(row => classId === 'all' || row.getAttribute('data-kelas-id') === classId);

            const totalPages = Math.ceil(matchingRows.length / rowsPerLeggerPage);
            if (currentLeggerPage > totalPages && totalPages > 0) currentLeggerPage = totalPages;

            const startIndex = (currentLeggerPage - 1) * rowsPerLeggerPage;
            const endIndex = startIndex + rowsPerLeggerPage;

            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');

            // Show only rows for current page
            matchingRows.slice(startIndex, endIndex).forEach(row => {
                row.style.display = '';
            });

            // Render Pagination Controls
            renderLeggerPagination(matchingRows.length, totalPages);

            if (page === 1) {
                triggerToast('Filter Matriks Legger diterapkan!');
            }
        }

        function renderLeggerPagination(totalItems, totalPages) {
            const info = document.getElementById('legger-page-info');
            const buttons = document.getElementById('legger-page-buttons');

            if (!info || !buttons) return;

            const start = totalItems === 0 ? 0 : ((currentLeggerPage - 1) * rowsPerLeggerPage) + 1;
            const end = Math.min(currentLeggerPage * rowsPerLeggerPage, totalItems);

            info.innerHTML = `Menampilkan <span class="font-bold text-slate-100">${start}-${end}</span> dari <span class="font-bold text-slate-100">${totalItems}</span> data`;

            let html = '';

            // Prev Button
            html += `<button onclick="filterAcademicGrid(${currentLeggerPage - 1})" class="px-3 py-1 rounded-md bg-slate-800 border border-slate-700/60 text-slate-400 hover:text-slate-100 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed" ${currentLeggerPage === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left text-xs"></i></button>`;

            // Page Numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentLeggerPage) {
                    html += `<button class="px-3 py-1 rounded-md bg-blue-600 text-white font-medium">${i}</button>`;
                } else {
                    html += `<button onclick="filterAcademicGrid(${i})" class="px-3 py-1 rounded-md bg-slate-800 border border-slate-700/60 text-slate-400 hover:text-slate-100 hover:bg-slate-700">${i}</button>`;
                }
            }

            // Next Button
            html += `<button onclick="filterAcademicGrid(${currentLeggerPage + 1})" class="px-3 py-1 rounded-md bg-slate-800 border border-slate-700/60 text-slate-400 hover:text-slate-100 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed" ${currentLeggerPage === totalPages || totalPages === 0 ? 'disabled' : ''}><i class="fa-solid fa-chevron-right text-xs"></i></button>`;

            buttons.innerHTML = html;
        }

        // Initialize pagination on load
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('filter-class')) {
                filterAcademicGrid(1);
            }

            // Jika belum ada pane yang aktif (mis. peran tanpa pane default), tampilkan menu pertama yang tersedia.
            if (!document.querySelector('.pane-content:not(.hidden-pane)')) {
                const firstLink = document.querySelector('#sidebar-menu-list a[onclick*="showPane"]');
                if (firstLink) firstLink.click();
            }
        });

        function approveLegger() { triggerToast('Legger berhasil divalidasi dan dikunci!'); }
        function broadcastAnnouncementK() { triggerToast('Pengumuman Kurikulum berhasil disiarkan!'); }
        function addJpRecord() { triggerToast('Alokasi Jam Pelajaran berhasil ditambahkan!'); }
        function addScheduleRecord() { triggerToast('Jadwal KBM berhasil diterbitkan!'); }
        function deleteSchedule(id) { triggerToast('Jadwal berhasil dihapus!'); }
        function openSummonModalWithStudent(nis, nama) { triggerToast(`Membuka form panggilan ortu untuk ${nama}`); }
        function broadcastAnnouncementS() { triggerToast('Pengumuman Kesiswaan berhasil disiarkan!'); }
        function confirmSummonArrival(id) { triggerToast('Kehadiran Orang Tua berhasil dikonfirmasi!'); }
        function submitIndividualBKLog() { triggerToast('Catatan konseling berhasil disimpan!'); }
        function switchTeacherChatConversation() { triggerToast('Beralih percakapan chat...'); }
        function filterAcademicGridGW() {
            const classId = document.getElementById('gw-filter-class').value;
            const rows = document.querySelectorAll('.gw-legger-row');

            rows.forEach(row => {
                if (classId === 'all' || row.getAttribute('data-kelas-id') === classId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            triggerToast('Filter Legger Guru Wali diterapkan!');
        }

        async function loadGWStudentListForAttendance() {
            const scheduleId = document.getElementById('gw-attn-schedule-select').value;
            const container = document.getElementById('gw-attn-input-container');
            const tbody = document.getElementById('gw-attn-input-tbody');

            if (!scheduleId) {
                container.classList.add('hidden-pane');
                return;
            }

            triggerToast('Memuat daftar siswa untuk presensi...');

            try {
                const response = await fetch(`/guru/api/siswa-by-jadwal/${scheduleId}`);
                const siswas = await response.json();

                let html = '';
                siswas.forEach(siswa => {
                    html += `
                        <tr class="border-b border-slate-700/60">
                            <td class="font-bold py-2">${siswa.nama} <span class="block text-slate-400 text-xs">NIS: ${siswa.nis}</span></td>
                            <td class="text-center py-2">
                                <div class="flex justify-center gap-3">
                                    <label class="flex items-center gap-1 cursor-pointer text-emerald-400 font-medium"><input type="radio" name="attn_${siswa.id}" value="Hadir" checked class="accent-emerald-500"> H</label>
                                    <label class="flex items-center gap-1 cursor-pointer text-amber-400 font-medium"><input type="radio" name="attn_${siswa.id}" value="Izin" class="accent-amber-500"> I</label>
                                    <label class="flex items-center gap-1 cursor-pointer text-cyan-400 font-medium"><input type="radio" name="attn_${siswa.id}" value="Sakit" class="accent-cyan-500"> S</label>
                                    <label class="flex items-center gap-1 cursor-pointer text-rose-400 font-medium"><input type="radio" name="attn_${siswa.id}" value="Alpa" class="accent-rose-500"> A</label>
                                </div>
                            </td>
                            <td class="py-2">
                                <input type="text" class="form-control form-control-sm text-slate-100 bg-slate-900 border-slate-700/60" placeholder="Keterangan (opsional)">
                            </td>
                        </tr>
                    `;
                });

                tbody.innerHTML = html;
                container.classList.remove('hidden-pane');

            } catch (error) {
                console.error('Error fetching students:', error);
                triggerToast('Gagal memuat data siswa.');
            }
        }

        function simulatePhotoUploadPreview() {
            const container = document.getElementById('photo-preview-container');
            if (container) {
                container.style.setProperty('display', 'flex', 'important');
                triggerToast('Foto berhasil diunggah!');
            }
        }

        function showPane(paneId, element) {
            // Hide all panes
            document.querySelectorAll('.pane-content').forEach(pane => {
                pane.classList.add('hidden-pane');
            });
            // Show selected pane
            const targetPane = document.getElementById(paneId);
            if (targetPane) {
                targetPane.classList.remove('hidden-pane');
            }

            // Update active state on sidebar
            document.querySelectorAll('#sidebar-menu-list a').forEach(a => {
                a.classList.remove('bg-slate-800/50', 'border-l-4', 'border-blue-600', 'text-slate-100');
                a.classList.add('text-slate-400');
            });
            if (element) {
                element.classList.remove('text-slate-400');
                element.classList.add('bg-slate-800/50', 'border-l-4', 'border-blue-600', 'text-slate-100');
            }

            // Charts initialized while their pane was hidden render at zero width;
            // nudge ApexCharts to recompute its size now that the pane is visible.
            window.dispatchEvent(new Event('resize'));
        }
    </script>
  </body>
</html>

