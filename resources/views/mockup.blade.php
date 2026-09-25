<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIParenting SMK - Sistem Informasi Akademik & Kesiswaan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-navy: #1E293B;
            --brand-navy-dark: #0F172A;
            --brand-slate: #F8FAFC;
            --brand-emerald: #10B981;
            --brand-emerald-light: #D1FAE5;
            --brand-amber: #F59E0B;
            --brand-amber-light: #FEF3C7;
            --brand-rose: #F43F5E;
            --brand-rose-light: #FFE4E6;
            --brand-indigo: #6366F1;
            --brand-indigo-light: #E0E7FF;
            --brand-blue: #3B82F6;
            --brand-blue-light: #DBEAFE;
        }

        body {
            background-color: #0f172a;
            font-family: 'Inter', 'Geist', system-ui, sans-serif;
            color: #f8fafc;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Responsive Simulated Chassis Mode */
        .preview-wrapper {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .preview-wrapper.simulated-mobile {
            background-color: #64748B;
            padding: 30px 10px;
            align-items: center;
            justify-content: center;
        }

        .app-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            background-color: #0f172a;
            transition: all 0.4s ease;
        }

        .preview-wrapper.simulated-mobile .app-container {
            width: 412px;
            height: 840px;
            border: 12px solid #1E293B;
            border-radius: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Phone chassis graphics */
        .phone-notch {
            display: none;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 160px;
            height: 25px;
            background: #1E293B;
            border-b border-slate-700/60-left-radius: 18px;
            border-b border-slate-700/60-right-radius: 18px;
            z-index: 1060;
        }

        .phone-home-indicator {
            display: none;
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 130px;
            height: 5px;
            background: #94A3B8;
            border-radius: 3px;
            z-index: 1060;
        }

        .preview-wrapper.simulated-mobile .phone-notch,
        .preview-wrapper.simulated-mobile .phone-home-indicator {
            display: block;
        }

        /* Floating Role Switcher */
        .role-switcher-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        /* Sidebar Styles */
        #sidebar-wrapper {
            width: 260px;
            background-color: #1e293b;
            transition: margin 0.3s ease;
            z-index: 1000;
        }

        #sidebar-wrapper .sidebar-heading {
            background-color: #0f172a;
            padding: 1.5rem 1.25rem;
            color: #FFF;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #94A3B8;
            border: none;
            padding: 0.85rem 1.5rem;
            font-size: 0.925rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            color: #FFF;
            background-color: rgba(255, 255, 255, 0.06);
            border-left: 4px solid var(--brand-blue);
            padding-left: 1.25rem;
        }

        /* Header & Content */
        .navbar-brand-custom {
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: -0.5px;
        }

        .content-scroller {
            flex: 1;
            overflow-y: auto;
            background-color: #0f172a;
            padding: 1.5rem;
            position: relative;
        }

        /* Simulated notch spacing inside mobile chassis */
        .preview-wrapper.simulated-mobile .content-scroller {
            padding-bottom: 70px;
        }

        .preview-wrapper.simulated-mobile .app-header {
            padding-top: 20px;
        }

        /* Dashboard Cards & Elements */
        .kpi-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }

        .avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--brand-blue-light);
            color: var(--brand-blue);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confidential-badge {
            background-color: var(--brand-rose-light);
            color: var(--brand-rose);
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

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        /* Kanban Columns */
        .kanban-col {
            background-color: #0f172a;
            border-radius: 14px;
            padding: 12px;
            min-height: 380px;
            max-height: 480px;
            overflow-y: auto;
            border: 1px dashed #CBD5E1;
        }

        .kanban-card {
            background: #FFF;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .kanban-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }

        /* Live Chat Mockup styles */
        .chat-container {
            height: 320px;
            overflow-y: auto;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px;
        }

        .chat-bubble {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 16px;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .chat-bubble.sent {
            background-color: #1e293b;
            color: #FFF;
            align-self: flex-end;
            border-b border-slate-700/60-right-radius: 2px;
        }

        .chat-bubble.received {
            background-color: #E2E8F0;
            color: var(--brand-navy-dark);
            align-self: flex-start;
            border-b border-slate-700/60-left-radius: 2px;
        }

        /* Custom transitions */
        .fade-transition {
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile layout structures */
        .mobile-bottom-nav {
            background-color: #FFF;
            border-t border-slate-700/60: 1px solid #E2E8F0;
            z-index: 1050;
        }

        .mobile-bottom-nav .nav-link {
            font-size: 11px;
            color: #64748B;
            text-align: center;
            font-weight: 600;
        }

        .mobile-bottom-nav .nav-link.active {
            color: var(--brand-blue);
        }

        /* Grid systems and buttons */
        .btn-brand-primary {
            background-color: #1e293b;
            border-color: var(--brand-navy);
            color: #FFF;
            font-weight: 600;
        }

        .btn-brand-primary:hover {
            background-color: #0f172a;
            border-color: var(--brand-navy-dark);
            color: #FFF;
        }

        /* Hide logic */
        .hidden-pane {
            display: none !important;
        }

        /* Progress markers */
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            background-color: #E2E8F0;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s ease;
        }
    </style>
</head>

<body>

    <!-- ========================================== -->
    <!-- FLOATING ROLE SWITCHER & VIEWPORT CONTROLLER -->
    <!-- ========================================== -->
    <div class="role-switcher-container glass-panel p-2 rounded-xl d-flex items-center gap-2">
        <!-- Viewport Switcher -->
        <div class="btn-group btn-group-sm border-r border-slate-700/60 pe-2 mr-1" role="group">
            <button onclick="setViewport('desktop')" id="btn-vp-desktop" class="btn btn-outline-secondary active"
                title="Desktop View">
                <i class="fa-solid fa-desktop"></i>
            </button>
            <button onclick="setViewport('mobile')" id="btn-vp-mobile" class="btn btn-outline-secondary"
                title="Mobile Chassis View">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </button>
        </div>

        <!-- Role Dropdown -->
        <div class="dropdown">
            <button class="btn btn-brand-primary btn-sm dropdown-toggle d-flex items-center gap-2" type="button"
                id="roleDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-gear"></i> <span id="current-role-label">Kepala Sekolah</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end glass-panel rounded-lg border-0 shadow-lg"
                aria-labelledby="roleDropdownBtn" style="min-width: 220px;">
                <li>
                    <h6 class="dropdown-header text-slate-100 font-bold uppercase pb-1">Manajemen Sekolah</h6>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('kepsek')"><span class="badge bg-primary"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Kepala Sekolah</a>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('waka_kurikulum')"><span class="badge bg-success"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Waka Kurikulum</a>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('waka_kesiswaan')"><span class="badge bg-danger"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Waka Kesiswaan</a>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#" onclick="switchRole('bk')"><span
                            class="badge bg-info" style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span>
                        BK (Konseling)</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <h6 class="dropdown-header text-slate-100 font-bold uppercase pb-1">Tenaga Pendidik</h6>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('wali_kelas')"><span class="badge bg-warning"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Wali Kelas (Siti)</a>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('guru_wali')"><span class="badge bg-primary"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Guru Wali
                        (Hendra)</a></li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('guru_mapel')"><span class="badge bg-secondary"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Guru Mapel</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <h6 class="dropdown-header text-slate-100 font-bold uppercase pb-1">Wali & Peserta Didik</h6>
                </li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#"
                        onclick="switchRole('siswa')"><span class="badge bg-dark"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Siswa (Andi)</a></li>
                <li><a class="dropdown-item d-flex items-center gap-2" href="#" onclick="switchRole('ortu')"><span
                            class="badge bg-slate-800/50 border"
                            style="width: 8px; height: 8px; border-radius:50%; padding:0;"></span> Orang Tua (Budi)</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MAIN SIMULATOR PREVIEW WRAPPER -->
    <!-- ========================================== -->
    <div id="previewWrapper" class="preview-wrapper">
        <div class="phone-notch"></div>
        <div class="phone-home-indicator"></div>

        <div id="appContainer" class="app-container">

            <!-- B. SYSTEM LAYOUT: MAIN SHELL (SIDEBAR + CONTENT HEADER) -->
            <div id="main-layout" class="d-flex flex-row flex-grow-1 h-full">

                <!-- 1. LEFT SIDEBAR (DESKTOP MODE ONLY) -->
                <nav id="sidebar-wrapper" class="flex flex-col flex-shrink-0 text-white">
                    <div class="sidebar-heading border-b border-slate-700/60 border-secondary d-flex items-center gap-2 py-4">
                        <i class="fa-solid fa-graduation-cap text-info fs-3"></i>
                        <span class="fs-5 font-bold tracking-tight">SI SMK Negeri 9 Malang</span>
                    </div>

                    <!-- User summary inside sidebar -->
                    <div class="p-3 border-b border-slate-700/60 border-secondary/20 text-center bg-dark/20">
                        <div class="avatar-circle mx-auto mb-2 uppercase fs-5" id="sb-user-avatar">KS</div>
                        <h6 class="m-0 font-bold small text-truncate" id="sb-user-name">Bpk. Guntur, M.Pd</h6>
                        <span class="badge bg-secondary-subtle text-slate-400 font-semibold uppercase mt-1"
                            style="font-size: 9px;" id="sb-user-role">Kepala Sekolah</span>
                    </div>

                    <!-- Sidebar Menus (Injected Dynamically via Role Map) -->
                    <div class="list-group list-group-flush flex-grow-1 overflow-y-auto" id="sidebar-menu-list">
                        <!-- Dynamic list items injected -->
                    </div>

                    <!-- Footer logout button -->
                    <div class="p-3 border-t border-slate-700/60 border-secondary/20">
                        <button onclick="logout()"
                            class="btn btn-outline-danger btn-sm w-full d-flex items-center justify-content-center gap-2 py-2">
                            <i class="fa-solid fa-power-off"></i> Keluar Portal
                        </button>
                    </div>
                </nav>

                <!-- 2. CONTENT AREA -->
                <div class="flex flex-col flex-grow-1 overflow-hidden">

                    <!-- Top Bar Header -->
                    <header
                        class="app-header navbar navbar-expand-lg navbar-dark bg-brand-navy-dark text-white px-3 flex-shrink-0 border-b border-slate-700/60 border-secondary/20"
                        style="height: 64px;">
                        <div class="container-fluid p-0 d-flex items-center justify-between">

                            <!-- Toggle Sidebar Menu (for Desktop UI) -->
                            <div class="d-flex items-center gap-2">
                                <button onclick="toggleSidebar()"
                                    class="btn btn-outline-secondary btn-sm d-none d-md-inline-block text-white"
                                    id="sidebar-toggler">
                                    <i class="fa-solid fa-bars-staggered"></i>
                                </button>
                                <span class="navbar-brand-custom text-info" id="top-title-brand">SMKN 9 Malang</span>
                            </div>

                            <!-- User Info & Notifications -->
                            <div class="d-flex items-center gap-3">
                                <!-- Notifications -->
                                <div class="dropdown">
                                    <button class="btn btn-dark btn-sm rounded-circle position-relative p-2"
                                        type="button" data-bs-toggle="dropdown">
                                        <i class="fa-regular fa-bell fs-6"></i>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle p-1.5 bg-danger border border-light rounded-circle"
                                            id="notif-badge"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 glass-panel rounded-lg p-2 text-slate-100"
                                        style="width: 290px; font-size: 13px;" id="notif-dropdown">
                                        <li>
                                            <h6 class="dropdown-header text-slate-100 font-bold pb-2">Notifikasi Terbaru</h6>
                                        </li>
                                        <!-- Injected notifications -->
                                    </ul>
                                </div>

                                <!-- Mini Profile Card -->
                                <div class="d-flex items-center gap-2">
                                    <div class="text-end d-none d-sm-block">
                                        <div class="small font-bold text-white text-truncate" style="max-width: 130px;"
                                            id="header-user-fullname">Bpk. Guntur, M.Pd</div>
                                        <div class="text-slate-400" style="font-size: 10px;">T.A 2026/2027 Ganjil</div>
                                    </div>
                                    <div class="avatar-circle font-bold uppercase"
                                        style="width: 34px; height: 34px; font-size: 13px;" id="header-avatar-mini"
                                        onclick="logout()">G</div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Scrollable Page Container -->
                    <main class="content-scroller" id="content-container">

                        <!-- ========================================== -->
                        <!-- 1. KEPALA SEKOLAH VIEWS -->
                        <!-- ========================================== -->
                        <!-- PAGE: KEPSEK AKADEMIK -->
                        <div id="pane-kepsek-akademik" class="pane-content hidden-pane fade-transition">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="font-bold m-0 text-slate-100">Pantau Ketuntasan Akademik</h4>
                                    <p class="text-slate-400 small m-0">Rekapitulasi Legger Akhir Semester seluruh
                                        Rombel.</p>
                                </div>
                                <select class="form-select form-select-sm w-auto text-slate-100"
                                    id="kepsek-akademik-filter-jurusan"
                                    onchange="triggerToast('Filtering data jurusan...');">
                                    <option value="all">Semua Jurusan</option>
                                    <option value="tkj">TKJ</option>
                                    <option value="rpl">RPL</option>
                                </select>
                            </div>

                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 text-slate-100">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kelas</th>
                                                <th>Wali Kelas</th>
                                                <th class="text-center">Kapasitas</th>
                                                <th class="text-center">Rata-rata Legger</th>
                                                <th class="text-center">Persentase KKM</th>
                                                <th class="text-center">Status Validasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="font-bold">XI TKJ 1</td>
                                                <td>Ibu Siti Rahmawati</td>
                                                <td class="text-center">36 Siswa</td>
                                                <td class="text-center font-bold text-blue-400">84.2</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">88.8%
                                                        Tuntas</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success badge-pill-custom">Terverifikasi
                                                        Waka</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold">XI RPL 2</td>
                                                <td>Ibu Dwi Astuti</td>
                                                <td class="text-center">35 Siswa</td>
                                                <td class="text-center font-bold text-blue-400">80.5</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20 badge-pill-custom">71.4%
                                                        Tuntas</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning badge-pill-custom">Proses
                                                        Validasi</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold">XII AK 1</td>
                                                <td>Bpk. Anton Hidayat</td>
                                                <td class="text-center">36 Siswa</td>
                                                <td class="text-center font-bold text-blue-400">86.7</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">94.4%
                                                        Tuntas</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success badge-pill-custom">Terverifikasi
                                                        Waka</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: KEPSEK KESISWAAN -->
                        <div id="pane-kepsek-kesiswaan" class="pane-content hidden-pane fade-transition">
                            <h4 class="font-bold text-slate-100 mb-2">Pantauan Kesiswaan & Kedisiplinan</h4>
                            <p class="text-slate-400 small mb-4">Analisis kasus pelanggaran tata tertib sekolah.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100">
                                <div class="col-md-8">
                                    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80">
                                        <h6 class="font-bold text-slate-100 mb-3">Tren Pelanggaran Kesiswaan</h6>
                                        <div class="flex items-end justify-between px-3 mt-4"
                                            style="height: 160px; border-b border-slate-700/60: 2px solid #E2E8F0;">
                                            <div class="flex flex-col items-center w-full">
                                                <div class="bg-danger-subtle w-50 rounded-top"
                                                    style="height: 40px; position: relative;">
                                                    <div class="bg-danger rounded-top position-absolute bottom-0 w-full"
                                                        style="height: 100%;"></div>
                                                </div>
                                                <span class="text-slate-400 mt-2 small font-semibold">Jul</span>
                                            </div>
                                            <div class="flex flex-col items-center w-full">
                                                <div class="bg-danger-subtle w-50 rounded-top"
                                                    style="height: 80px; position: relative;">
                                                    <div class="bg-danger rounded-top position-absolute bottom-0 w-full"
                                                        style="height: 100%;"></div>
                                                </div>
                                                <span class="text-slate-400 mt-2 small font-semibold">Ags</span>
                                            </div>
                                            <div class="flex flex-col items-center w-full">
                                                <div class="bg-danger-subtle w-50 rounded-top"
                                                    style="height: 110px; position: relative;">
                                                    <div class="bg-danger rounded-top position-absolute bottom-0 w-full"
                                                        style="height: 100%;"></div>
                                                </div>
                                                <span class="text-slate-400 mt-2 small font-semibold">Sep</span>
                                            </div>
                                            <div class="flex flex-col items-center w-full">
                                                <div class="bg-danger-subtle w-50 rounded-top"
                                                    style="height: 50px; position: relative;">
                                                    <div class="bg-danger rounded-top position-absolute bottom-0 w-full"
                                                        style="height: 100%;"></div>
                                                </div>
                                                <span class="text-slate-400 mt-2 small font-semibold">Okt</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full">
                                        <h6 class="font-bold text-slate-100 mb-3">Leaderboard Pelanggaran</h6>
                                        <div class="flex flex-col gap-3" id="kepsek-violations-lead">
                                            <!-- Dynamically injected high-violation students -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: KEPSEK LAPORAN -->
                        <div id="pane-kepsek-laporan" class="pane-content hidden-pane fade-transition">
                            <!-- Announcement publishing board -->
                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
                                <h5 class="font-bold text-slate-100 mb-2"><i
                                        class="fa-solid fa-bullhorn text-warning mr-1"></i> Siaran Pengumuman Sekolah
                                </h5>
                                <p class="text-slate-400 small mb-3">Siarkan pemberitahuan resmi secara real-time ke
                                    portal Siswa & Orang Tua.</p>
                                <form onsubmit="event.preventDefault(); broadcastAnnouncement();" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div class="col-md-3">
                                        <input type="text" id="bc-title" class="form-control form-control-sm"
                                            placeholder="Judul Pengumuman..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="bc-category" class="form-select form-select-sm text-slate-100">
                                            <option value="Akademik">Akademik</option>
                                            <option value="Kedisiplinan">Kedisiplinan</option>
                                            <option value="Event">Event Sekolah</option>
                                            <option value="Umum">Umum</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" id="bc-desc" class="form-control form-control-sm"
                                            placeholder="Isi detail pengumuman..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-brand-primary btn-sm w-full font-bold"><i
                                                class="fa-solid fa-paper-plane mr-1"></i> Siarkan</button>
                                    </div>
                                </form>
                            </div>

                            <h4 class="font-bold text-slate-100 mb-2">Laporan Eksekutif</h4>
                            <p class="text-slate-400 small mb-4">Export rekapitulasi data akademik and kedisiplinan
                                semester berjalan.</p>

                            <!-- Custom Report Selection Form -->
                            <form
                                onsubmit="event.preventDefault(); downloadReport(document.getElementById('rep-type').value);"
                                class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
                                <h6 class="font-bold text-slate-100 mb-3"><i
                                        class="fa-solid fa-sliders mr-1 text-info"></i> Kustomisasi Ekspor Laporan</h6>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div class="col-md-3">
                                        <label class="form-label small font-semibold">Jenis Laporan</label>
                                        <select id="rep-type" class="form-select form-select-sm">
                                            <option value="Kehadiran">Rekapitulasi Kehadiran Siswa</option>
                                            <option value="Legger">Legger Nilai Akhir Semester</option>
                                            <option value="BK">Kasus Bimbingan Konseling</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small font-semibold">Format Output</label>
                                        <select class="form-select form-select-sm">
                                            <option>Acrobat PDF Document (.pdf)</option>
                                            <option>Microsoft Excel Spreadsheet (.xlsx)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small font-semibold">Rentang Tanggal</label>
                                        <input type="text" class="form-control form-control-sm"
                                            value="Semester Ganjil 2026/2027" readonly>
                                    </div>
                                    <div class="col-md-2 flex items-end">
                                        <button type="submit" class="btn btn-brand-primary btn-sm w-full font-bold"><i
                                                class="fa-solid fa-download mr-1"></i> Ekspor</button>
                                    </div>
                                </div>
                            </form>
                        </div>


                        <!-- ========================================== -->
                        <!-- 2. WAKA KURIKULUM VIEWS -->
                        <!-- ========================================== -->


                        <!-- ========================================== -->
                        <!-- 3. WAKA KESISWAAN VIEWS -->
                        <!-- ========================================== -->


                        <!-- ========================================== -->
                        <!-- 4. BK (BIMBINGAN KONSELING) VIEWS -->
                        <!-- ========================================== -->
                        <!-- PAGE: BK ASESMEN -->
                        <div id="pane-bk-asesmen" class="pane-content hidden-pane fade-transition">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="font-bold m-0 text-slate-100">Asesmen Psikologis & Minat Bakat</h4>
                                    <p class="text-slate-400 small m-0">Evaluasi berkala tingkat stress akademik dan
                                        pemetaan karir siswa.</p>
                                </div>
                                <button class="btn btn-brand-primary btn-sm rounded-lg font-bold" data-bs-toggle="modal"
                                    data-bs-target="#addAsesmenModal">
                                    <i class="fa-solid fa-plus mr-1"></i> Asesmen Baru
                                </button>
                            </div>

                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 text-slate-100">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <th class="text-center">Stress Level</th>
                                                <th>Minat Lanjutan</th>
                                                <th>Catatan BK</th>
                                                <th>Kerahasiaan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bk-asesmen-tbody">
                                            <!-- Injected dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: BK KANBAN -->
                        <div id="pane-bk-kanban" class="pane-content hidden-pane fade-transition">
                            <div class="d-flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-bold m-0 text-slate-100">Kanban Tindak Lanjut Konseling</h4>
                                    <p class="text-slate-400 small m-0">Alur penanganan kasus BK secara konseptual.</p>
                                </div>
                                <div class="d-flex items-center gap-2">
                                    <button class="btn btn-brand-primary btn-sm rounded-lg font-bold"
                                        data-bs-toggle="modal" data-bs-target="#addBKCaseModal">
                                        <i class="fa-solid fa-plus mr-1"></i> Kasus Baru
                                    </button>
                                    <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>
                                        RAHASIA BK - Akses Terbatas</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Antrean -->
                                <div class="col-md-4">
                                    <div class="kanban-col">
                                        <h6
                                            class="font-bold text-slate-400 mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                                            Antrean Masuk</h6>
                                        <div id="kanban-antrean" class="flex flex-col gap-2">
                                            <!-- Injected cards -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Proses -->
                                <div class="col-md-4">
                                    <div class="kanban-col">
                                        <h6
                                            class="font-bold text-primary mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                                            Sedang Diproses</h6>
                                        <div id="kanban-proses" class="flex flex-col gap-2">
                                            <!-- Injected cards -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Selesai -->
                                <div class="col-md-4">
                                    <div class="kanban-col">
                                        <h6
                                            class="font-bold text-success mb-3 border-b border-slate-700/60 pb-2 uppercase text-xs tracking-wider">
                                            Selesai (Ditutup)</h6>
                                        <div id="kanban-selesai" class="flex flex-col gap-2">
                                            <!-- Injected cards -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: BK RIWAYAT -->
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
                                        <div class="col-md-4">
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


                        <!-- ========================================== -->
                        <!-- 5. GURU WALI / WALI KELAS VIEWS -->
                        <!-- ========================================== -->
                        <!-- PAGE: WALI DASHBOARD -->
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
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
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

                        <!-- PAGE: WALI PRESENSI (AGGREGATION MATRIX & VERIFICATION) -->
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
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm" style="font-size: 13px;">
                                        <thead class="table-light">
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
                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 text-slate-100">
                                <h6 class="font-bold text-slate-100 mb-3"><i
                                        class="fa-solid fa-table-cells mr-1 text-primary"></i> Matriks Kehadiran Harian
                                    Rombel (XI TKJ 1)</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-bordered text-center"
                                        style="font-size: 13px;">
                                        <thead class="table-light align-middle text-slate-100">
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

                        <!-- PAGE: WALI CATATAN -->
                        <div id="pane-wali-catatan" class="pane-content hidden-pane fade-transition">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="font-bold m-0 text-slate-100">Input Catatan Perkembangan Raport</h4>
                                    <p class="text-slate-400 small m-0">Catatan wali kelas untuk dicantumkan pada lembar
                                        e-Rapor akhir.</p>
                                </div>
                                <button onclick="saveWaliNotes()"
                                    class="btn btn-brand-primary btn-sm rounded-lg font-bold">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Semua Catatan
                                </button>
                            </div>

                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80">
                                <div class="table-responsive">
                                    <table class="table align-middle table-hover text-slate-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <th>Catatan Wali Kelas</th>
                                                <th class="text-center" style="width: 150px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="wali-catatan-tbody">
                                            <!-- Dynamic entries -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: WALI CHAT -->
                        <div id="pane-wali-chat" class="pane-content hidden-pane fade-transition">
                            <h4 class="font-bold text-slate-100 mb-2">Pusat Komunikasi Orang Tua</h4>
                            <p class="text-slate-400 small mb-4">Konsultasi langsung dengan wali murid kelas XI TKJ 1.
                            </p>

                            <div class="row g-3">
                                <div class="col-md-4">
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
                                        <div class="d-flex items-center gap-2 border-b border-slate-700/60 pb-3 mb-3">
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

                        <!-- ========================================== -->
                        <!-- 5.5 GURU WALI (ACADEMIC ADVISOR) VIEWS -->
                        <!-- ========================================== -->
                        <!-- PAGE: GURU WALI DASHBOARD -->
                        <div id="pane-guru-wali-dashboard" class="pane-content hidden-pane fade-transition">
                            <div class="card bg-brand-navy text-white border-0 rounded-xl p-4 shadow-sm mb-4">
                                <div class="flex items-center">
                                    <div class="col-md-8">
                                        <h5 class="m-0 text-slate-400 small uppercase tracking-wider">Perkembangan
                                            Akademik Kelas Binaan</h5>
                                        <h3 class="font-bold m-0 mt-1">XI TKJ 1 (Teknik Komputer & Jaringan)</h3>
                                        <p class="m-0 text-slate-400 mt-1 small">Guru Wali: <span
                                                id="gw-dashboard-name">Bpk. Hendra Wijaya, S.T</span> | Target KKM: 75
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <span class="text-slate-400 small block">Rerata Nilai Rombel</span>
                                        <h2 class="font-bold text-success m-0" id="gw-dashboard-average-grade">83.5</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100">
                                <div class="col-md-6">
                                    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full">
                                        <h6 class="font-bold text-slate-100 mb-3"><i
                                                class="fa-solid fa-trophy text-warning mr-1"></i> Peringkat Paralel
                                            Kelas (Top 3 Akademik)</h6>
                                        <div class="flex flex-col gap-2" id="gw-top-students">
                                            <!-- Injected Top Academic Students -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div
                                        class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 h-full border-t border-slate-700/60 border-amber-500 border-4">
                                        <h6 class="font-bold text-warning mb-3"><i
                                                class="fa-solid fa-graduation-cap mr-1"></i> Siswa Dibawah KKM (Perlu
                                            Remedial)</h6>
                                        <ul class="list-group list-group-flush" id="gw-remedial-list">
                                            <!-- Remedial list of students -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: GURU WALI LEGGER & AKADEMIK -->
                        <div id="pane-guru-wali-legger" class="pane-content hidden-pane fade-transition">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h4 class="font-bold m-0 text-slate-100">Legger Nilai & Evaluasi Rombel</h4>
                                    <p class="text-slate-400 small m-0">Tinjau, perbarui, dan validasi sebaran nilai
                                        mata pelajaran untuk e-Rapor.</p>
                                </div>
                                <div class="flex gap-2">
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

                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 text-slate-100">
                                <h6 class="font-bold text-slate-100 mb-3" id="gw-grid-heading"><i
                                        class="fa-solid fa-table-cells mr-1 text-primary"></i> Daftar Nilai Rombel: XI
                                    TKJ 1 | Mapel Binaan</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm text-center"
                                        style="font-size: 13px;">
                                        <thead class="table-light align-middle text-slate-100">
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
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE: GURU WALI RAPOR -->
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

                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 text-slate-100">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-slate-100">
                                        <thead class="table-light">
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
                                            <!-- Dynamic rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <!-- ========================================== -->
                        <!-- 6. GURU MATA PELAJARAN (GURU MAPEL) VIEWS -->
                        <!-- ========================================== -->
                        <!-- PAGE: GURU MAPEL DASHBOARD -->
                        <div id="pane-guru-mapel-dashboard" class="pane-content hidden-pane fade-transition">

                            <!-- Jadwal Mengajar Guru Mapel -->
                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
                                <h5 class="font-bold text-slate-100 mb-2"><i
                                        class="fa-regular fa-clock text-info mr-1"></i> Jadwal Mengajar Anda (Hari Ini -
                                    Selasa)</h5>
                                <p class="text-slate-400 small mb-3">Silakan pilih jadwal untuk mengisi Jurnal Mengajar
                                    dan Presensi.</p>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle table-sm"
                                        style="font-size: 13px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Jam Ke / Waktu</th>
                                                <th>Kelas Rombel</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Ruang / Lab</th>
                                                <th class="text-center">Status Jurnal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="guru-mapel-schedules-tbody">
                                            <!-- Dynamic rows populated from mockDatabase.schedules filtered by Pak Danny -->
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
                                            <!-- Options loaded dynamically -->
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

                                <div class="table-responsive mt-3 hidden-pane" id="gw-attn-input-container">
                                    <h6 class="font-bold small text-slate-100 mb-2"><i
                                            class="fa-solid fa-users mr-1 text-primary"></i> Daftar Presensi Rombel</h6>
                                    <table class="table table-hover align-middle table-sm" style="font-size: 13px;">
                                        <thead class="table-light">
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
                                            class="btn btn-brand-primary btn-sm rounded-lg font-bold">
                                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Jurnal & Presensi
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- List of past teaching journals -->
                            <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
                                <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-book mr-1 text-info"></i>
                                    Riwayat Jurnal Mengajar Anda</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle table-sm"
                                        style="font-size: 13px;">
                                        <thead class="table-light">
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
                                            <!-- Injected dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- 7. SISWA VIEWS -->
                        <!-- ========================================== -->
                        <div id="pane-siswa-portal" class="pane-content hidden-pane fade-transition">
                            <div class="card bg-brand-navy text-white border-0 rounded-xl p-3 shadow-sm mb-3">
                                <div class="d-flex items-center gap-3">
                                    <div class="avatar-circle bg-slate-800/80 text-slate-100 fs-4 font-bold"
                                        style="width: 50px; height: 50px;">AS</div>
                                    <div>
                                        <h5 class="font-bold m-0" id="siswa-portal-name">Andi Susanto</h5>
                                        <span class="text-slate-400 small" style="font-size: 11px;">Siswa Kelas XI TKJ
                                            1</span>
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center mt-3 pt-2 border-t border-slate-700/60 border-secondary/20">
                                    <span class="small text-slate-400" id="siswa-portal-attendance-rate"><i
                                            class="fa-solid fa-circle-check"></i> 98.2% Kehadiran</span>
                                    <span class="badge bg-success badge-pill-custom">Aktif Belajar</span>
                                </div>
                            </div>

                            <!-- Mobile Tabs for Siswa -->
                            <div class="btn-group btn-group-sm w-full mb-3" role="group">
                                <button type="button" onclick="switchSiswaTab('nilai')" id="siswa-t-nilai"
                                    class="btn btn-outline-secondary active">Nilai & KKM</button>
                                <button type="button" onclick="switchSiswaTab('jadwal')" id="siswa-t-jadwal"
                                    class="btn btn-outline-secondary">Jadwal Kelas</button>
                                <button type="button" onclick="switchSiswaTab('catatan')" id="siswa-t-catatan"
                                    class="btn btn-outline-secondary">Tata Tertib</button>
                            </div>

                            <!-- Siswa Nilai Pane -->
                            <div id="siswa-tab-nilai-pane" class="fade-transition">
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-3"><i
                                            class="fa-solid fa-graduation-cap text-primary mr-1"></i> Rapor Sementara
                                        Ganjil</h6>
                                    <div class="flex flex-col gap-3" id="siswa-grades-list">
                                        <!-- Injected dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Siswa Jadwal Pane -->
                            <div id="siswa-tab-jadwal-pane" class="hidden-pane fade-transition">
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-2"><i
                                            class="fa-solid fa-calendar-day text-info mr-1"></i> Jadwal Pelajaran Hari
                                        Ini</h6>
                                    <div class="list-group list-group-flush" style="font-size: 13px;">
                                        <div class="list-group-item px-0 py-2 flex justify-between">
                                            <div>
                                                <span class="font-bold block">Jam Ke-1 (07:00 - 08:30)</span>
                                                <span class="text-slate-400">Matematika Terapan (Bpk. Anton)</span>
                                            </div>
                                            <span class="badge bg-secondary align-self-center">Ruang 101</span>
                                        </div>
                                        <div class="list-group-item px-0 py-2 flex justify-between">
                                            <div>
                                                <span class="font-bold block">Jam Ke-2 (08:30 - 10:00)</span>
                                                <span class="text-slate-400">Bahasa Indonesia (Ibu Dra. Sri)</span>
                                            </div>
                                            <span class="badge bg-secondary align-self-center">Ruang 101</span>
                                        </div>
                                        <div class="list-group-item px-0 py-2 flex justify-between">
                                            <div>
                                                <span class="font-bold block">Jam Ke-3 (10:15 - 12:15)</span>
                                                <span class="text-slate-400">Administrasi Jaringan (Bpk. Hendra)</span>
                                            </div>
                                            <span class="badge bg-primary align-self-center">Lab RPS 1</span>
                                        </div>
                                        <div class="list-group-item px-0 py-2 flex justify-between">
                                            <div>
                                                <span class="font-bold block">Jam Ke-4 (13:00 - 15:00)</span>
                                                <span class="text-slate-400">Pemrograman Web & Mobile (Pak Danny)</span>
                                            </div>
                                            <span class="badge bg-primary align-self-center">Lab RPS 2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Siswa Catatan/Tata Tertib Pane -->
                            <div id="siswa-tab-catatan-pane" class="hidden-pane fade-transition">
                                <div
                                    class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3 border-l border-slate-700/60 border-rose-500 border-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h6 class="font-bold text-slate-100 m-0"><i
                                                class="fa-solid fa-triangle-exclamation text-danger mr-1"></i> Akumulasi
                                            Poin Pelanggaran</h6>
                                        <span class="badge bg-danger fs-6 font-bold" id="siswa-portal-violation-points">10
                                            Poin</span>
                                    </div>
                                    <div class="small text-slate-400 mb-3" id="siswa-violation-list">
                                        <!-- Injected violations -->
                                    </div>
                                </div>
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-2"><i
                                            class="fa-solid fa-pen-clip text-primary mr-1"></i> Catatan Pembinaan Wali
                                        Kelas (Sikap)</h6>
                                    <p class="small text-slate-400 italic mb-0" id="siswa-portal-wali-note">"Andi
                                        menunjukkan kemajuan..."</p>
                                </div>
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100">
                                    <h6 class="font-bold text-slate-100 mb-2"><i
                                            class="fa-solid fa-graduation-cap text-success mr-1"></i> Catatan Akademik
                                        Guru Wali</h6>
                                    <p class="small text-slate-400 italic mb-0" id="siswa-portal-academic-note">"Andi
                                        menunjukkan kemajuan akademik..."</p>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- 8. ORANG TUA VIEWS -->
                        <!-- ========================================== -->
                        <div id="pane-ortu-portal" class="pane-content hidden-pane fade-transition">
                            <!-- Alert Banners dynamically injected -->
                            <div id="ortu-attendance-notifications-container">
                                <!-- Warning or info banners -->
                            </div>

                            <div class="card bg-brand-navy text-white border-0 rounded-xl p-3 shadow-sm mb-3">
                                <div class="flex justify-between align-items-start mb-2">
                                    <div>
                                        <h5 class="font-bold m-0">Bpk. Budi Susanto</h5>
                                        <span class="text-slate-400 small" style="font-size: 11px;">Orang Tua dari Andi
                                            Susanto</span>
                                    </div>
                                    <div class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">Koneksi Aktif
                                    </div>
                                </div>
                                <div class="bg-dark/30 rounded-lg p-2.5 mt-2">
                                    <span class="text-slate-400 block" style="font-size: 10px;">Presensi Andi Hari
                                        Ini:</span>
                                    <p class="font-bold text-success m-0" id="ortu-child-attendance-status"><i
                                            class="fa-solid fa-circle-check"></i> Hadir Tepat Waktu</p>
                                </div>
                            </div>

                            <!-- Mobile Tabs for Ortu -->
                            <div class="btn-group btn-group-sm w-full mb-3" role="group">
                                <button type="button" onclick="switchOrtuTab('kesiswaan')" id="ortu-t-kesiswaan"
                                    class="btn btn-outline-secondary active">Kedisiplinan & Poin</button>
                                <button type="button" onclick="switchOrtuTab('akademik')" id="ortu-t-akademik"
                                    class="btn btn-outline-secondary">Rapor Akademik</button>
                                <button type="button" onclick="switchOrtuTab('chat')" id="ortu-t-chat"
                                    class="btn btn-outline-secondary">Konsultasi Chat</button>
                            </div>

                            <!-- Ortu Kesiswaan & Presensi Pane -->
                            <div id="ortu-tab-kesiswaan-pane" class="fade-transition">
                                <div
                                    class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3 border-l border-slate-700/60 border-rose-500 border-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h6 class="font-bold text-slate-100 m-0"><i
                                                class="fa-solid fa-triangle-exclamation text-danger mr-1"></i> Poin
                                            Disiplin Siswa</h6>
                                        <span class="badge bg-danger fs-6 font-bold" id="ortu-violation-points">10
                                            Poin</span>
                                    </div>
                                    <div class="small text-slate-400 mb-2" id="ortu-violation-list">
                                        <!-- Injected -->
                                    </div>
                                    <div class="text-slate-400" style="font-size: 10px;">*Batas maksimal toleransi
                                        pelanggaran sebelum SP1: 50 Poin.</div>
                                </div>

                                <!-- Announcement feed -->
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3">
                                    <div class="flex justify-between items-center mb-3">
                                        <h6 class="font-bold text-slate-100 m-0"><i
                                                class="fa-solid fa-bullhorn text-warning mr-1"></i> Siaran Pengumuman
                                        </h6>
                                        <select id="ortu-announcement-filter"
                                            class="form-select form-select-xs w-auto text-slate-100 py-0 px-2"
                                            style="font-size: 11px;" onchange="renderOrtuAnnouncements()">
                                            <option value="all">Semua</option>
                                            <option value="Akademik">Akademik</option>
                                            <option value="Kedisiplinan">Disiplin</option>
                                            <option value="Event">Event</option>
                                        </select>
                                    </div>
                                    <div class="list-group list-group-flush" id="ortu-announcements-list"
                                        style="max-height: 250px; overflow-y: auto;">
                                        <!-- announcements -->
                                    </div>
                                </div>

                                <div
                                    class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 border-l border-slate-700/60 border-amber-500 border-4 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-2"><i
                                            class="fa-solid fa-pen-clip text-warning mr-1"></i> Catatan Pembinaan Wali
                                        Kelas (Sikap)</h6>
                                    <p class="small text-slate-400 italic mb-0" id="ortu-portal-wali-note">"Andi cukup
                                        tertib..."</p>
                                </div>
                            </div>

                            <!-- Ortu Akademik Pane -->
                            <div id="ortu-tab-akademik-pane" class="hidden-pane fade-transition">
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-3"><i
                                            class="fa-solid fa-star-half-stroke text-warning mr-1"></i> Lembar Legger
                                        Rapor Semester Ganjil</h6>
                                    <div class="flex flex-col gap-3" id="ortu-grades-list">
                                        <!-- Injected grades -->
                                    </div>
                                </div>
                                <div
                                    class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 text-slate-100 border-l border-slate-700/60 border-emerald-500 border-4 mb-3">
                                    <h6 class="font-bold text-slate-100 mb-2"><i
                                            class="fa-solid fa-graduation-cap text-success mr-1"></i> Catatan Akademik
                                        Guru Wali</h6>
                                    <p class="small text-slate-400 italic mb-0" id="ortu-portal-academic-note">"Andi
                                        menunjukkan kemajuan akademik..."</p>
                                </div>
                            </div>

                            <!-- Ortu Konsultasi Chat Pane -->
                            <div id="ortu-tab-chat-pane" class="hidden-pane fade-transition">
                                <div class="card border-0 rounded-xl shadow-sm p-3 bg-slate-800/80 flex flex-col text-slate-100"
                                    style="height: 380px;">
                                    <div class="mb-3">
                                        <label class="form-label small font-semibold">Saluran Konsultasi:</label>
                                        <select id="chat-channel-selector"
                                            class="form-select form-select-sm text-slate-100 font-semibold"
                                            onchange="switchParentChatConversation()">
                                            <option value="ortu_andi_wali" selected>Ibu Siti (Wali Kelas XI TKJ 1)
                                            </option>
                                            <option value="ortu_andi_bk">Ibu Mayang (Konselor BK)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex items-center gap-2 border-b border-slate-700/60 pb-2 mb-2">
                                        <div class="avatar-circle font-bold bg-primary text-white"
                                            style="width: 32px; height: 32px; font-size: 11px;">WL</div>
                                        <div>
                                            <h6 class="font-bold text-slate-100 m-0" style="font-size: 13px;"
                                                id="ortu-chat-target-name">Ibu Siti (Wali Kelas)</h6>
                                            <span class="text-success small" style="font-size: 9px;"><i
                                                    class="fa-solid fa-circle text-success" style="font-size: 7px;"></i>
                                                Online</span>
                                        </div>
                                    </div>

                                    <!-- Chat bubbles -->
                                    <div class="chat-container flex flex-col flex-grow-1"
                                        id="ortu-chat-messages-container" style="height: 180px;">
                                        <!-- bubbles -->
                                    </div>

                                    <div class="flex gap-2 mt-2">
                                        <input type="text" id="ortu-chat-input-text"
                                            onkeypress="handleOrtuChatKeyPress(event)"
                                            class="form-control form-control-sm rounded-pill px-3"
                                            placeholder="Ketik pesan ke sekolah...">
                                        <button onclick="sendOrtuMessage()"
                                            class="btn btn-brand-primary btn-sm rounded-circle px-2.5"><i
                                                class="fa-solid fa-paper-plane" style="font-size: 11px;"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </main>

                    <!-- Mobile Bottom Navigation Bar (Visible in mobile mode/viewport) -->
                    <div id="mobile-navigation-bar"
                        class="mobile-bottom-nav d-flex justify-content-around py-2 d-md-none border-t border-slate-700/60 bg-slate-800/80 flex-shrink-0">
                        <button onclick="switchMobileNav('beranda')" id="mn-home" class="btn nav-link active">
                            <i class="fa-solid fa-house block mb-1 fs-5"></i> Beranda
                        </button>
                        <button onclick="switchMobileNav('akademik')" id="mn-acad" class="btn nav-link">
                            <i class="fa-solid fa-chart-pie block mb-1 fs-5"></i> Akademik
                        </button>
                        <button onclick="switchMobileNav('kesiswaan')" id="mn-dis" class="btn nav-link">
                            <i class="fa-solid fa-clipboard-user block mb-1 fs-5"></i> Kesiswaan
                        </button>
                        <button onclick="logout()" class="btn nav-link text-danger">
                            <i class="fa-solid fa-right-from-bracket block mb-1 fs-5"></i> Keluar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- INTERACTIVE MODAL DIALOGS -->
    <!-- ========================================== -->

    <!-- MODAL: ADD VIOLATION (WAKA KESISWAAN) -->
    <div class="modal fade" id="addViolationModal" tabindex="-1" aria-labelledby="addViolationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-danger text-white rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="addViolationModalLabel"><i
                            class="fa-solid fa-triangle-exclamation mr-1"></i> Catat Pelanggaran Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="violation-student-select" class="form-label small font-semibold">Pilih Siswa</label>
                        <select id="violation-student-select" class="form-select">
                            <!-- Options injected -->
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="violation-category-select" class="form-label small font-semibold">Kategori
                                Pelanggaran</label>
                            <select id="violation-category-select" class="form-select"
                                onchange="updatePoinSuggestion()">
                                <option value="Keterlambatan" data-poin="5">Keterlambatan Upacara (5 Poin)</option>
                                <option value="Atribut Sekolah" data-poin="10">Atribut Tidak Lengkap (10 Poin)</option>
                                <option value="Bolos Sekolah" data-poin="25">Membolos KBM (25 Poin)</option>
                                <option value="Pertikaian" data-poin="50">Perkelahian / Fisik (50 Poin)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="violation-points" class="form-label small font-semibold">Poin
                                Pelanggaran</label>
                            <input type="number" id="violation-points" class="form-control" value="5" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="violation-desc" class="form-label small font-semibold">Catatan Kronologi / Tindak
                            Lanjut</label>
                        <textarea id="violation-desc" class="form-control" rows="3"
                            placeholder="Masukkan rincian kejadian dan bentuk teguran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitViolationRecord()"
                        class="btn btn-danger btn-sm rounded-lg font-bold">Simpan Catatan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD ASESMEN BK -->
    <div class="modal fade" id="addAsesmenModal" tabindex="-1" aria-labelledby="addAsesmenModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-indigo text-white rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="addAsesmenModalLabel"><i
                            class="fa-solid fa-clipboard-question mr-1"></i> Sesi Konseling & Asesmen BK</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="asesmen-student-select" class="form-label small font-semibold">Nama Siswa</label>
                        <select id="asesmen-student-select" class="form-select">
                            <!-- Injected -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small font-semibold flex justify-between">
                            <span>Tingkat Stress Akademik</span>
                            <span id="stress-val-display" class="badge bg-primary">5</span>
                        </label>
                        <input type="range" id="asesmen-stress" class="form-range" min="1" max="10" value="5"
                            oninput="document.getElementById('stress-val-display').innerText = this.value">
                        <div class="flex justify-between text-slate-400 mt-1" style="font-size: 10px;">
                            <span>Sangat Baik (1)</span>
                            <span>Sangat Stress (10)</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="asesmen-career" class="form-label small font-semibold">Minat Karir Lanjutan</label>
                        <select id="asesmen-career" class="form-select">
                            <option value="Bekerja di Industri">Bekerja di Industri Kejuruan</option>
                            <option value="Melanjutkan Kuliah">Melanjutkan Kuliah S1</option>
                            <option value="Wirausaha Mandiri">Wirausaha Mandiri (Start-up)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="asesmen-notes" class="form-label small font-semibold">Catatan Konseling & Hasil
                            Observasi</label>
                        <textarea id="asesmen-notes" class="form-control" rows="2"
                            placeholder="Ketik rincian hasil konseling..."></textarea>
                    </div>
                    <!-- Restricted Privacy Flags -->
                    <div class="bg-slate-800/50 p-3 rounded-lg border">
                        <h6 class="font-bold small text-danger uppercase tracking-wider mb-2"><i
                                class="fa-solid fa-user-shield"></i> Protokol Kerahasiaan BK</h6>
                        <div class="form-check form-switch small">
                            <input class="form-check-input" type="checkbox" id="flag-confidential" checked>
                            <label class="form-check-label font-semibold" for="flag-confidential">Tandai Sesi Sangat
                                Rahasia (Akses BK Saja)</label>
                        </div>
                        <div class="form-check form-switch small mt-1">
                            <input class="form-check-input" type="checkbox" id="flag-restricted-parents">
                            <label class="form-check-label font-semibold" for="flag-restricted-parents">Sembunyikan dari
                                e-Rapor Orang Tua</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitBKAsesmen()"
                        class="btn btn-indigo text-white btn-sm rounded-lg font-bold">Simpan Sesi & Asesmen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: REFERRAL ESCALATION WORKFLOW -->
    <div class="modal fade" id="escalateCaseModal" tabindex="-1" aria-labelledby="escalateCaseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-warning text-slate-100 rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="escalateCaseModalLabel"><i
                            class="fa-solid fa-triangle-exclamation mr-1"></i> Rujukan & Eskalasi Kasus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="escalate-student-nis">
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Nama Siswa</label>
                        <input type="text" id="escalate-student-name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="escalate-target-select" class="form-label small font-semibold">Rujuk / Eskalasi
                            Ke</label>
                        <select id="escalate-target-select" class="form-select">
                            <option value="bk">Bimbingan Konseling (BK)</option>
                            <option value="kesiswaan">Waka Kesiswaan (Discipline)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="escalate-category-select" class="form-label small font-semibold">Kategori
                            Kasus</label>
                        <select id="escalate-category-select" class="form-select">
                            <option value="Akademik / Penurunan Motivasi">Akademik / Penurunan Motivasi Belajar</option>
                            <option value="Kedisiplinan / Membolos">Kedisiplinan / Membolos KBM</option>
                            <option value="Sikap / Perilaku Sosial">Sikap / Perilaku Sosial / Bullying</option>
                            <option value="Kesehatan Mental / Pribadi">Kesehatan Mental / Masalah Keluarga</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="escalate-notes" class="form-label small font-semibold">Kronologi & Alasan
                            Eskalasi</label>
                        <textarea id="escalate-notes" class="form-control" rows="3"
                            placeholder="Masukkan rincian kronologi, perilaku siswa, dan upaya pembinaan awal..."
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitEscalationReferral()"
                        class="btn btn-warning btn-sm rounded-lg font-bold">Kirim Rujukan Eskalasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: GURU WALI ACADEMIC NOTES FORM -->
    <div class="modal fade" id="guruWaliRemarkModal" tabindex="-1" aria-labelledby="guruWaliRemarkModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-brand-primary text-white rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="guruWaliRemarkModalLabel"><i
                            class="fa-solid fa-marker mr-1"></i> Catatan Akademik Guru Wali</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="gw-modal-student-nis">
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Nama Siswa</label>
                        <input type="text" id="gw-modal-student-name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Mata Pelajaran Pengampu</label>
                        <input type="text" class="form-control" value="Administrasi Infrastruktur Jaringan" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="gw-modal-note" class="form-label small font-semibold">Catatan Evaluasi / Kemajuan
                            Akademik</label>
                        <textarea id="gw-modal-note" class="form-control" rows="3"
                            placeholder="Deskripsikan penguasaan materi praktikum, pemecahan masalah (troubleshooting), dan rekomendasi belajar siswa..."></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="gw-modal-remedial">
                        <label class="form-check-label font-semibold text-danger small" for="gw-modal-remedial">
                            Tandai Butuh Program Remedial & Pendampingan Khusus
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitGuruWaliRemarkModal()"
                        class="btn btn-brand-primary btn-sm rounded-lg font-bold">Simpan Catatan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD PANGGILAN ORTU (WAKA KESISWAAN) -->
    <div class="modal fade" id="addSummonModal" tabindex="-1" aria-labelledby="addSummonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-danger text-white rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="addSummonModalLabel"><i class="fa-solid fa-envelope mr-1"></i>
                        Jadwalkan Undangan Panggilan Orang Tua</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="sum-student-select" class="form-label small font-semibold">Nama Siswa</label>
                        <select id="sum-student-select" class="form-select text-slate-100">
                            <!-- Options injected -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sum-parent-name" class="form-label small font-semibold">Nama Orang Tua / Wali
                            Murid</label>
                        <input type="text" id="sum-parent-name" class="form-control" placeholder="Nama Bpk/Ibu Wali..."
                            required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="sum-date" class="form-label small font-semibold">Tanggal Pemanggilan</label>
                            <input type="date" id="sum-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sum-time" class="form-label small font-semibold">Waktu / Jam</label>
                            <input type="text" id="sum-time" class="form-control" value="09:00 WIB" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="sum-room" class="form-label small font-semibold">Tempat Rapat Mediasi</label>
                        <select id="sum-room" class="form-select text-slate-100">
                            <option>Ruang Waka Kesiswaan</option>
                            <option>Ruang Bimbingan Konseling (BK)</option>
                            <option>Ruang Media Center</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sum-reason" class="form-label small font-semibold">Alasan Rujukan Panggilan</label>
                        <textarea id="sum-reason" class="form-control" rows="2"
                            placeholder="Masukkan alasan pemanggilan (Alpa tinggi / kasus berat)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitSummonModal()"
                        class="btn btn-danger btn-sm rounded-lg font-bold">Kirim Undangan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD KANBAN CASE BK -->
    <div class="modal fade" id="addBKCaseModal" tabindex="-1" aria-labelledby="addBKCaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-indigo text-white rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="addBKCaseModalLabel"><i class="fa-solid fa-list-check mr-1"></i>
                        Buka Kasus BK Baru (Kanban)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="kan-student-select" class="form-label small font-semibold">Nama Siswa</label>
                        <select id="kan-student-select" class="form-select text-slate-100">
                            <!-- Injected -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kan-title" class="form-label small font-semibold">Topik Kasus Masalah</label>
                        <input type="text" id="kan-title" class="form-control"
                            placeholder="Contoh: Penurunan Nilai Drastis / Konflik..." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="kan-urgency" class="form-label small font-semibold">Urgent Tingkat</label>
                            <select id="kan-urgency" class="form-select text-slate-100">
                                <option value="Mendesak">Mendesak / Berat</option>
                                <option value="Normal" selected>Normal</option>
                                <option value="Pemantauan">Pemantauan Ringan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="kan-confidential" class="form-label small font-semibold">Kerahasiaan
                                Kasus</label>
                            <select id="kan-confidential" class="form-select text-slate-100">
                                <option value="Umum">Umum (Bisa diakses Wali Kelas)</option>
                                <option value="Rahasia BK">Sangat Rahasia (Akses BK Saja)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitBKCaseModal()"
                        class="btn btn-indigo text-white btn-sm rounded-lg font-bold">Tambah Kasus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ATTENDANCE EXCEPTION VERIFICATION -->
    <div class="modal fade" id="verifyAttendanceModal" tabindex="-1" aria-labelledby="verifyAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0 rounded-xl shadow-lg text-slate-100">
                <div class="modal-header bg-warning text-slate-100 rounded-top-4 border-0">
                    <h5 class="modal-title font-bold" id="verifyAttendanceModalLabel"><i
                            class="fa-solid fa-user-shield mr-1"></i> Verifikasi Ketidakhadiran Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="verify-exception-id">
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Nama Siswa</label>
                        <input type="text" id="verify-student-name" class="form-control bg-slate-800/50" readonly>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small font-semibold">Mata Pelajaran / Sesi</label>
                            <input type="text" id="verify-subject" class="form-control bg-slate-800/50" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small font-semibold">Guru Pengampu</label>
                            <input type="text" id="verify-teacher" class="form-control bg-slate-800/50" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small font-semibold">Laporan Ketidakhadiran Asli</label>
                        <input type="text" id="verify-raw-reason" class="form-control bg-slate-800/50" readonly>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <label for="verify-decision" class="form-label small font-semibold text-primary">Keputusan
                            Verifikasi Akhir</label>
                        <select id="verify-decision" class="form-select text-slate-100">
                            <option value="Verified Valid">Valid (Ketidakhadiran Disetujui - Dispensasi / Surat Izin
                                Ortu)</option>
                            <option value="Verified Invalid">Tidak Valid (Murni Membolos - Ditandai Alpa Permanen)
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="verify-notes" class="form-label small font-semibold">Catatan Verifikator &
                            Keterangan</label>
                        <textarea id="verify-notes" class="form-control" rows="2"
                            placeholder="Masukkan berkas pendukung (misal: Surat dokter dilampirkan)..."
                            required></textarea>
                    </div>
                    <!-- Notification Checkbox custom for Client Android App -->
                    <div class="form-check form-switch small">
                        <input class="form-check-input" type="checkbox" id="verify-send-app-alert" checked>
                        <label class="form-check-label font-semibold text-danger" for="verify-send-app-alert">
                            Kirim Notifikasi Push ke Aplikasi Android Orang Tua (SIParenting App)
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-slate-800/50 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-lg"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="submitAttendanceVerification()"
                        class="btn btn-warning text-slate-100 btn-sm rounded-lg font-bold">Simpan Verifikasi</button>
                </div>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- BOOTSTRAP TOAST (NOTIFICATION DISCOVERY) -->
    <!-- ========================================== -->
    <div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1100;">
        <div id="actionToast" class="toast items-center text-white bg-dark border-0 rounded-lg" role="alert"
            aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body d-flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-success fs-5"></i>
                    <span id="toast-message-content">Aksi berhasil diselesaikan.</span>
                </div>
                <button type="button" class="btn-close btn-close-white mr-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- DYNAMIC CORE JAVASCRIPT SYSTEM -->
    <!-- ========================================== -->
    <script>
        // MOCK STATE DATABASE
        const mockDatabase = {
            students: [
                {
                    nis: 1001,
                    name: 'Andi Susanto',
                    class: 'XI TKJ 1',
                    grades: { Math: 88, Indo: 90, Jaringan: 95, Pemrograman: 92 },
                    attendanceToday: 'H',
                    totalAlpa: 1,
                    academicNote: 'Kemampuan konfigurasi Cisco di atas rata-rata. Direcommendasikan sertifikasi CCNA.',
                    waliNote: 'Andi menunjukkan kepemimpinan yang baik dan prestasi akademik menonjol semester ini. Pertahankan!',
                    counselingStress: 3,
                    counselingCareer: 'Bekerja di Industri',
                    counselingNote: 'Termotivasi tinggi untuk bekerja setelah lulus. Sedang mempersiapkan portofolio jaringan.',
                    totalViolationPoints: 10,
                    violations: [
                        { date: '2026-08-10', title: 'Terlambat Upacara Bendera', category: 'Keterlambatan', points: 5 },
                        { date: '2026-08-18', title: 'Atribut Seragam Tidak Lengkap', category: 'Atribut Sekolah', points: 5 }
                    ]
                },
                {
                    nis: 1002,
                    name: 'Bunga Citra',
                    class: 'XI TKJ 1',
                    grades: { Math: 82, Indo: 88, Jaringan: 85, Pemrograman: 86 },
                    attendanceToday: 'H',
                    totalAlpa: 0,
                    academicNote: 'Ketekunan dalam menyelesaikan tugas harian sangat baik, namun perlu peningkatan keberanian saat sesi diskusi.',
                    waliNote: 'Siswa aktif, ramah, dan mematuhi seluruh peraturan tata tertib sekolah.',
                    counselingStress: 4,
                    counselingCareer: 'Melanjutkan Kuliah',
                    counselingNote: 'Memiliki minat di bidang teknik informatika, didorong untuk mempersiapkan jalur prestasi SNBP.',
                    totalViolationPoints: 0,
                    violations: []
                },
                {
                    nis: 1003,
                    name: 'Caca Marica',
                    class: 'XI TKJ 1',
                    grades: { Math: 80, Indo: 84, Jaringan: 88, Pemrograman: 82 },
                    attendanceToday: 'H',
                    totalAlpa: 0,
                    academicNote: 'Prestasi akademik stabil. Pemahaman materi perancangan layout web responsif sangat baik.',
                    waliNote: 'Pertahankan prestasi akademik, serta keterlibatan aktif dalam kegiatan OSIS.',
                    counselingStress: 2,
                    counselingCareer: 'Wirausaha Mandiri',
                    counselingNote: 'Sangat berminat pada UI/UX freelancing. Sudah mulai merintis projek kecil.',
                    totalViolationPoints: 0,
                    violations: []
                },
                {
                    nis: 1004,
                    name: 'Dodi Hermawan',
                    class: 'XI TKJ 1',
                    grades: { Math: 68, Indo: 72, Jaringan: 65, Pemrograman: 70 },
                    attendanceToday: 'S',
                    totalAlpa: 4,
                    academicNote: 'Nilai di beberapa mata pelajaran kejuruan produktif masih di bawah KKM. Memerlukan remedial terpadu.',
                    waliNote: 'Butuh pendampingan intensif dari orang tua untuk meningkatkan kepedulian jam belajar di rumah.',
                    counselingStress: 8,
                    counselingCareer: 'Bekerja di Industri',
                    counselingNote: 'Mengalami tekanan emosional karena tertinggal materi produktif. Konselor menjadwalkan remedial khusus.',
                    totalViolationPoints: 35,
                    violations: [
                        { date: '2026-08-05', title: 'Membolos Jam Pelajaran Kejuruan', category: 'Bolos Sekolah', points: 25 },
                        { date: '2026-08-12', title: 'Terlambat Masuk Jam Pertama', category: 'Keterlambatan', points: 10 }
                    ]
                },
                {
                    nis: 1005,
                    name: 'Eko Saputro',
                    class: 'XI TKJ 1',
                    grades: { Math: 75, Indo: 80, Jaringan: 74, Pemrograman: 78 },
                    attendanceToday: 'A',
                    totalAlpa: 5,
                    academicNote: 'Cukup mahir dalam praktik perakitan PC, perlu meningkatkan kehadiran di kelas teori.',
                    waliNote: 'Eko sering membolos tanpa alasan yang jelas. Pihak sekolah telah menerbitkan Surat Panggilan Ortu 1.',
                    counselingStress: 7,
                    counselingCareer: 'Wirausaha Mandiri',
                    counselingNote: 'Mengaku kurang berminat di teori, lebih menyukai praktikum lapangan. BK mengarahkan pemahaman regulasi industri.',
                    totalViolationPoints: 50,
                    violations: [
                        { date: '2026-08-01', title: 'Ketidakhadiran Tanpa Keterangan (Alpa) 3x', category: 'Bolos Sekolah', points: 25 },
                        { date: '2026-08-20', title: 'Terlibat Keributan di Kantin', category: 'Pertikaian', points: 25 }
                    ]
                }
            ],
            counselingKanban: [
                { id: 'k1', title: 'Kasus Perundungan Siber', student: 'Dodi Hermawan', category: 'Mendesak', stage: 'antrean' },
                { id: 'k2', title: 'Penurunan Motivasi Belajar', student: 'Andi Susanto', category: 'Normal', stage: 'proses' },
                { id: 'k3', title: 'Konflik Teman Sebaya', student: 'Eko Saputro', category: 'Normal', stage: 'selesai' }
            ],
            summons: [
                { id: 's1', student: 'Eko Saputro', class: 'XI TKJ 1', date: 'Kamis, 27 Agustus 2026', time: '09:00 WIB', status: 'Menunggu Konfirmasi' },
                { id: 's2', student: 'Dodi Hermawan', class: 'XI TKJ 1', date: 'Senin, 24 Agustus 2026', time: '10:00 WIB', status: 'Hadir / Mediasi Selesai' }
            ],
            chats: {
                'ortu_andi_wali': [
                    { sender: 'ortu', text: 'Selamat pagi Ibu Siti. Mohon izin bertanya, apakah besok ada pembagian lembar nilai harian untuk anak saya Andi?', time: '08:15' },
                    { sender: 'wali', text: 'Selamat pagi Bapak Budi. Benar, besok pagi lembar nilai rekap harian legger akan kami bagikan lewat rapor siswa. Mohon diperiksa perkembangannya.', time: '08:30' },
                    { sender: 'ortu', text: 'Baik Ibu, terima kasih infonya. Kami akan terus memantau belajar Andi di rumah.', time: '08:35' }
                ],
                'ortu_dodi_wali': [
                    { sender: 'wali', text: 'Selamat siang Bapak/Ibu wali Dodi. Mohon perhatiannya terkait kehadiran Dodi yang tidak masuk tanpa keterangan selama 2 hari minggu ini.', time: '13:00' },
                    { sender: 'ortu', text: 'Selamat siang Ibu. Ya ampun, mohon maaf sekali Ibu, dodi bilangnya berangkat ke sekolah. Kami akan segera mendisiplinkan Dodi di rumah.', time: '13:05' }
                ],
                'ortu_eko_wali': [
                    { sender: 'wali', text: 'Selamat siang Bapak Joko. Terkait surat panggilan mediasi yang kami kirimkan, apakah bapak bisa hadir besok jam 09:00?', time: '10:00' },
                    { sender: 'ortu', text: 'Selamat siang Ibu. Insya Allah saya akan meluangkan waktu hadir besok pagi ke sekolah.', time: '10:10' }
                ],
                'ortu_andi_bk': [
                    { sender: 'ortu', text: 'Selamat siang Ibu Mayang, saya budi ortu andi. Ingin menanyakan perkembangan sesi konseling andi.', time: '12:00' },
                    { sender: 'bk', text: 'Selamat siang Pak Budi. Andi sangat aktif dan komunikatif dalam sesi konseling. Tingkat stress akademiknya sangat rendah, andi fokus ke persiapan portofolio jaringan.', time: '12:15' }
                ]
            },
            announcements: [
                { date: '24 Agt 2026', title: 'Undangan Rapat Komite PKL', desc: 'Rapat pleno wali murid kelas XI membahas persiapan Praktik Kerja Lapangan (PKL) semester depan.', category: 'Event' },
                { date: '15 Agt 2026', title: 'Ujian Tengah Semester (UTS)', desc: 'Pengumuman jadwal UTS Ganjil dimulai tanggal 1 September 2026. Kartu ujian dapat diunduh di portal.', category: 'Akademik' }
            ],
            auditLogs: [
                { timestamp: '2026-08-25 10:30', actor: 'Ibu Siti Rahmawati (Wali Kelas)', desc: 'Mengisi catatan rapor Andi Susanto', impact: 'Catatan Tersimpan' },
                { timestamp: '2026-08-25 11:15', actor: 'Bpk. Hendra Wijaya (Guru Wali)', desc: 'Mengubah nilai Jaringan Bunga Citra: 85 -> 88', impact: 'Rerata Legger naik ke 85.3' }
            ],
            leggerApproved: false,

            // MOCK SCHEDULES (Sebaran Jadwal)
            schedules: [
                { id: 'sch1', teacher: 'Pak Danny', day: 'Selasa', period: 'Jam ke-1 (07:00 - 07:45)', rombel: 'XI_TKJ_1', subject: 'Pemrograman', room: 'Lab RPS 1', logged: false },
                { id: 'sch2', teacher: 'Pak Danny', day: 'Selasa', period: 'Jam ke-5 (10:30 - 11:15)', rombel: 'XI_TKJ_1', subject: 'Pemrograman', room: 'Lab RPS 2', logged: false },
                { id: 'sch3', teacher: 'Bpk. Hendra Wijaya', day: 'Selasa', period: 'Jam ke-3 (08:30 - 09:15)', rombel: 'XI_TKJ_1', subject: 'Jaringan', room: 'Lab RPS 1', logged: true }
            ],

            // TEACHING JOURNALS LOGS
            journals: [
                { id: 'j1', date: '2026-08-25', scheduleId: 'sch3', teacher: 'Bpk. Hendra Wijaya', material: 'Routing Statis Cisco Packet Tracer', photo: 'Mengajar_Jaringan.jpg' }
            ],

            // GRANULAR ATTENDANCE DATABASE MODEL
            subjectAttendance: {
                '2026-08-25': {
                    'XI_TKJ_1': {
                        'Math': {
                            teacher: 'Bpk. Anton Hidayat',
                            logs: { 1001: 'H', 1002: 'H', 1003: 'H', 1004: 'H', 1005: 'H' }
                        },
                        'Indo': {
                            teacher: 'Ibu Dra. Sri Rahayu',
                            logs: { 1001: 'H', 1002: 'H', 1003: 'H', 1004: 'H', 1005: 'H' }
                        },
                        'Jaringan': {
                            teacher: 'Bpk. Hendra Wijaya',
                            logs: { 1001: 'H', 1002: 'H', 1003: 'H', 1004: 'A', 1005: 'A' }
                        },
                        'Pemrograman': {
                            teacher: 'Pak Danny',
                            logs: { 1001: 'H', 1002: 'H', 1003: 'H', 1004: 'H', 1005: 'A' }
                        }
                    }
                }
            },
            attendanceExceptions: [
                {
                    id: 'exc1',
                    nis: 1004,
                    studentName: 'Dodi Hermawan',
                    class: 'XI TKJ 1',
                    subject: 'Jaringan',
                    date: '2026-08-25',
                    status: 'A',
                    teacher: 'Bpk. Hendra Wijaya',
                    reason: 'Meninggalkan area kelas sebelum jam berakhir.',
                    verificationStatus: 'Pending Review',
                    verificationNotes: '',
                    verifier: ''
                },
                {
                    id: 'exc2',
                    nis: 1005,
                    studentName: 'Eko Saputro',
                    class: 'XI TKJ 1',
                    subject: 'Jaringan',
                    date: '2026-08-25',
                    status: 'A',
                    teacher: 'Bpk. Hendra Wijaya',
                    reason: 'Tanpa Keterangan (Alpa)',
                    verificationStatus: 'Pending Review',
                    verificationNotes: '',
                    verifier: ''
                },
                {
                    id: 'exc3',
                    nis: 1005,
                    studentName: 'Eko Saputro',
                    class: 'XI TKJ 1',
                    subject: 'Pemrograman',
                    date: '2026-08-25',
                    status: 'A',
                    teacher: 'Pak Danny',
                    reason: 'Bolos jam pelajaran terakhir',
                    verificationStatus: 'Pending Review',
                    verificationNotes: '',
                    verifier: ''
                }
            ]
        };

        // CURRENT ACTIVE STATE
        let activeRole = 'kepsek';
        let activeNav = 'dashboard';

        // ROLE SCHEMAS (MENUS & USER DETAILS)
        const roleConfig = {
            'kepsek': {
                name: 'Bpk. H. Guntur, M.Pd',
                roleLabel: 'Kepala Sekolah',
                avatarInitials: 'KS',
                menus: [
                    { id: 'dashboard', label: 'Dashboard Global', icon: 'fa-chart-line', pane: 'pane-kepsek-dashboard' },
                    { id: 'akademik', label: 'Pantau Akademik', icon: 'fa-square-poll-vertical', pane: 'pane-kepsek-akademik' },
                    { id: 'kesiswaan', label: 'Pantau Kesiswaan', icon: 'fa-shield-halved', pane: 'pane-kepsek-kesiswaan' },
                    { id: 'laporan', label: 'Laporan Eksekutif', icon: 'fa-file-contract', pane: 'pane-kepsek-laporan' }
                ]
            },
            'waka_kurikulum': {
                name: 'Ibu Ika, M.Pd',
                roleLabel: 'Waka Kurikulum',
                avatarInitials: 'WK',
                menus: [
                    { id: 'legger', label: 'Validasi Legger', icon: 'fa-file-signature', pane: 'pane-kurikulum-legger' },
                    { id: 'struktur', label: 'Struktur Kurikulum', icon: 'fa-sitemap', pane: 'pane-kurikulum-struktur' },
                    { id: 'rapor', label: 'Cetak e-Rapor', icon: 'fa-file-pdf', pane: 'pane-kurikulum-rapor' }
                ]
            },
            'waka_kesiswaan': {
                name: 'Bpk. Azhar',
                roleLabel: 'Waka Kesiswaan',
                avatarInitials: 'WS',
                menus: [
                    { id: 'absensi', label: 'Absensi Bermasalah', icon: 'fa-user-clock', pane: 'pane-kesiswaan-absensi' },
                    { id: 'pelanggaran', label: 'Input Pelanggaran', icon: 'fa-triangle-exclamation', pane: 'pane-kesiswaan-pelanggaran' },
                    { id: 'ortu', label: 'Panggilan Ortu', icon: 'fa-handshake', pane: 'pane-kesiswaan-ortu' }
                ]
            },
            'bk': {
                name: 'Ibu Mayang, S.Psi',
                roleLabel: 'Koordinator BK',
                avatarInitials: 'BK',
                menus: [
                    { id: 'asesmen', label: 'Asesmen Psikologis', icon: 'fa-clipboard-question', pane: 'pane-bk-asesmen' },
                    { id: 'kanban', label: 'Kanban Tindak Lanjut', icon: 'fa-list-check', pane: 'pane-bk-kanban' },
                    { id: 'riwayat', label: 'Jejak Rekam Siswa', icon: 'fa-address-card', pane: 'pane-bk-riwayat' }
                ]
            },
            'wali_kelas': {
                name: 'Ibu Siti, S.Kom',
                roleLabel: 'Wali Kelas XI TKJ 1',
                avatarInitials: 'WK',
                menus: [
                    { id: 'dashboard', label: 'Dashboard Kelas', icon: 'fa-users', pane: 'pane-wali-dashboard' },
                    { id: 'presensi', label: 'Rekap Presensi Mapel', icon: 'fa-calendar-check', pane: 'pane-wali-presensi' },
                    { id: 'catatan', label: 'Catatan Rapor (Sikap)', icon: 'fa-pen-clip', pane: 'pane-wali-catatan' },
                    { id: 'chat', label: 'Komunikasi Ortu', icon: 'fa-comments', pane: 'pane-wali-chat' }
                ]
            },
            'guru_wali': {
                name: 'Bpk. Wijaya, S.T',
                roleLabel: 'Guru Wali Akademik',
                avatarInitials: 'GW',
                menus: [
                    { id: 'gw_dashboard', label: 'Dashboard Akademik', icon: 'fa-chart-simple', pane: 'pane-guru-wali-dashboard' },
                    { id: 'gw_legger', label: 'Legger & Akademik', icon: 'fa-table-list', pane: 'pane-guru-wali-legger' },
                    { id: 'gw_rapor', label: 'Cetak e-Rapor', icon: 'fa-file-pdf', pane: 'pane-guru-wali-rapor' }
                ]
            },
            'guru_mapel': {
                name: 'Pak Danny, S.T',
                roleLabel: 'Guru Mapel',
                avatarInitials: 'GM',
                menus: [
                    { id: 'dashboard', label: 'Jurnal & Presensi', icon: 'fa-book-open', pane: 'pane-guru-mapel-dashboard' }
                ]
            },
            'siswa': {
                name: 'Andi Susanto',
                roleLabel: 'Siswa XI TKJ 1',
                avatarInitials: 'AS',
                menus: [
                    { id: 'siswa_portal', label: 'Siswa Portal', icon: 'fa-circle-user', pane: 'pane-siswa-portal' }
                ]
            },
            'ortu': {
                name: 'Bpk. Budi Susanto',
                roleLabel: 'Orang Tua (Wali)',
                avatarInitials: 'BS',
                menus: [
                    { id: 'ortu_portal', label: 'Portal Orang Tua', icon: 'fa-users-viewfinder', pane: 'pane-ortu-portal' }
                ]
            }
        };

        // WINDOW ONLOAD INITIALIZATION
        window.addEventListener('DOMContentLoaded', () => {
            updateNotificationDropdown();
        });

        // VIEWPORT FRAME SWITCHER
        function setViewport(mode) {
            const wrapper = document.getElementById('previewWrapper');
            const btnDesktop = document.getElementById('btn-vp-desktop');
            const btnMobile = document.getElementById('btn-vp-mobile');
            const sidebar = document.getElementById('sidebar-wrapper');
            const bottomNav = document.getElementById('mobile-navigation-bar');

            if (mode === 'mobile') {
                wrapper.classList.add('simulated-mobile');
                btnMobile.classList.add('active');
                btnDesktop.classList.remove('active');
                sidebar.classList.add('d-none');
                bottomNav.classList.remove('d-none');
                triggerToast("Tampilan disimulasikan ke Smartphone (412x840px).");
            } else {
                wrapper.classList.remove('simulated-mobile');
                btnDesktop.classList.add('active');
                btnMobile.classList.remove('active');

                if (activeRole === 'siswa' || activeRole === 'ortu') {
                    sidebar.classList.add('d-none');
                    bottomNav.classList.remove('d-none');
                } else {
                    sidebar.classList.remove('d-none');
                    bottomNav.classList.add('d-none');
                }
            }
        }

        // SIDEBAR TOGGLER
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-wrapper');
            if (sidebar.style.display === 'none') {
                sidebar.style.display = 'flex';
            } else {
                sidebar.style.display = 'none';
            }
        }

        // ROLE SWITCHING ENGINE
        function switchRole(roleKey) {
            activeRole = roleKey;
            const config = roleConfig[roleKey];

            document.getElementById('login-screen').classList.add('hidden-pane');
            document.getElementById('current-role-label').innerText = config.roleLabel;

            document.getElementById('sb-user-name').innerText = config.name;
            document.getElementById('sb-user-role').innerText = config.roleLabel;
            document.getElementById('sb-user-avatar').innerText = config.avatarInitials;

            document.getElementById('header-user-fullname').innerText = config.name.split(',')[0];
            document.getElementById('header-avatar-mini').innerText = config.avatarInitials;

            const menuList = document.getElementById('sidebar-menu-list');
            menuList.innerHTML = '';
            config.menus.forEach((menu, index) => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = `list-group-item list-group-item-action ${index === 0 ? 'active' : ''}`;
                item.onclick = (e) => {
                    e.preventDefault();
                    activateMenu(menu.id);
                };
                item.innerHTML = `<i class="fa-solid ${menu.icon}"></i> <span>${menu.label}</span>`;
                menuList.appendChild(item);
            });

            const sidebar = document.getElementById('sidebar-wrapper');
            const bottomNav = document.getElementById('mobile-navigation-bar');
            const header = document.querySelector('.app-header');

            if (roleKey === 'siswa' || roleKey === 'ortu') {
                sidebar.classList.add('d-none');
                if (header) header.classList.add('d-none');
                bottomNav.classList.remove('d-none');
                document.getElementById('previewWrapper').classList.add('simulated-mobile');
                document.getElementById('btn-vp-mobile').classList.add('active');
                document.getElementById('btn-vp-desktop').classList.remove('active');

                const targetPane = roleKey === 'siswa' ? 'pane-siswa-portal' : 'pane-ortu-portal';
                showPane(targetPane);
                activeNav = roleKey === 'siswa' ? 'siswa_portal' : 'ortu_portal';

                if (roleKey === 'siswa') {
                    renderSiswaPortal();
                } else {
                    renderOrtuPortal();
                }
            } else {
                if (header) header.classList.remove('d-none');
                const isChassisMobile = document.getElementById('previewWrapper').classList.contains('simulated-mobile');
                if (!isChassisMobile) {
                    sidebar.classList.remove('d-none');
                    bottomNav.classList.add('d-none');
                } else {
                    sidebar.classList.add('d-none');
                    bottomNav.classList.remove('d-none');
                }

                const firstMenu = config.menus[0];
                activateMenu(firstMenu.id);
            }

            rebuildRoleComponents();
            triggerToast(`Beralih ke hak akses: ${config.roleLabel}`);
        }

        function activateMenu(menuId) {
            activeNav = menuId;
            const items = document.querySelectorAll('#sidebar-menu-list .list-group-item');
            const role = roleConfig[activeRole];
            const activeIndex = role.menus.findIndex(m => m.id === menuId);

            items.forEach((item, idx) => {
                if (idx === activeIndex) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            const activeMenuObj = role.menus.find(m => m.id === menuId);
            if (activeMenuObj) {
                showPane(activeMenuObj.pane);
            }
        }

        function showPane(paneId) {
            const panes = document.querySelectorAll('.pane-content');
            panes.forEach(pane => {
                if (pane.id === paneId) {
                    pane.classList.remove('hidden-pane');
                } else {
                    pane.classList.add('hidden-pane');
                }
            });
        }

        function rebuildRoleComponents() {
            populateStudentDropdowns();

            if (activeRole === 'kepsek') {
                renderKepsekDashboard();
                renderKepsekAkademik();
                renderKepsekKesiswaan();
            } else if (activeRole === 'waka_kurikulum') {
                renderKurikulumLegger();
                renderKurikulumRapor();
                renderKurikulumSchedulesList();
            } else if (activeRole === 'waka_kesiswaan') {
                renderKesiswaanAbsensi();
                renderKesiswaanPelanggaran();
                renderKesiswaanSummons();
            } else if (activeRole === 'bk') {
                renderBKAsesmen();
                renderBKKanban();
                renderBKStudentList();
            } else if (activeRole === 'wali_kelas') {
                renderWaliDashboard();
                renderWaliPresensi();
                renderWaliCatatan();
                renderWaliChats();
            } else if (activeRole === 'guru_wali') {
                renderGuruWaliDashboard();
                renderGuruWaliLegger();
                renderGuruWaliRapor();
            } else if (activeRole === 'guru_mapel') {
                renderGuruMapelSchedules();
                renderGuruMapelJournals();
                loadGuruMapelScheduleOptions();
            }
        }

        function handleLoginSubmit() {
            const selectedRole = document.getElementById('login-role-selector').value;
            switchRole(selectedRole);
        }

        function logout() {
            document.getElementById('login-screen').classList.remove('hidden-pane');
            setViewport('desktop');
        }

        // ==========================================
        // 1. KEPALA SEKOLAH LOGIC
        // ==========================================
        function renderKepsekDashboard() {
            document.getElementById('stat-total-siswa').innerText = mockDatabase.students.length + 1240;
            document.getElementById('stat-kasus').innerText = mockDatabase.students.filter(s => s.totalViolationPoints > 0).length + 7;
        }

        function renderKepsekAkademik() { }

        function renderKepsekKesiswaan() {
            const leadContainer = document.getElementById('kepsek-violations-lead');
            leadContainer.innerHTML = '';

            const sorted = [...mockDatabase.students].sort((a, b) => b.totalViolationPoints - a.totalViolationPoints);
            sorted.slice(0, 3).forEach(student => {
                const item = document.createElement('div');
                item.className = 'flex justify-between items-center border-b border-slate-700/60 pb-2';
                item.innerHTML = `
                    <div>
                        <span class="font-bold block small" style="font-size: 13px;">${student.name}</span>
                        <span class="text-slate-400" style="font-size: 11px;">${student.class}</span>
                    </div>
                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-lg">${student.totalViolationPoints} Poin</span>
                `;
                leadContainer.appendChild(item);
            });
        }

        // ==========================================
        // 2. WAKA KURIKULUM & GENERAL LEGGER LOGIC
        // ==========================================
        function filterAcademicGrid() {
            renderKurikulumLegger();
        }

        function renderKurikulumLegger() {
            const subject = document.getElementById('filter-subject').value;
            const rombel = document.getElementById('filter-class').value;

            document.getElementById('legger-grid-heading').innerText = `Matriks Rombel: ${rombel.replace(/_/g, ' ')} | Mapel: ${subject === 'all' ? 'Legger Rerata' : subject}`;

            const thead = document.getElementById('legger-validation-thead');
            const tbody = document.getElementById('legger-validation-tbody');
            tbody.innerHTML = '';

            if (subject === 'all') {
                thead.innerHTML = `
                    <tr>
                        <th>Siswa</th>
                        <th class="text-center">Matematika</th>
                        <th class="text-center">B. Indonesia</th>
                        <th class="text-center">Jaringan (Prod)</th>
                        <th class="text-center">Pemrograman (Prod)</th>
                        <th class="text-center">Rerata Rapor</th>
                        <th>Status Ketuntasan</th>
                    </tr>
                `;

                mockDatabase.students.forEach(student => {
                    const avg = ((student.grades.Math + student.grades.Indo + student.grades.Jaringan + student.grades.Pemrograman) / 4).toFixed(1);
                    const isTuntas = avg >= 75;
                    const statusBadge = isTuntas
                        ? `<span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">Tuntas Rapor</span>`
                        : `<span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 badge-pill-custom">Belum Tuntas</span>`;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-bold">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                        <td class="text-center font-monospace">${student.grades.Math}</td>
                        <td class="text-center font-monospace">${student.grades.Indo}</td>
                        <td class="text-center font-monospace">${student.grades.Jaringan}</td>
                        <td class="text-center font-monospace">${student.grades.Pemrograman}</td>
                        <td class="text-center font-bold text-primary font-monospace">${avg}</td>
                        <td>${statusBadge}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                thead.innerHTML = `
                    <tr>
                        <th>Siswa</th>
                        <th class="text-center" style="width: 140px;">Nilai Harian</th>
                        <th class="text-center" style="width: 140px;">Nilai PTS</th>
                        <th class="text-center" style="width: 140px;">Nilai PAS</th>
                        <th class="text-center">Nilai Akhir Legger</th>
                        <th>Kriteria Ketuntasan</th>
                        <th>Hasil Evaluasi</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                `;

                mockDatabase.students.forEach(student => {
                    const studentGrade = student.grades[subject];
                    const isTuntas = studentGrade >= 75;
                    const statusBadge = isTuntas
                        ? `<span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 badge-pill-custom">TUNTAS KKM</span>`
                        : `<span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 badge-pill-custom">TIDAK TUNTAS</span>`;

                    const editableInput = (activeRole === 'waka_kurikulum')
                        ? `<input type="number" id="grade-edit-${student.nis}" class="form-control form-control-sm text-center font-monospace font-bold" style="width: 70px; margin: 0 auto;" value="${studentGrade}">`
                        : `<span class="font-monospace font-bold">${studentGrade}</span>`;

                    const saveBtn = (activeRole === 'waka_kurikulum')
                        ? `<button onclick="updateGradeLegger(${student.nis}, '${subject}')" class="btn btn-brand-primary btn-xs py-1 px-2 rounded-2"><i class="fa-solid fa-floppy-disk"></i></button>`
                        : `<span class="text-slate-400 small italic">-</span>`;

                    const tr = document.createElement('tr');
                    tr.className = 'align-middle';
                    tr.innerHTML = `
                        <td class="font-bold">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                        <td class="text-center font-monospace">${studentGrade - 3}</td>
                        <td class="text-center font-monospace">${studentGrade - 1}</td>
                        <td class="text-center font-monospace">${studentGrade + 1}</td>
                        <td class="text-center font-bold text-primary font-monospace">${editableInput}</td>
                        <td class="text-center">75</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">${saveBtn}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            renderAuditLogs();

            const badge = document.getElementById('legger-status-badge');
            const btn = document.getElementById('btn-approve-legger');
            if (mockDatabase.leggerApproved) {
                badge.innerText = 'Valid & Terkunci (Disetujui Waka)';
                badge.className = 'badge bg-success badge-pill-custom';
                btn.className = 'btn btn-secondary btn-sm rounded-lg disabled';
                btn.innerHTML = '<i class="fa-solid fa-lock mr-1"></i> Legger Terkunci';
            } else {
                badge.innerText = 'Draft (Menunggu Validasi Waka)';
                badge.className = 'badge bg-warning badge-pill-custom';
                btn.className = 'btn btn-success btn-sm rounded-lg font-bold';
                btn.innerHTML = '<i class="fa-solid fa-file-signature mr-1"></i> Validasi & Kunci Legger';
            }
        }

        function updateGradeLegger(studentNis, subjectKey) {
            const input = document.getElementById(`grade-edit-${studentNis}`);
            if (!input) return;
            const newGradeVal = parseInt(input.value);

            if (newGradeVal < 0 || newGradeVal > 100 || isNaN(newGradeVal)) {
                alert("Nilai harus berkisar antara 0 - 100!");
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == studentNis);
            if (student) {
                const oldGrade = student.grades[subjectKey];
                student.grades[subjectKey] = newGradeVal;

                const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const dateStr = new Date().toISOString().split('T')[0];
                mockDatabase.auditLogs.unshift({
                    timestamp: dateStr + ' ' + timeStr,
                    actor: roleConfig[activeRole].roleLabel,
                    desc: `Mengubah Nilai ${subjectKey} ${student.name}: ${oldGrade} -> ${newGradeVal}`,
                    impact: `Rerata Rapor: ${((student.grades.Math + student.grades.Indo + student.grades.Jaringan + student.grades.Pemrograman) / 4).toFixed(1)}`
                });

                renderKurikulumLegger();
                triggerToast(`Nilai ${subjectKey} untuk ${student.name} berhasil diperbarui!`);
            }
        }

        function renderAuditLogs() {
            const tbody = document.getElementById('academic-audit-log-tbody');
            const tbodyGW = document.getElementById('gw-audit-log-tbody');

            const renderRows = (targetTbody) => {
                if (!targetTbody) return;
                targetTbody.innerHTML = '';
                mockDatabase.auditLogs.forEach(log => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-monospace small">${log.timestamp}</td>
                        <td class="font-bold">${log.actor}</td>
                        <td>${log.desc}</td>
                        <td><span class="badge bg-secondary-subtle text-slate-400">${log.impact}</span></td>
                    `;
                    targetTbody.appendChild(tr);
                });
            };

            renderRows(tbody);
            renderRows(tbodyGW);
        }

        function addJpRecord() {
            const sub = document.getElementById('jp-subject').value;
            const load = document.getElementById('jp-load').value;
            const teacher = document.getElementById('jp-teacher').value;
            const cat = document.getElementById('jp-category').value;

            const tbody = document.getElementById('curriculum-structure-tbody');

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4">${sub}</td>
                <td class="text-center">${load} JP</td>
                <td>${teacher}</td>
                <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
            `;

            tbody.appendChild(tr);

            document.getElementById('jp-subject').value = '';
            document.getElementById('jp-load').value = '';
            document.getElementById('jp-teacher').value = '';

            triggerToast(`Rencana alokasi mapel ${sub} (${load} JP) berhasil ditambahkan!`);
        }

        // ADD NEW SCHEDULE (Sebaran Jadwal Mapel)
        function addScheduleRecord() {
            const teacher = document.getElementById('sch-teacher').value;
            const day = document.getElementById('sch-day').value;
            const period = document.getElementById('sch-period').value;
            const rombel = document.getElementById('sch-rombel').value;
            const room = document.getElementById('sch-room').value;
            const subject = document.getElementById('sch-subject').value;

            const newSch = {
                id: 'sch' + (mockDatabase.schedules.length + 1),
                teacher: teacher,
                day: day,
                period: period,
                rombel: rombel,
                subject: subject,
                room: room,
                logged: false
            };

            mockDatabase.schedules.push(newSch);
            renderKurikulumSchedulesList();
            triggerToast(`Jadwal KBM Rombel ${rombel.replace(/_/g, ' ')} berhasil diterbitkan!`);
        }

        function renderKurikulumSchedulesList() {
            const tbody = document.getElementById('schedules-list-tbody');
            if (tbody) {
                tbody.innerHTML = '';
                mockDatabase.schedules.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-bold">${s.teacher}</td>
                        <td>${s.day}</td>
                        <td>${s.period}</td>
                        <td>${s.rombel.replace(/_/g, ' ')}</td>
                        <td>${s.subject}</td>
                        <td><span class="badge bg-secondary">${s.room}</span></td>
                        <td class="text-center">
                            <button onclick="deleteSchedule('${s.id}')" class="btn btn-outline-danger btn-xs py-0.5 px-2" style="font-size: 11px;"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function deleteSchedule(id) {
            mockDatabase.schedules = mockDatabase.schedules.filter(s => s.id !== id);
            renderKurikulumSchedulesList();
            triggerToast("Slot jadwal berhasil dihapus dari sebaran kurikulum!");
        }

        function approveLegger() {
            mockDatabase.leggerApproved = true;
            renderKurikulumLegger();
            triggerToast("Legger kelas XI TKJ 1 berhasil disahkan dan dikunci!");
        }

        function renderKurikulumRapor() {
            const grid = document.getElementById('kurikulum-rapor-grid');
            grid.innerHTML = '';

            const classes = [
                { name: 'X TKJ 1', wali: 'Bpk. Anton Hidayat', status: 'Draft', color: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
                { name: 'XI TKJ 1', wali: 'Ibu Siti Rahmawati', status: mockDatabase.leggerApproved ? 'Valid' : 'Menunggu Validasi', color: mockDatabase.leggerApproved ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
                { name: 'XII RPL 1', wali: 'Ibu Dwi Astuti', status: 'Selesai / Terkirim', color: 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }
            ];

            classes.forEach(c => {
                const card = document.createElement('div');
                card.className = 'col-md-4';
                card.innerHTML = `
                    <div class="card p-3 border-0 rounded-xl shadow-sm bg-slate-800/80 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h6 class="font-bold m-0 text-slate-100">${c.name}</h6>
                                <span class="badge ${c.color} badge-pill-custom">${c.status}</span>
                            </div>
                            <p class="text-slate-400 small m-0 mb-3">Wali Kelas: ${c.wali}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="triggerToast('Legger ${c.name} dicetak')" class="btn btn-outline-secondary btn-sm rounded-lg flex-grow-1">Cetak Legger</button>
                            <button onclick="triggerToast('e-Rapor ${c.name} digenerate')" class="btn btn-brand-primary btn-sm rounded-lg flex-grow-1" ${c.status !== 'Valid' && c.status !== 'Selesai / Terkirim' ? 'disabled' : ''}>Cetak Rapor</button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        // ==========================================
        // 3. WAKA KESISWAAN LOGIC
        // ==========================================
        function renderKesiswaanAbsensi() {
            const tbody = document.getElementById('kesiswaan-absensi-tbody');
            tbody.innerHTML = '';

            const problematic = mockDatabase.students.filter(s => s.totalAlpa >= 3);
            problematic.forEach(student => {
                const warningLevel = student.totalAlpa >= 5
                    ? `<span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 badge-pill-custom">SP 1 (Panggilan Ortu)</span>`
                    : `<span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20 badge-pill-custom">Teguran Keras</span>`;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-bold">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                    <td class="text-center">${student.class}</td>
                    <td class="text-center font-bold text-danger fs-5">${student.totalAlpa}</td>
                    <td>${warningLevel}</td>
                    <td class="text-end">
                        <button onclick="openSummonModalWithStudent(${student.nis}, '${student.name}')" class="btn btn-outline-danger btn-sm rounded-lg">
                            <i class="fa-regular fa-envelope mr-1"></i> Panggil Ortu
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openSummonModalWithStudent(nis, name) {
            document.getElementById('sum-student-select').value = nis;
            document.getElementById('sum-parent-name').value = `Orang Tua ${name}`;
            document.getElementById('sum-reason').value = `Akumulasi ketidakhadiran (Alpa) tinggi terdeteksi di sistem absensi.`;

            const modalEl = document.getElementById('addSummonModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function submitSummonModal() {
            const studentNis = document.getElementById('sum-student-select').value;
            const parentName = document.getElementById('sum-parent-name').value;
            const sDate = document.getElementById('sum-date').value;
            const sTime = document.getElementById('sum-time').value;
            const sRoom = document.getElementById('sum-room').value;
            const sReason = document.getElementById('sum-reason').value;

            if (!parentName.trim() || !sDate.trim()) {
                alert("Tanggal dan Nama Orang tua wajib diisi!");
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == studentNis);
            if (student) {
                mockDatabase.summons.unshift({
                    id: 's' + (mockDatabase.summons.length + 1),
                    student: student.name,
                    class: student.class,
                    date: sDate,
                    time: sTime,
                    status: 'Menunggu Konfirmasi'
                });

                const modal = bootstrap.Modal.getInstance(document.getElementById('addSummonModal'));
                modal.hide();

                document.getElementById('sum-parent-name').value = '';
                document.getElementById('sum-reason').value = '';

                renderKesiswaanSummons();
                triggerToast(`Jadwal panggilan untuk Wali ${student.name} berhasil diterbitkan di ${sRoom}!`);
            }
        }

        function renderKesiswaanPelanggaran() {
            const tbody = document.getElementById('kesiswaan-pelanggaran-tbody');
            tbody.innerHTML = '';
            const filterText = document.getElementById('filter-violation-student').value.toLowerCase();

            mockDatabase.students.forEach(student => {
                if (student.name.toLowerCase().includes(filterText)) {
                    student.violations.forEach(v => {
                        const tr = document.createElement('tr');
                        let levelClass = 'bg-secondary';
                        if (v.points >= 25) levelClass = 'bg-danger';
                        else if (v.points >= 10) levelClass = 'bg-warning text-slate-100';

                        tr.innerHTML = `
                            <td class="font-monospace small">${v.date}</td>
                            <td class="font-bold">${student.name}</td>
                            <td>${student.class}</td>
                            <td>${v.title}</td>
                            <td class="text-center font-bold">${v.points}</td>
                            <td><span class="badge ${levelClass} badge-pill-custom">${v.category}</span></td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            });
        }

        function renderKesiswaanSummons() {
            const grid = document.getElementById('kesiswaan-summons-grid');
            grid.innerHTML = '';

            mockDatabase.summons.forEach(s => {
                let statusClass = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                if (s.status.includes('Hadir')) statusClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';

                const card = document.createElement('div');
                card.className = 'col-md-6';
                card.innerHTML = `
                    <div class="card p-3 border-0 rounded-xl shadow-sm bg-slate-800/80 border-l border-slate-700/60 border-4 border-warning">
                        <div class="flex justify-between align-items-start mb-2">
                            <div>
                                <h6 class="font-bold text-slate-100 mb-1">Mediasi: Wali dari ${s.student}</h6>
                                <span class="text-slate-400 small">${s.class}</span>
                            </div>
                            <span class="badge ${statusClass} badge-pill-custom">${s.status}</span>
                        </div>
                        <p class="small text-slate-400 m-0 mb-3"><i class="fa-regular fa-clock mr-1"></i> ${s.date} Pukul ${s.time}</p>
                        <div class="flex gap-2">
                            <button onclick="triggerToast('Menghubungi Ortu ${s.student}...')" class="btn btn-outline-secondary btn-sm rounded-lg flex-grow-1"><i class="fa-brands fa-whatsapp"></i> Chat</button>
                            <button onclick="confirmSummonArrival('${s.id}')" class="btn btn-brand-primary btn-sm rounded-lg flex-grow-1" ${s.status.includes('Hadir') ? 'disabled' : ''}>Konfirmasi Hadir</button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        function summonParent(studentName) {
            const id = 's' + (mockDatabase.summons.length + 1);
            mockDatabase.summons.unshift({
                id: id,
                student: studentName,
                class: 'XI TKJ 1',
                date: 'Kamis, 27 Agustus 2026',
                time: '10:00 WIB',
                status: 'Menunggu Konfirmasi'
            });
            renderKesiswaanSummons();
            triggerToast(`Surat Panggilan Orang Tua untuk ${studentName} berhasil dikirim!`);
        }

        function confirmSummonArrival(summonId) {
            const s = mockDatabase.summons.find(sum => sum.id === summonId);
            if (s) {
                s.status = 'Hadir / Mediasi Selesai';
                renderKesiswaanSummons();
                triggerToast(`Kehadiran Wali Murid dari ${s.student} telah terkonfirmasi.`);
            }
        }

        function updatePoinSuggestion() {
            const select = document.getElementById('violation-category-select');
            const selectedOpt = select.options[select.selectedIndex];
            const poin = selectedOpt.getAttribute('data-poin');
            document.getElementById('violation-points').value = poin;
        }

        // ==========================================
        // 4. BK (COUNSELING) LOGIC
        // ==========================================
        function renderBKAsesmen() {
            const tbody = document.getElementById('bk-asesmen-tbody');
            tbody.innerHTML = '';

            mockDatabase.students.forEach(student => {
                let badgeClass = 'bg-success';
                if (student.counselingStress >= 8) badgeClass = 'bg-danger';
                else if (student.counselingStress >= 5) badgeClass = 'bg-warning text-slate-100';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-bold">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">${student.class}</span></td>
                    <td class="text-center"><span class="badge ${badgeClass} badge-pill-custom">Stress Level: ${student.counselingStress}/10</span></td>
                    <td><span class="badge bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 badge-pill-custom">${student.counselingCareer}</span></td>
                    <td class="small text-slate-400 italic">"${student.counselingNote}"</td>
                    <td><span class="confidential-badge"><i class="fa-solid fa-lock"></i> RAHASIA</span></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderBKKanban() {
            const antreanCol = document.getElementById('kanban-antrean');
            const prosesCol = document.getElementById('kanban-proses');
            const selesaiCol = document.getElementById('kanban-selesai');

            antreanCol.innerHTML = '';
            prosesCol.innerHTML = '';
            selesaiCol.innerHTML = '';

            mockDatabase.counselingKanban.forEach(k => {
                const card = document.createElement('div');
                card.className = 'kanban-card text-slate-100';

                let btnHtml = '';
                if (k.stage === 'antrean') {
                    btnHtml = `<button onclick="moveKanban('${k.id}', 'proses')" class="btn btn-outline-primary btn-sm w-full py-1 font-semibold" style="font-size: 10px;">Proses Kasus <i class="fa-solid fa-arrow-right"></i></button>`;
                } else if (k.stage === 'proses') {
                    btnHtml = `<button onclick="moveKanban('${k.id}', 'selesai')" class="btn btn-outline-success btn-sm w-full py-1 font-semibold" style="font-size: 10px;">Tutup Kasus <i class="fa-solid fa-check"></i></button>`;
                } else {
                    btnHtml = `<span class="badge bg-secondary-subtle text-slate-400 w-full block text-center py-1">Kasus Ditutup</span>`;
                }

                card.innerHTML = `
                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase mb-1" style="font-size: 8px;">${k.category}</span>
                    <h6 class="font-bold text-slate-100 mb-1" style="font-size: 13px;">${k.title}</h6>
                    <p class="text-slate-400 mb-2" style="font-size: 11px;">Siswa: ${k.student}</p>
                    ${btnHtml}
                `;

                if (k.stage === 'antrean') antreanCol.appendChild(card);
                else if (k.stage === 'proses') prosesCol.appendChild(card);
                else selesaiCol.appendChild(card);
            });
        }

        function submitBKCaseModal() {
            const studentNis = document.getElementById('kan-student-select').value;
            const title = document.getElementById('kan-title').value;
            const urgency = document.getElementById('kan-urgency').value;
            const conf = document.getElementById('kan-confidential').value;

            if (!title.trim()) {
                alert("Topik kasus wajib diisi!");
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == studentNis);
            if (student) {
                mockDatabase.counselingKanban.unshift({
                    id: 'k' + (mockDatabase.counselingKanban.length + 1),
                    title: title,
                    student: student.name,
                    category: urgency,
                    stage: 'antrean'
                });

                const modal = bootstrap.Modal.getInstance(document.getElementById('addBKCaseModal'));
                modal.hide();
                document.getElementById('kan-title').value = '';

                renderBKKanban();
                triggerToast(`Kasus baru "${title}" (${conf}) masuk ke Antrean Kanban.`);
            }
        }

        function moveKanban(id, newStage) {
            const card = mockDatabase.counselingKanban.find(c => c.id === id);
            if (card) {
                card.stage = newStage;
                renderBKKanban();
                triggerToast(`Kasus "${card.title}" dialirkan ke kolom ${newStage.toUpperCase()}`);
            }
        }

        function submitBKAsesmen() {
            const studentNis = document.getElementById('asesmen-student-select').value;
            const stress = parseInt(document.getElementById('asesmen-stress').value);
            const career = document.getElementById('asesmen-career').value;
            const notes = document.getElementById('asesmen-notes').value;
            const isConf = document.getElementById('flag-confidential').checked;

            const student = mockDatabase.students.find(s => s.nis == studentNis);
            if (student) {
                student.counselingStress = stress;
                student.counselingCareer = career;
                student.counselingNote = notes || 'Sesi bimbingan psikologi berkala.';

                const modal = bootstrap.Modal.getInstance(document.getElementById('addAsesmenModal'));
                modal.hide();
                document.getElementById('asesmen-notes').value = '';

                rebuildRoleComponents();
                triggerToast(`Sesi Konseling & Asesmen ${student.name} tersimpan ${isConf ? '(Status: RAHASIA)' : '(Status: UMUM)'}!`);
            }
        }

        function renderBKStudentList() {
            const select = document.getElementById('bk-search-student-dropdown');
            select.innerHTML = '<option value="">-- Pilih Siswa Binaan --</option>';

            const summonSelect = document.getElementById('sum-student-select');
            if (summonSelect) summonSelect.innerHTML = '';

            const kanSelect = document.getElementById('kan-student-select');
            if (kanSelect) kanSelect.innerHTML = '';

            mockDatabase.students.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.nis;
                opt.innerText = `${s.name} (${s.class})`;
                select.appendChild(opt);

                if (summonSelect) {
                    const optSum = opt.cloneNode(true);
                    summonSelect.appendChild(optSum);
                }
                if (kanSelect) {
                    const optKan = opt.cloneNode(true);
                    kanSelect.appendChild(optKan);
                }
            });
        }

        function loadStudentHistoryBK() {
            const nis = document.getElementById('bk-search-student-dropdown').value;
            const resultDiv = document.getElementById('bk-student-history-result');

            if (!nis) {
                resultDiv.classList.add('hidden-pane');
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                document.getElementById('bk-hist-name').innerText = student.name;
                document.getElementById('bk-hist-class').innerText = `Kelas ${student.class} | NIS: ${student.nis}`;
                document.getElementById('bk-hist-points').innerText = `${student.totalViolationPoints} Poin`;

                const rate = (((30 - student.totalAlpa) / 30) * 100).toFixed(1);
                document.getElementById('bk-hist-attendance').innerText = `${rate}% Hadir`;
                document.getElementById('bk-hist-notes').innerText = `"${student.counselingNote}"`;

                const timeline = document.getElementById('bk-timeline-container');
                timeline.innerHTML = '';

                if (student.violations.length === 0) {
                    timeline.innerHTML = '<p class="text-slate-400 small italic text-center">Tidak ada catatan pelanggaran tata tertib.</p>';
                } else {
                    student.violations.forEach(v => {
                        const item = document.createElement('div');
                        item.className = 'mb-4 position-relative';
                        item.innerHTML = `
                            <div class="position-absolute bg-danger rounded-circle" style="width: 10px; height: 10px; left: -25px; top: 6px; border: 2px solid #FFF;"></div>
                            <span class="text-slate-400 font-monospace" style="font-size: 11px;">${v.date}</span>
                            <h6 class="font-bold text-slate-100 m-0 mt-1" style="font-size: 14px;">${v.title}</h6>
                            <p class="text-danger small m-0">Kategori: ${v.category} (+${v.points} Poin Pelanggaran)</p>
                        `;
                        timeline.appendChild(item);
                    });
                }
                resultDiv.classList.remove('hidden-pane');
            }
        }

        function submitIndividualBKLog() {
            const topic = document.getElementById('bk-indiv-log-title').value;
            const cat = document.getElementById('bk-indiv-log-urgency').value;
            const desc = document.getElementById('bk-indiv-log-desc').value;
            const nis = document.getElementById('bk-search-student-dropdown').value;

            if (!topic.trim() || !desc.trim()) {
                alert("Semua data isian log rujukan individu harus diisi!");
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                const dateStr = new Date().toISOString().split('T')[0];
                student.violations.unshift({
                    date: dateStr,
                    title: `[BK Konseling] ${topic}: ${desc}`,
                    category: cat,
                    points: 0
                });

                document.getElementById('bk-indiv-log-title').value = '';
                document.getElementById('bk-indiv-log-desc').value = '';

                loadStudentHistoryBK();
                triggerToast(`Catatan konseling individu "${topic}" berhasil ditambahkan ke timeline!`);
            }
        }

        // ==========================================
        // 5. GURU WALI (CLASS ADVISOR / HOME ROOM) LOGIC
        // ==========================================
        function renderWaliDashboard() {
            document.getElementById('wali-dashboard-name').innerText = roleConfig['wali_kelas'].name;
            const container = document.getElementById('wali-attendance-summary');
            if (container) {
                let hadir = 0, sakit = 0, izin = 0, alpa = 0;
                mockDatabase.students.forEach(s => {
                    if (s.attendanceToday === 'H') hadir++;
                    else if (s.attendanceToday === 'S') sakit++;
                    else if (s.attendanceToday === 'I') izin++;
                    else if (s.attendanceToday === 'A') alpa++;
                });

                container.innerHTML = `
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center border-b border-slate-700/60 pb-1">
                            <span class="small"><i class="fa-solid fa-circle text-success mr-1" style="font-size: 8px;"></i> Hadir (Present)</span>
                            <span class="font-bold font-monospace text-success">${hadir} Siswa</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-700/60 pb-1">
                            <span class="small"><i class="fa-solid fa-circle text-warning mr-1" style="font-size: 8px;"></i> Sakit (Sick)</span>
                            <span class="font-bold font-monospace text-warning">${sakit} Siswa</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-700/60 pb-1">
                            <span class="small"><i class="fa-solid fa-circle text-info mr-1" style="font-size: 8px;"></i> Izin (Permitted)</span>
                            <span class="font-bold font-monospace text-info">${izin} Siswa</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="small"><i class="fa-solid fa-circle text-danger mr-1" style="font-size: 8px;"></i> Alpa (Absent)</span>
                            <span class="font-bold font-monospace text-danger">${alpa} Siswa</span>
                        </div>
                    </div>
                `;
            }

            const warningList = document.getElementById('wali-warning-list');
            warningList.innerHTML = '';

            const warningStudents = mockDatabase.students.filter(s => s.totalViolationPoints > 20 || s.totalAlpa >= 3);
            if (warningStudents.length === 0) {
                warningList.innerHTML = '<li class="list-group-item text-slate-400 small italic text-center py-3">Semua siswa kondusif.</li>';
            } else {
                warningStudents.forEach(s => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item flex justify-between items-center px-0 py-2';
                    li.innerHTML = `
                        <div>
                            <span class="font-bold block small" style="font-size: 13px;">${s.name}</span>
                            <span class="text-slate-400" style="font-size: 10px;">${s.totalAlpa}x Alpa, ${s.totalViolationPoints} Poin</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button onclick="openEscalationReferralModal(${s.nis}, '${s.name}')" class="btn btn-outline-warning btn-xs py-1 px-2" style="font-size: 10px;"><i class="fa-solid fa-triangle-exclamation"></i> Rujuk</button>
                        </div>
                    `;
                    warningList.appendChild(li);
                });
            }
        }

        // GRaNULAR ATTENDANCE LOGS RENDER FOR GURU WALI (WALI KELAS)
        function renderWaliPresensi() {
            // A. Render Aggregated Matrix table
            const matrixTbody = document.getElementById('wali-aggregated-presensi-tbody');
            if (matrixTbody) {
                matrixTbody.innerHTML = '';

                const dateKey = '2026-08-25';
                const classKey = 'XI_TKJ_1';
                const dayAtt = mockDatabase.subjectAttendance[dateKey][classKey];

                mockDatabase.students.forEach(student => {
                    const mStatus = dayAtt['Math'].logs[student.nis] || 'H';
                    const iStatus = dayAtt['Indo'].logs[student.nis] || 'H';
                    const jStatus = dayAtt['Jaringan'].logs[student.nis] || 'H';
                    const pStatus = dayAtt['Pemrograman'].logs[student.nis] || 'H';

                    const getBadge = (status) => {
                        if (status === 'H') return '<span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 py-1 px-2.5">Hadir</span>';
                        if (status === 'S') return '<span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20 py-1 px-2.5">Sakit</span>';
                        if (status === 'I') return '<span class="badge bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 py-1 px-2.5">Izin</span>';
                        return '<span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 py-1 px-2.5">Alpa</span>';
                    };

                    const rawArray = [mStatus, iStatus, jStatus, pStatus];
                    const hadirCount = rawArray.filter(x => x === 'H').length;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-start font-bold">${student.name}</td>
                        <td>${getBadge(mStatus)}</td>
                        <td>${getBadge(iStatus)}</td>
                        <td>${getBadge(jStatus)}</td>
                        <td>${getBadge(pStatus)}</td>
                        <td class="font-bold text-slate-400 font-monospace">${hadirCount}/4 Hadir</td>
                    `;
                    matrixTbody.appendChild(tr);
                });
            }

            // B. Render Exceptions List for Guru Wali (Wali Kelas)
            const exceptionsTbody = document.getElementById('wali-exceptions-tbody');
            if (exceptionsTbody) {
                exceptionsTbody.innerHTML = '';

                const pendingExceptions = mockDatabase.attendanceExceptions;
                if (pendingExceptions.length === 0) {
                    exceptionsTbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-400 small py-3">Tidak ada ketidakhadiran yang memerlukan verifikasi.</td></tr>';
                } else {
                    pendingExceptions.forEach(exc => {
                        let statusColor = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                        if (exc.verificationStatus.includes('Valid')) statusColor = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                        else if (exc.verificationStatus.includes('Invalid')) statusColor = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';

                        const tr = document.createElement('tr');
                        const isPending = (exc.verificationStatus === 'Pending Review');
                        const actBtn = isPending
                            ? `<button onclick="openVerificationModal('${exc.id}')" class="btn btn-warning btn-xs py-1 px-2 rounded-2 font-bold text-slate-100" style="font-size: 10px;"><i class="fa-solid fa-user-shield"></i> Verifikasi</button>`
                            : `<span class="text-slate-400 font-semibold" style="font-size: 11px;">Oleh ${exc.verifier}</span>`;

                        tr.innerHTML = `
                            <td class="font-bold">${exc.studentName}</td>
                            <td>${exc.subject}</td>
                            <td class="small text-slate-400">${exc.teacher}</td>
                            <td class="small italic text-slate-400">"${exc.reason}"</td>
                            <td><span class="badge ${statusColor} badge-pill-custom">${exc.verificationStatus}</span></td>
                            <td class="text-center">${actBtn}</td>
                        `;
                        exceptionsTbody.appendChild(tr);
                    });
                }
            }
        }

        // OPEN ATTENDANCE EXCEPTION VERIFICATION MODAL
        function openVerificationModal(excId) {
            const exc = mockDatabase.attendanceExceptions.find(e => e.id === excId);
            if (exc) {
                document.getElementById('verify-exception-id').value = exc.id;
                document.getElementById('verify-student-name').value = exc.studentName;
                document.getElementById('verify-subject').value = exc.subject;
                document.getElementById('verify-teacher').value = exc.teacher;
                document.getElementById('verify-raw-reason').value = exc.reason;
                document.getElementById('verify-notes').value = '';

                const modal = new bootstrap.Modal(document.getElementById('verifyAttendanceModal'));
                modal.show();
            }
        }

        // SUBMIT ATTENDANCE VERIFICATION
        function submitAttendanceVerification() {
            const excId = document.getElementById('verify-exception-id').value;
            const decision = document.getElementById('verify-decision').value;
            const notes = document.getElementById('verify-notes').value;
            const sendAndroidAlert = document.getElementById('verify-send-app-alert').checked;

            if (!notes.trim()) {
                alert("Catatan verifikator wajib diisi!");
                return;
            }

            const exc = mockDatabase.attendanceExceptions.find(e => e.id === excId);
            if (exc) {
                exc.verificationStatus = decision;
                exc.verificationNotes = notes;
                exc.verifier = roleConfig[activeRole].roleLabel;

                const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const dateStr = new Date().toISOString().split('T')[0];

                const actorLabel = roleConfig[activeRole].roleLabel;
                mockDatabase.auditLogs.unshift({
                    timestamp: dateStr + ' ' + timeStr,
                    actor: actorLabel,
                    desc: `Verifikasi Absensi ${exc.studentName} di Mapel ${exc.subject}: ${decision === 'Verified Valid' ? 'Valid/Disetujui' : 'Tidak Valid/Bolos'}`,
                    impact: decision === 'Verified Valid' ? 'Dispen / Bebas Pelanggaran' : 'Pelanggaran Alpa +1'
                });

                const student = mockDatabase.students.find(s => s.nis === exc.nis);
                if (student && decision === 'Verified Invalid') {
                    student.totalAlpa += 1;
                }

                if (sendAndroidAlert) {
                    const noticeTitle = decision === 'Verified Valid' ? 'Absensi Terverifikasi (Izin)' : 'Peringatan Absensi (Membolos)';
                    const noticeText = decision === 'Verified Valid'
                        ? `Izin ketidakhadiran ${exc.studentName} pada mapel ${exc.subject} telah disetujui Guru Wali.`
                        : `PEMBERITAHUAN: ${exc.studentName} terverifikasi MEMBOLOS pada mata pelajaran ${exc.subject}. Poin kedisiplinan bertambah!`;

                    mockDatabase.announcements.unshift({
                        date: 'Baru Saja',
                        title: `[Android App Notice] ${noticeTitle}`,
                        desc: noticeText,
                        category: 'Kedisiplinan'
                    });
                }

                const modalEl = document.getElementById('verifyAttendanceModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                rebuildRoleComponents();
                triggerToast(`Absensi ${exc.studentName} berhasil diverifikasi sebagai: ${decision.replace('Verified ', '')}!`);
            }
        }

        // ==========================================
        // 6. GURU MATA PELAJARAN (GURU MAPEL) LOGIC
        // ==========================================
        function loadGuruMapelScheduleOptions() {
            const select = document.getElementById('gw-attn-schedule-select');
            if (select) {
                select.innerHTML = '<option value="">-- Pilih Rencana Jadwal Mengajar Anda --</option>';
                // Filter schedules assigned to Pak Danny
                const activeSchedules = mockDatabase.schedules.filter(s => s.teacher === 'Pak Danny');
                activeSchedules.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.innerText = `${s.day} • ${s.period} | Rombel: ${s.rombel.replace(/_/g, ' ')} (${s.subject})`;
                    select.appendChild(opt);
                });
            }
        }

        function renderGuruMapelSchedules() {
            const tbody = document.getElementById('guru-mapel-schedules-tbody');
            if (tbody) {
                tbody.innerHTML = '';
                const mySchedules = mockDatabase.schedules.filter(s => s.teacher === 'Pak Danny');

                mySchedules.forEach(s => {
                    const statusText = s.logged
                        ? '<span class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Jurnal Terisi</span>'
                        : '<span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20">Belum di-Jurnal</span>';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-bold">${s.period}</td>
                        <td>${s.rombel.replace(/_/g, ' ')}</td>
                        <td>${s.subject}</td>
                        <td><span class="badge bg-secondary">${s.room}</span></td>
                        <td class="text-center">${statusText}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function loadGWStudentListForAttendance() {
            const scheduleId = document.getElementById('gw-attn-schedule-select').value;
            const container = document.getElementById('gw-attn-input-container');
            const tbody = document.getElementById('gw-attn-input-tbody');

            if (!scheduleId) {
                container.classList.add('hidden-pane');
                return;
            }

            container.classList.remove('hidden-pane');
            tbody.innerHTML = '';

            const s = mockDatabase.schedules.find(sch => sch.id === scheduleId);
            if (s) {
                const dateKey = '2026-08-25';

                // Get or create subject attendance node
                if (!mockDatabase.subjectAttendance[dateKey]) {
                    mockDatabase.subjectAttendance[dateKey] = {};
                }
                if (!mockDatabase.subjectAttendance[dateKey][s.rombel]) {
                    mockDatabase.subjectAttendance[dateKey][s.rombel] = {};
                }
                if (!mockDatabase.subjectAttendance[dateKey][s.rombel][s.subject]) {
                    mockDatabase.subjectAttendance[dateKey][s.rombel][s.subject] = {
                        teacher: s.teacher,
                        logs: {}
                    };
                }

                const logs = mockDatabase.subjectAttendance[dateKey][s.rombel][s.subject].logs;

                mockDatabase.students.forEach(student => {
                    const currentStatus = logs[student.nis] || 'H';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="font-bold">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="gw-p-${student.nis}" id="gw-p-h-${student.nis}" value="H" ${currentStatus === 'H' ? 'checked' : ''}>
                                <label class="btn btn-outline-success px-2.5" for="gw-p-h-${student.nis}">H</label>

                                <input type="radio" class="btn-check" name="gw-p-${student.nis}" id="gw-p-s-${student.nis}" value="S" ${currentStatus === 'S' ? 'checked' : ''}>
                                <label class="btn btn-outline-warning px-2.5" for="gw-p-s-${student.nis}">S</label>

                                <input type="radio" class="btn-check" name="gw-p-${student.nis}" id="gw-p-i-${student.nis}" value="I" ${currentStatus === 'I' ? 'checked' : ''}>
                                <label class="btn btn-outline-info px-2.5" for="gw-p-i-${student.nis}">I</label>

                                <input type="radio" class="btn-check" name="gw-p-${student.nis}" id="gw-p-a-${student.nis}" value="A" ${currentStatus === 'A' ? 'checked' : ''}>
                                <label class="btn btn-outline-danger px-2.5" for="gw-p-a-${student.nis}">A</label>
                            </div>
                        </td>
                        <td>
                            <input type="text" id="gw-desc-${student.nis}" class="form-control form-control-sm" placeholder="Keterangan alpa/sakit...">
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function simulatePhotoUploadPreview() {
            document.getElementById('photo-preview-container').setAttribute('style', 'display: flex !important;');
            triggerToast("Foto aktivitas mengajar terunggah ke server!");
        }

        function submitSubjectKBMJournal() {
            const scheduleId = document.getElementById('gw-attn-schedule-select').value;
            const material = document.getElementById('gw-attn-material').value;

            if (!scheduleId || !material.trim()) {
                alert("Pilih jadwal dan isi materi pembelajaran!");
                return;
            }

            const s = mockDatabase.schedules.find(sch => sch.id === scheduleId);
            if (s) {
                const dateKey = '2026-08-25';

                // 1. Mark schedule as logged
                s.logged = true;

                // 2. Add to Teaching journals log list
                mockDatabase.journals.unshift({
                    id: 'j' + (mockDatabase.journals.length + 1),
                    date: dateKey,
                    scheduleId: s.id,
                    teacher: s.teacher,
                    material: material,
                    photo: 'Mengajar_Pemrograman.jpg'
                });

                // 3. Log student attendances
                mockDatabase.students.forEach(student => {
                    const checkedRadio = document.querySelector(`input[name="gw-p-${student.nis}"]:checked`);
                    if (checkedRadio) {
                        const status = checkedRadio.value;
                        mockDatabase.subjectAttendance[dateKey][s.rombel][s.subject].logs[student.nis] = status;

                        if (status === 'S' || status === 'I' || status === 'A') {
                            const noteInput = document.getElementById(`gw-desc-${student.nis}`).value || 'Mangkir / tanpa alasan.';

                            // Prevent duplicate exceptions
                            let exc = mockDatabase.attendanceExceptions.find(e => e.nis === student.nis && e.subject === s.subject);
                            if (!exc) {
                                mockDatabase.attendanceExceptions.unshift({
                                    id: 'exc' + (mockDatabase.attendanceExceptions.length + 1),
                                    nis: student.nis,
                                    studentName: student.name,
                                    class: student.class,
                                    subject: s.subject,
                                    date: dateKey,
                                    status: status,
                                    teacher: s.teacher,
                                    reason: noteInput,
                                    verificationStatus: 'Pending Review',
                                    verificationNotes: '',
                                    verifier: ''
                                });
                            } else {
                                exc.status = status;
                                exc.reason = noteInput;
                                exc.verificationStatus = 'Pending Review';
                            }
                        }
                    }
                });

                // 4. Log audit change
                const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const dateStr = new Date().toISOString().split('T')[0];
                mockDatabase.auditLogs.unshift({
                    timestamp: dateStr + ' ' + timeStr,
                    actor: `Pak Danny (Guru Mapel)`,
                    desc: `Menyimpan Jurnal & Presensi Rombel ${s.rombel.replace(/_/g, ' ')} [${s.subject}]`,
                    impact: 'Jurnal & Absensi Tersimpan'
                });

                // Reset forms
                document.getElementById('gw-attn-material').value = '';
                document.getElementById('photo-preview-container').setAttribute('style', 'display: none !important;');
                document.getElementById('gw-attn-photo-file').value = '';

                rebuildRoleComponents();
                triggerToast(`Jurnal KBM & Presensi kelas ${s.rombel.replace(/_/g, ' ')} berhasil diterbitkan!`);
            }
        }

        function renderGuruMapelJournals() {
            const tbody = document.getElementById('guru-mapel-journals-tbody');
            if (tbody) {
                tbody.innerHTML = '';
                // Filter journals by Pak Danny
                const myJournals = mockDatabase.journals.filter(j => j.teacher === 'Pak Danny');

                if (myJournals.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-400 small py-3">Belum ada riwayat pengisian jurnal mengajar.</td></tr>';
                } else {
                    myJournals.forEach(j => {
                        const s = mockDatabase.schedules.find(sch => sch.id === j.scheduleId) || {};
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="font-monospace small">${j.date}</td>
                            <td class="font-bold">${j.material}</td>
                            <td>${s.rombel ? s.rombel.replace(/_/g, ' ') : ''}</td>
                            <td>${s.period || ''}</td>
                            <td><span class="badge bg-secondary-subtle text-slate-400 py-1 px-2.5"><i class="fa-solid fa-image"></i> ${j.photo}</span></td>
                            <td><span class="badge bg-success badge-pill-custom">Terverifikasi</span></td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            }
        }

        // ==========================================
        // 5. GURU WALI (CLASS ADVISOR / GURU WALI) NOTES
        // ==========================================
        function openEscalationReferralModal(nis, name) {
            document.getElementById('escalate-student-nis').value = nis;
            document.getElementById('escalate-student-name').value = name;

            const modalEl = document.getElementById('escalateCaseModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function submitEscalationReferral() {
            const nis = document.getElementById('escalate-student-nis').value;
            const target = document.getElementById('escalate-target-select').value;
            const category = document.getElementById('escalate-category-select').value;
            const notes = document.getElementById('escalate-notes').value;

            if (!notes.trim()) {
                alert("Kronologi alasan rujukan harus diisi!");
                return;
            }

            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const dateStr = new Date().toISOString().split('T')[0];

                if (target === 'bk') {
                    const id = 'k' + (mockDatabase.counselingKanban.length + 1);
                    mockDatabase.counselingKanban.unshift({
                        id: id,
                        title: `${category}: ${notes}`,
                        student: student.name,
                        category: 'Rujukan Wali',
                        stage: 'antrean'
                    });

                    mockDatabase.auditLogs.unshift({
                        timestamp: dateStr + ' ' + timeStr,
                        actor: `${roleConfig[activeRole].roleLabel} (${roleConfig[activeRole].name.split(',')[0]})`,
                        desc: `Eskalasi rujukan ${student.name} ke BK: ${category}`,
                        impact: 'Kasus Masuk BK'
                    });

                    student.counselingNote = `[Rujukan ${roleConfig[activeRole].roleLabel}] ${notes}`;
                } else {
                    student.violations.unshift({
                        date: dateStr,
                        title: `[Rujukan ${roleConfig[activeRole].roleLabel}] ${category}: ${notes}`,
                        category: 'Eskalasi Kesiswaan',
                        points: 15
                    });
                    student.totalViolationPoints += 15;

                    mockDatabase.auditLogs.unshift({
                        timestamp: dateStr + ' ' + timeStr,
                        actor: `${roleConfig[activeRole].roleLabel} (${roleConfig[activeRole].name.split(',')[0]})`,
                        desc: `Rujukan kedisiplinan ${student.name} ke Kesiswaan: ${category}`,
                        impact: 'Pelanggaran +15 Poin'
                    });
                }

                const modalEl = document.getElementById('escalateCaseModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                document.getElementById('escalate-notes').value = '';

                rebuildRoleComponents();
                triggerToast(`Kasus ${student.name} berhasil dirujuk dan dieskalasi ke ${target.toUpperCase()}!`);
            }
        }

        function renderWaliCatatan() {
            const tbody = document.getElementById('wali-catatan-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';

            mockDatabase.students.forEach(student => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-bold text-start">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                    <td>
                        <textarea id="walinote-${student.nis}" rows="2" class="form-control form-control-sm text-slate-100" placeholder="Masukkan catatan pembinaan sikap dan kedisiplinan siswa...">${student.waliNote || ''}</textarea>
                    </td>
                    <td class="text-center">
                        <button onclick="openEscalationReferralModal(${student.nis}, '${student.name}')" class="btn btn-warning btn-xs py-1 px-2 rounded-2" style="font-size: 11px;" title="Rujuk Kasus"><i class="fa-solid fa-share-from-square"></i> Rujuk BK</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function saveWaliNotes() {
            mockDatabase.students.forEach(student => {
                const val = document.getElementById(`walinote-${student.nis}`).value;
                student.waliNote = val;
            });
            triggerToast("Catatan wali kelas untuk e-Rapor berhasil diperbarui!");
        }

        function switchTeacherChatConversation() {
            renderWaliChats();
        }

        function renderWaliChats() {
            const convoKey = document.getElementById('chat-parent-selector').value;

            const userList = document.getElementById('chat-users-list');
            userList.className = 'list-group list-group-flush';
            userList.innerHTML = '';

            const convoMeta = {
                'ortu_andi_wali': { name: 'Bpk. Budi Susanto', role: 'Ortu Andi', lastTime: '08:35', preview: 'Kami akan memantau belajar Andi...' },
                'ortu_dodi_wali': { name: 'Ibu Ningsih', role: 'Ortu Dodi', lastTime: '13:05', preview: 'Dodi bilangnya berangkat ke sekolah...' },
                'ortu_eko_wali': { name: 'Bpk. Joko', role: 'Ortu Eko', lastTime: '10:10', preview: 'Insya Allah saya akan meluangkan waktu...' }
            };

            Object.keys(convoMeta).forEach(key => {
                const isActive = (key === convoKey);
                const meta = convoMeta[key];

                const a = document.createElement('a');
                a.href = '#';
                a.className = `list-group-item list-group-item-action ${isActive ? 'active' : ''} p-2`;
                a.onclick = (e) => {
                    e.preventDefault();
                    document.getElementById('chat-parent-selector').value = key;
                    switchTeacherChatConversation();
                };

                a.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-bold small ${isActive ? 'text-white' : 'text-slate-100'}" style="font-size: 12px;">${meta.name}</span>
                        <span class="font-monospace opacity-75" style="font-size: 9px;">${meta.lastTime}</span>
                    </div>
                    <p class="small m-0 text-truncate opacity-75" style="font-size: 10px;">${meta.preview}</p>
                `;
                userList.appendChild(a);
            });

            document.getElementById('chat-header-name').innerText = convoMeta[convoKey].name;

            const container = document.getElementById('chat-messages-container');
            container.innerHTML = '';

            const conversation = mockDatabase.chats[convoKey] || [];
            conversation.forEach(c => {
                const bubble = document.createElement('div');
                bubble.className = `chat-bubble ${c.sender === 'wali' ? 'sent' : 'received'}`;
                bubble.innerHTML = `
                    <span>${c.text}</span>
                    <span class="block text-end opacity-50 mt-1 font-monospace" style="font-size: 9px;">${c.time}</span>
                `;
                container.appendChild(bubble);
            });
            container.scrollTop = container.scrollHeight;
        }

        function handleChatKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('chat-input-text');
            const val = input.value.trim();
            if (!val) return;

            const convoKey = document.getElementById('chat-parent-selector').value;

            mockDatabase.chats[convoKey].push({
                sender: 'wali',
                text: val,
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });

            input.value = '';
            renderWaliChats();

            setTimeout(() => {
                let parentReply = 'Terima kasih banyak atas penjelasannya Ibu Siti. Kami sangat menghargainya.';
                if (convoKey.includes('dodi')) {
                    parentReply = 'Baik Ibu, saya akan segera memeriksa tas Dodi dan memberitahukan perkembangan belajarnya.';
                } else if (convoKey.includes('eko')) {
                    parentReply = 'Baik Ibu, besok pagi jam 09:00 saya pastikan hadir di ruang Guru.';
                }

                mockDatabase.chats[convoKey].push({
                    sender: 'ortu',
                    text: parentReply,
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
                renderWaliChats();
                if (activeRole === 'ortu') {
                    renderOrtuChat();
                }
            }, 1500);
        }

        // ==========================================
        // GURU WALI NILAI LEGGER (WALI KELAS VIEW ON GURU WALI MENU)
        // ==========================================
        function filterAcademicGridGW() {
            renderGuruWaliLegger();
        }

        function renderGuruWaliLegger() {
            const tbody = document.getElementById('guru-wali-legger-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';

            const filterEl = document.getElementById('gw-filter-subject');
            const subject = filterEl ? filterEl.value : 'Jaringan';

            const headingEl = document.getElementById('gw-grid-heading');
            if (headingEl) {
                headingEl.innerHTML = `<i class="fa-solid fa-table-cells mr-1 text-primary"></i> Daftar Nilai Rombel: XI TKJ 1 | Mapel Binaan: ${subject}`;
            }

            mockDatabase.students.forEach(student => {
                const score = student.grades[subject] || 75;
                const isTuntas = score >= 75;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-bold text-start">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                    <td class="text-center font-monospace">${score - 3}</td>
                    <td class="text-center font-monospace">${score - 1}</td>
                    <td class="text-center font-monospace">${score + 1}</td>
                    <td class="text-center font-bold text-primary font-monospace">
                        <input type="number" id="gw-grade-${student.nis}" class="form-control form-control-sm text-center font-monospace" style="width: 70px; margin: 0 auto;" value="${score}" onchange="updateStudentGradeInDB(${student.nis}, '${subject}', this.value)">
                    </td>
                    <td>
                        <span class="badge ${isTuntas ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'} badge-pill-custom">
                            ${isTuntas ? 'Tuntas' : 'Remedial'}
                        </span>
                    </td>
                    <td>
                        <textarea id="gw-remark-${student.nis}" rows="1" class="form-control form-control-sm text-slate-400" style="font-size: 12px; min-width: 150px;" readonly placeholder="Klik 'Ubah Catatan'..."> ${student.academicNote || ''}</textarea>
                    </td>
                    <td class="text-center">
                        <div class="flex gap-1 justify-center">
                            <button onclick="openGuruWaliRemarkModal(${student.nis}, '${student.name}')" class="btn btn-brand-primary btn-xs py-1 px-2 rounded-2" title="Kelola Catatan"><i class="fa-solid fa-edit"></i></button>
                            <button onclick="openEscalationReferralModal(${student.nis}, '${student.name}')" class="btn btn-warning btn-xs py-1 px-2 rounded-2" title="Eskalasi Rujukan"><i class="fa-solid fa-triangle-exclamation"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            renderAuditLogs();
        }

        function updateStudentGradeInDB(nis, subject, scoreVal) {
            const score = parseInt(scoreVal);
            if (isNaN(score)) return;
            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                student.grades[subject] = score;
                triggerToast(`Nilai ${subject} untuk ${student.name} berhasil diperbarui ke ${score}!`);
                renderGuruWaliDashboard();
                renderGuruWaliLegger();
                renderGuruWaliRapor();
            }
        }

        function renderGuruWaliDashboard() {
            const nameEl = document.getElementById('gw-dashboard-name');
            if (nameEl) nameEl.innerText = roleConfig['guru_wali'].name;

            // Calculate rombel average
            let totalScore = 0;
            let totalStudents = mockDatabase.students.length;
            mockDatabase.students.forEach(s => {
                totalScore += (s.grades.Math + s.grades.Indo + s.grades.Jaringan + s.grades.Pemrograman) / 4;
            });
            const rombelAvg = (totalScore / totalStudents).toFixed(1);
            const avgEl = document.getElementById('gw-dashboard-average-grade');
            if (avgEl) avgEl.innerText = rombelAvg;

            // Render top 3 academic
            const container = document.getElementById('gw-top-students');
            if (container) {
                container.innerHTML = '';
                const sorted = [...mockDatabase.students].sort((a, b) => {
                    const avgA = (a.grades.Math + a.grades.Indo + a.grades.Jaringan + a.grades.Pemrograman) / 4;
                    const avgB = (b.grades.Math + b.grades.Indo + b.grades.Jaringan + b.grades.Pemrograman) / 4;
                    return avgB - avgA;
                });

                sorted.slice(0, 3).forEach((s, idx) => {
                    const avg = ((s.grades.Math + s.grades.Indo + s.grades.Jaringan + s.grades.Pemrograman) / 4).toFixed(1);
                    const colors = ['bg-warning text-slate-100', 'bg-secondary text-white', 'bg-danger text-white'];
                    const item = document.createElement('div');
                    item.className = 'flex justify-between items-center border-b border-slate-700/60 pb-2';
                    item.innerHTML = `
                        <div class="d-flex items-center gap-2">
                            <span class="badge ${colors[idx]} rounded-circle d-flex items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">${idx + 1}</span>
                            <span class="font-bold small" style="font-size: 13px;">${s.name}</span>
                        </div>
                        <span class="text-primary font-monospace font-bold">${avg}</span>
                    `;
                    container.appendChild(item);
                });
            }

            // Render remedial list
            const remedialList = document.getElementById('gw-remedial-list');
            if (remedialList) {
                remedialList.innerHTML = '';
                const remedialStudents = mockDatabase.students.filter(s => {
                    return (s.grades.Math < 75 || s.grades.Indo < 75 || s.grades.Jaringan < 75 || s.grades.Pemrograman < 75);
                });

                if (remedialStudents.length === 0) {
                    remedialList.innerHTML = '<li class="list-group-item text-slate-400 small italic text-center py-3">Semua siswa tuntas KKM.</li>';
                } else {
                    remedialStudents.forEach(s => {
                        let subjectsBelow = [];
                        if (s.grades.Math < 75) subjectsBelow.push('MTK');
                        if (s.grades.Indo < 75) subjectsBelow.push('B.Indo');
                        if (s.grades.Jaringan < 75) subjectsBelow.push('Jaringan');
                        if (s.grades.Pemrograman < 75) subjectsBelow.push('Prog');

                        const li = document.createElement('li');
                        li.className = 'list-group-item flex justify-between items-center px-0 py-2';
                        li.innerHTML = `
                            <div>
                                <span class="font-bold block small" style="font-size: 13px;">${s.name}</span>
                                <span class="text-slate-400" style="font-size: 10px;">Gagal KKM: ${subjectsBelow.join(', ')}</span>
                            </div>
                            <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 font-semibold" style="font-size: 10px;">Remedial</span>
                        `;
                        remedialList.appendChild(li);
                    });
                }
            }
        }

        function renderGuruWaliRapor() {
            const tbody = document.getElementById('guru-wali-rapor-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';

            mockDatabase.students.forEach(student => {
                let remedialCount = 0;
                if (student.grades.Math < 75) remedialCount++;
                if (student.grades.Indo < 75) remedialCount++;
                if (student.grades.Jaringan < 75) remedialCount++;
                if (student.grades.Pemrograman < 75) remedialCount++;

                const average = ((student.grades.Math + student.grades.Indo + student.grades.Jaringan + student.grades.Pemrograman) / 4).toFixed(1);

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-bold text-start">${student.name} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: ${student.nis}</span></td>
                    <td class="font-monospace font-bold">${average}</td>
                    <td class="text-center">
                        <span class="badge ${remedialCount === 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'} badge-pill-custom">
                            ${remedialCount} Mapel Belum Tuntas
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${remedialCount === 0 ? 'bg-success text-white' : 'bg-danger text-white'} badge-pill-custom">
                            ${remedialCount === 0 ? 'Siap Terbit' : 'Ditangguhkan'}
                        </span>
                    </td>
                    <td>
                        <span class="text-slate-400 small italic text-truncate d-inline-block" style="max-width: 180px;">${student.academicNote || 'Belum ada catatan.'}</span>
                    </td>
                    <td class="text-center">
                        <button onclick="downloadReport('${student.name}')" class="btn btn-outline-primary btn-xs py-1 px-2 text-xs" ${remedialCount === 0 ? '' : 'disabled'}><i class="fa-solid fa-file-pdf"></i> Unduh e-Rapor</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openGuruWaliRemarkModal(nis, name) {
            document.getElementById('gw-modal-student-nis').value = nis;
            document.getElementById('gw-modal-student-name').value = name;

            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                document.getElementById('gw-modal-note').value = student.academicNote || '';
                document.getElementById('gw-modal-remedial').checked = (student.grades.Jaringan < 75);
            }

            const modalEl = document.getElementById('guruWaliRemarkModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function submitGuruWaliRemarkModal() {
            const nis = document.getElementById('gw-modal-student-nis').value;
            const notes = document.getElementById('gw-modal-note').value;
            const remedial = document.getElementById('gw-modal-remedial').checked;

            const student = mockDatabase.students.find(s => s.nis == nis);
            if (student) {
                student.academicNote = notes;

                const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const dateStr = new Date().toISOString().split('T')[0];
                mockDatabase.auditLogs.unshift({
                    timestamp: dateStr + ' ' + timeStr,
                    actor: 'Bpk. Hendra (Guru Wali)',
                    desc: `Menyimpan Catatan Rapor ${student.name}`,
                    impact: remedial ? 'Ditandai Remedial' : 'Catatan Diperbarui'
                });

                const modalEl = document.getElementById('guruWaliRemarkModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                rebuildRoleComponents();
                triggerToast(`Catatan akademik untuk ${student.name} berhasil disimpan!`);
            }
        }

        function saveGuruWaliRemarks() {
            const subject = 'Jaringan';

            mockDatabase.students.forEach(student => {
                const scoreInput = document.getElementById(`gw-grade-${student.nis}`);
                const oldGrade = student.grades[subject];
                const newGrade = parseInt(scoreInput.value) || 0;

                student.grades[subject] = newGrade;

                if (oldGrade !== newGrade) {
                    const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    const dateStr = new Date().toISOString().split('T')[0];
                    mockDatabase.auditLogs.unshift({
                        timestamp: dateStr + ' ' + timeStr,
                        actor: 'Bpk. Hendra (Guru Wali)',
                        desc: `Mengubah nilai ${subject} ${student.name}: ${oldGrade} -> ${newGrade}`,
                        impact: `Ketuntasan: ${newGrade >= 75 ? 'TUNTAS' : 'REMEDIAL'}`
                    });
                }
            });
            rebuildRoleComponents();
            triggerToast("Nilai & Catatan kemajuan akademik mapel Jaringan berhasil disimpan!");
        }

        // ==========================================
        // 7. SISWA PORTAL (MOBILE VIEWS)
        // ==========================================
        let activeSiswaTab = 'nilai';

        function renderSiswaPortal() {
            const student = mockDatabase.students[0];
            document.getElementById('siswa-portal-name').innerText = student.name;

            const attendanceRate = (((30 - student.totalAlpa) / 30) * 100).toFixed(1);
            document.getElementById('siswa-portal-attendance-rate').innerHTML = `<i class="fa-solid fa-circle-check"></i> ${attendanceRate}% Kehadiran`;

            const gradesList = document.getElementById('siswa-grades-list');
            gradesList.innerHTML = '';

            const subjects = [
                { key: 'Math', name: 'Matematika Terapan', val: student.grades.Math },
                { key: 'Indo', name: 'Bahasa Indonesia Kejuruan', val: student.grades.Indo },
                { key: 'Jaringan', name: 'Administrasi Infrastruktur Jaringan', val: student.grades.Jaringan },
                { key: 'Pemrograman', name: 'Pemrograman Web & Mobile', val: student.grades.Pemrograman }
            ];

            subjects.forEach(sub => {
                const item = document.createElement('div');
                item.className = 'border-b border-slate-700/60 pb-2';
                item.innerHTML = `
                    <div class="flex justify-between small font-semibold mb-1 text-slate-100">
                        <span>${sub.name}</span>
                        <span class="text-primary font-monospace">${sub.val}</span>
                    </div>
                    <div class="progress-bar-custom" style="height: 6px;">
                        <div class="progress-bar-inner ${sub.val >= 75 ? 'bg-success' : 'bg-danger'}" style="width: ${sub.val}%"></div>
                    </div>
                `;
                gradesList.appendChild(item);
            });

            document.getElementById('siswa-portal-violation-points').innerText = `${student.totalViolationPoints} Poin`;
            const vList = document.getElementById('siswa-violation-list');
            vList.innerHTML = '';

            if (student.violations.length === 0) {
                vList.innerHTML = '<span class="italic text-slate-400">Bersih dari pelanggaran.</span>';
            } else {
                student.violations.forEach(v => {
                    const entry = document.createElement('div');
                    entry.className = 'flex justify-between border-b border-slate-700/60 border-light py-1 text-slate-700';
                    entry.innerHTML = `<span>${v.title}</span><span class="text-danger font-bold">+${v.points}</span>`;
                    vList.appendChild(entry);
                });
            }

            document.getElementById('siswa-portal-wali-note').innerText = `"${student.waliNote || 'Belum ada catatan pembinaan...'}"`;
            const acNoteEl = document.getElementById('siswa-portal-academic-note');
            if (acNoteEl) acNoteEl.innerText = `"${student.academicNote || 'Belum ada catatan akademik...'}"`;
        }

        function switchSiswaTab(tab) {
            activeSiswaTab = tab;

            document.getElementById('siswa-t-nilai').classList.remove('active');
            document.getElementById('siswa-t-jadwal').classList.remove('active');
            document.getElementById('siswa-t-catatan').classList.remove('active');

            document.getElementById('siswa-tab-nilai-pane').classList.add('hidden-pane');
            document.getElementById('siswa-tab-jadwal-pane').classList.add('hidden-pane');
            document.getElementById('siswa-tab-catatan-pane').classList.add('hidden-pane');

            document.getElementById(`siswa-t-${tab}`).classList.add('active');
            document.getElementById(`siswa-tab-${tab}-pane`).classList.remove('hidden-pane');
        }

        // ==========================================
        // 8. ORANG TUA PORTAL (MOBILE VIEWS)
        // ==========================================
        let activeOrtuTab = 'akademik';

        function renderOrtuPortal() {
            const student = mockDatabase.students[0];

            const statusLabel = document.getElementById('ortu-child-attendance-status');
            if (student.attendanceToday === 'H') {
                statusLabel.innerHTML = '<i class="fa-solid fa-circle-check"></i> Hadir Tepat Waktu (06:45)';
                statusLabel.className = 'font-bold text-success m-0';
            } else if (student.attendanceToday === 'S') {
                statusLabel.innerHTML = '<i class="fa-solid fa-circle-info"></i> Izin Sakit (Surat Terlampir)';
                statusLabel.className = 'font-bold text-warning m-0';
            } else if (student.attendanceToday === 'I') {
                statusLabel.innerHTML = '<i class="fa-solid fa-circle-info"></i> Izin Keperluan Keluarga';
                statusLabel.className = 'font-bold text-info m-0';
            } else {
                statusLabel.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Tanpa Alasan / Membolos';
                statusLabel.className = 'font-bold text-danger m-0';
            }

            const notificationContainer = document.getElementById('ortu-attendance-notifications-container');
            notificationContainer.innerHTML = '';

            const studentExceptions = mockDatabase.attendanceExceptions.filter(e => e.nis === student.nis);
            studentExceptions.forEach(exc => {
                const div = document.createElement('div');
                if (exc.verificationStatus === 'Pending Review') {
                    div.className = 'alert alert-warning py-2 mb-3 small d-flex items-center gap-2 shadow-sm border-l border-slate-700/60 border-amber-500 border-4';
                    div.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i> <div><strong>Review Absensi:</strong> ${student.name} ditandai <strong>Alpa</strong> pada mapel <strong>${exc.subject}</strong>. Menunggu verifikasi berkas izin Anda.</div>`;
                } else if (exc.verificationStatus === 'Verified Invalid') {
                    div.className = 'alert alert-danger py-2 mb-3 small d-flex items-center gap-2 shadow-sm border-l border-slate-700/60 border-rose-500 border-4';
                    div.innerHTML = `<i class="fa-solid fa-circle-exclamation text-danger fs-5"></i> <div><strong>Pelanggaran Membolos:</strong> ${student.name} dikonfirmasi <strong>BOLOS</strong> pada mapel <strong>${exc.subject}</strong>. Poin disiplin bertambah!</div>`;
                } else {
                    div.className = 'alert alert-success py-2 mb-3 small d-flex items-center gap-2 shadow-sm border-l border-slate-700/60 border-emerald-500 border-4';
                    div.innerHTML = `<i class="fa-solid fa-circle-check text-success fs-5"></i> <div><strong>Izin Disetujui:</strong> Dispensasi ketidakhadiran pada mapel <strong>${exc.subject}</strong> telah disahkah oleh Guru Wali.</div>`;
                }
                notificationContainer.appendChild(div);
            });

            const gradesList = document.getElementById('ortu-grades-list');
            gradesList.innerHTML = '';

            const subjects = [
                { name: 'Matematika Terapan', val: student.grades.Math },
                { name: 'Bahasa Indonesia Kejuruan', val: student.grades.Indo },
                { name: 'Administrasi Infrastruktur Jaringan', val: student.grades.Jaringan },
                { name: 'Pemrograman Web & Mobile', val: student.grades.Pemrograman }
            ];

            subjects.forEach(sub => {
                const item = document.createElement('div');
                item.className = 'border-b border-slate-700/60 pb-2';
                item.innerHTML = `
                    <div class="flex justify-between small font-semibold mb-1">
                        <span>${sub.name}</span>
                        <span class="${sub.val >= 75 ? 'text-success' : 'text-danger'} font-monospace font-bold">${sub.val}</span>
                    </div>
                    <div class="flex justify-between text-slate-400" style="font-size: 11px;">
                        <span>Kriteria Ketuntasan (KKM): 75</span>
                        <span class="font-bold">${sub.val >= 75 ? 'Tuntas' : 'Remedial'}</span>
                    </div>
                `;
                gradesList.appendChild(item);
            });

            document.getElementById('ortu-violation-points').innerText = `${student.totalViolationPoints} Poin`;
            const vList = document.getElementById('ortu-violation-list');
            vList.innerHTML = '';

            if (student.violations.length === 0) {
                vList.innerHTML = '<span class="italic text-slate-400">Belum ada catatan pelanggaran tata tertib.</span>';
            } else {
                student.violations.forEach(v => {
                    const entry = document.createElement('div');
                    entry.className = 'border-b border-slate-700/60 border-light py-2';
                    entry.innerHTML = `
                        <div class="flex justify-between font-bold text-slate-100" style="font-size: 13px;">
                            <span>${v.title}</span>
                            <span class="text-danger">+${v.points}</span>
                        </div>
                        <span class="text-slate-400 font-monospace block" style="font-size: 10px;">${v.date} • Kategori: ${v.category}</span>
                    `;
                    vList.appendChild(entry);
                });
            }

            const waliNoteEl = document.getElementById('ortu-portal-wali-note');
            if (waliNoteEl) waliNoteEl.innerText = `"${student.waliNote || 'Belum ada catatan pembinaan...'}"`;

            const acNoteEl = document.getElementById('ortu-portal-academic-note');
            if (acNoteEl) acNoteEl.innerText = `"${student.academicNote || 'Belum ada catatan akademik...'}"`;

            renderOrtuAnnouncements();
            renderOrtuChat();
        }

        function renderOrtuAnnouncements() {
            const filterVal = document.getElementById('ortu-announcement-filter').value;
            const aList = document.getElementById('ortu-announcements-list');
            aList.innerHTML = '';

            mockDatabase.announcements.forEach(a => {
                if (filterVal === 'all' || a.category === filterVal) {
                    const item = document.createElement('div');
                    item.className = 'list-group-item px-0 py-2 border-b border-slate-700/60';

                    let badgeColor = 'bg-secondary';
                    if (a.category === 'Akademik') badgeColor = 'bg-success';
                    else if (a.category === 'Kedisiplinan') badgeColor = 'bg-danger';
                    else if (a.category === 'Event') badgeColor = 'bg-indigo';

                    item.innerHTML = `
                        <div class="flex justify-between items-center">
                            <span class="badge ${badgeColor} badge-pill-custom text-white" style="font-size: 9px;">${a.category}</span>
                            <span class="text-slate-400" style="font-size: 10px;">${a.date}</span>
                        </div>
                        <h6 class="font-bold m-0 mt-1" style="font-size: 13px;">${a.title}</h6>
                        <p class="text-slate-400 small m-0 mt-1 leading-relaxed" style="font-size: 12px;">${a.desc}</p>
                    `;
                    aList.appendChild(item);
                }
            });
        }

        function switchParentChatConversation() {
            renderOrtuChat();
        }

        function renderOrtuChat() {
            const convoKey = document.getElementById('chat-channel-selector').value;

            const chatTargetName = document.getElementById('ortu-chat-target-name');
            if (convoKey === 'ortu_andi_wali') {
                chatTargetName.innerText = 'Ibu Siti (Wali Kelas)';
            } else {
                chatTargetName.innerText = 'Ibu Mayang (Guru BK)';
            }

            const container = document.getElementById('ortu-chat-messages-container');
            container.innerHTML = '';

            const conversation = mockDatabase.chats[convoKey] || [];
            conversation.forEach(c => {
                const bubble = document.createElement('div');
                bubble.className = `chat-bubble ${c.sender === 'ortu' ? 'sent' : 'received'}`;
                bubble.innerHTML = `
                    <span>${c.text}</span>
                    <span class="block text-end opacity-50 mt-1 font-monospace" style="font-size: 9px;">${c.time}</span>
                `;
                container.appendChild(bubble);
            });
            container.scrollTop = container.scrollHeight;
        }

        function handleOrtuChatKeyPress(e) {
            if (e.key === 'Enter') {
                sendOrtuMessage();
            }
        }

        function sendOrtuMessage() {
            const input = document.getElementById('ortu-chat-input-text');
            const val = input.value.trim();
            if (!val) return;

            const convoKey = document.getElementById('chat-channel-selector').value;

            mockDatabase.chats[convoKey].push({
                sender: 'ortu',
                text: val,
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });

            input.value = '';
            renderOrtuChat();

            setTimeout(() => {
                let replyText = 'Terima kasih Bapak Budi. Kami akan jadwalkan agenda mediasi jika diperlukan. Segera kami kabari kembali.';
                let senderKey = 'wali';
                if (convoKey === 'ortu_andi_bk') {
                    replyText = 'Halo Pak Budi, kami dari bimbingan konseling selalu memantau motivasi Andi. Sesi andi sejauh ini sangat baik.';
                    senderKey = 'bk';
                }

                mockDatabase.chats[convoKey].push({
                    sender: senderKey,
                    text: replyText,
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
                renderOrtuChat();
                if (activeRole === 'wali_kelas') {
                    renderWaliChats();
                }
            }, 1500);
        }

        function switchOrtuTab(tab) {
            activeOrtuTab = tab;
            document.getElementById('ortu-t-akademik').classList.remove('active');
            document.getElementById('ortu-t-kesiswaan').classList.remove('active');
            document.getElementById('ortu-t-chat').classList.remove('active');

            document.getElementById('ortu-tab-akademik-pane').classList.add('hidden-pane');
            document.getElementById('ortu-tab-kesiswaan-pane').classList.add('hidden-pane');
            document.getElementById('ortu-tab-chat-pane').classList.add('hidden-pane');

            document.getElementById(`ortu-t-${tab}`).classList.add('active');
            document.getElementById(`ortu-tab-${tab}-pane`).classList.remove('hidden-pane');
        }

        function switchMobileNav(tabKey) {
            document.getElementById('mn-home').classList.remove('active');
            document.getElementById('mn-acad').classList.remove('active');
            document.getElementById('mn-dis').classList.remove('active');

            if (tabKey === 'beranda') {
                document.getElementById('mn-home').classList.add('active');
                if (activeRole === 'siswa') {
                    switchSiswaTab('jadwal');
                } else {
                    switchOrtuTab('kesiswaan');
                }
            } else if (tabKey === 'akademik') {
                document.getElementById('mn-acad').classList.add('active');
                if (activeRole === 'siswa') {
                    switchSiswaTab('nilai');
                } else {
                    switchOrtuTab('akademik');
                }
            } else {
                document.getElementById('mn-dis').classList.add('active');
                if (activeRole === 'siswa') {
                    switchSiswaTab('catatan');
                } else {
                    switchOrtuTab('chat');
                }
            }
        }

        // ==========================================
        // BROADCASTING LOGIC FOR WAKAS & KEPSEK
        // ==========================================
        function broadcastAnnouncementGeneric(title, cat, desc) {
            if (!title.trim() || !desc.trim()) {
                alert("Semua data isian pengumuman harus diisi!");
                return;
            }

            const newAnn = {
                date: 'Hari Ini',
                title: title,
                desc: desc,
                category: cat
            };

            mockDatabase.announcements.unshift(newAnn);
            triggerToast(`Pengumuman "${title}" kategori [${cat}] berhasil disiarkan!`);
        }

        function broadcastAnnouncement() {
            const title = document.getElementById('bc-title').value;
            const cat = document.getElementById('bc-category').value;
            const desc = document.getElementById('bc-desc').value;

            broadcastAnnouncementGeneric(title, cat, desc);

            document.getElementById('bc-title').value = '';
            document.getElementById('bc-desc').value = '';
        }

        function broadcastAnnouncementK() {
            const title = document.getElementById('bc-title-k').value;
            const desc = document.getElementById('bc-desc-k').value;

            broadcastAnnouncementGeneric(title, 'Akademik', desc);

            document.getElementById('bc-title-k').value = '';
            document.getElementById('bc-desc-k').value = '';
        }

        function broadcastAnnouncementS() {
            const title = document.getElementById('bc-title-s').value;
            const desc = document.getElementById('bc-desc-s').value;

            broadcastAnnouncementGeneric(title, 'Kedisiplinan', desc);

            document.getElementById('bc-title-s').value = '';
            document.getElementById('bc-desc-s').value = '';
        }

        // ==========================================
        // GENERAL HELPERS & INJECTIONS
        // ==========================================
        function populateStudentDropdowns() {
            const list = [
                document.getElementById('violation-student-select'),
                document.getElementById('asesmen-student-select')
            ];

            list.forEach(select => {
                if (select) {
                    select.innerHTML = '';
                    mockDatabase.students.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.nis;
                        opt.innerText = `${s.name} (${s.class})`;
                        select.appendChild(opt);
                    });
                }
            });
        }

        function updateNotificationDropdown() {
            const list = document.getElementById('notif-dropdown');
            list.innerHTML = `
                <li><h6 class="dropdown-header text-slate-100 font-bold pb-2">Notifikasi Terbaru</h6></li>
                <li>
                    <a class="dropdown-item py-2 border-b border-slate-700/60 text-wrap" href="#">
                        <div class="font-bold text-slate-100" style="font-size: 12px;">Ketidakhadiran Terdeteksi</div>
                        <span class="text-slate-400 text-xs">Eko Saputro (XI TKJ 1) ditandai Alpa oleh Wali Kelas hari ini.</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-wrap" href="#">
                        <div class="font-bold text-slate-100" style="font-size: 12px;">Sesi Konseling Ditambahkan</div>
                        <span class="text-slate-400 text-xs">BK mendaftarkan antrean kasus "cyberbullying" untuk Dodi.</span>
                    </a>
                </li>
            `;
        }

        function downloadReport(title) {
            triggerToast(`Mempersiapkan dokumen Laporan ${title}...`);
            setTimeout(() => {
                triggerToast(`Laporan ${title} Semester Ganjil berhasil diunduh!`);
            }, 1200);
        }

        function triggerToast(message) {
            const toastEl = document.getElementById('actionToast');
            document.getElementById('toast-message-content').innerText = message;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>
</body>

</html>
