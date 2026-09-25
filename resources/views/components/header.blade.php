@php
    $user = Auth::user();
    $initials = $user ? strtoupper(substr($user->nama, 0, 2)) : 'U';
@endphp
<header
    class="app-header navbar navbar-expand-lg navbar-dark bg-slate-900 text-slate-100 px-3 flex-shrink-0 border-b border-slate-700/60 border-slate-700/60"
    style="height: 64px;">
    <div class="container-fluid p-0 d-flex items-center justify-between">

        <!-- Toggle Sidebar Menu -->
        <div class="d-flex items-center gap-2">
            <button onclick="toggleSidebar()"
                class="btn btn-outline-secondary btn-sm text-slate-100 md:hidden"
                id="sidebar-toggler">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <span class="navbar-brand-custom text-info hidden sm:inline-block" id="top-title-brand">SMKN 9 Malang</span>
            <span class="navbar-brand-custom text-info sm:hidden" id="top-title-brand">SMKN 9</span>
        </div>

        <!-- User Info & Notifications -->
        <div class="d-flex items-center gap-3">
            <!-- Theme Toggle -->
            <button @click="darkMode = !darkMode" class="btn btn-sm rounded-circle p-2 text-slate-400 hover:text-slate-100 transition-colors border-0" style="background: transparent;">
                <i class="fa-solid fa-sun fs-5" x-show="darkMode" style="display: none;"></i>
                <i class="fa-solid fa-moon fs-5" x-show="!darkMode"></i>
            </button>

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
                    <div class="small font-bold text-slate-100 text-truncate" style="max-width: 130px;"
                        id="header-user-fullname">{{ $user->nama ?? 'User' }}</div>
                    <div class="text-slate-400" style="font-size: 10px;">T.A 2026/2027 Ganjil</div>
                </div>
                <div class="avatar-circle font-bold uppercase"
                    style="width: 34px; height: 34px; font-size: 13px;" id="header-avatar-mini">
                    {{ $initials }}
                </div>
            </div>
        </div>
    </div>
</header>
