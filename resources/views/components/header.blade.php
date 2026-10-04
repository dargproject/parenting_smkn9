@php
    $user = Auth::user();
    $initials = $user ? strtoupper(substr($user->nama, 0, 2)) : 'U';
    $tahunAjaranAktif = \App\Models\TahunAjaran::where('is_active', true)->first();
    $notifikasi = \App\Models\Pengumuman::latest()->take(5)->get();
    $notifTerbaru = $notifikasi->first()?->created_at?->timestamp ?? 0;
    $bukaProfil = $errors->hasBag('profil') || $errors->hasBag('password');
@endphp
<header
    class="app-header navbar navbar-expand-lg navbar-dark bg-slate-900 text-slate-100 px-3 flex-shrink-0 border-b border-slate-700/60 border-slate-700/60"
    style="height: 64px;">
    <div class="container-fluid p-0 d-flex items-center justify-between">

        <!-- Toggle Sidebar Menu -->
        <div class="d-flex items-center gap-2">
            <button onclick="toggleSidebar()"
                class="btn btn-outline-secondary btn-sm text-slate-100"
                id="sidebar-toggler" title="Tampilkan / sembunyikan menu">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <span class="navbar-brand-custom text-info hidden sm:inline-block">{{ setting('nama_sekolah', 'SMKN 9 Malang') }}</span>
            <span class="navbar-brand-custom text-info sm:hidden">{{ setting('nama_sekolah', 'SMKN 9') }}</span>
        </div>

        <!-- User Info & Notifications -->
        <div class="d-flex items-center gap-3">
            <!-- Theme Toggle -->
            <button @click="darkMode = !darkMode" class="btn btn-sm rounded-circle p-2 text-slate-400 hover:text-slate-100 transition-colors border-0" style="background: transparent;">
                <i class="fa-solid fa-sun fs-5" x-show="darkMode" style="display: none;"></i>
                <i class="fa-solid fa-moon fs-5" x-show="!darkMode"></i>
            </button>

            <!-- Notifications -->
            <div class="relative" x-data="{ open: false, terbaru: {{ $notifTerbaru }}, dilihat: 0,
                init() { try { this.dilihat = Number(localStorage.getItem('notifDilihat') || 0) } catch (e) {} },
                toggle() { this.open = !this.open; if (this.open) { this.dilihat = this.terbaru; try { localStorage.setItem('notifDilihat', this.terbaru) } catch (e) {} } } }"
                @click.outside="open = false">
                <button type="button" @click="toggle()" class="btn btn-dark btn-sm rounded-circle position-relative p-2" title="Notifikasi">
                    <i class="fa-regular fa-bell fs-6"></i>
                    <span x-show="terbaru > dilihat" style="display: none;"
                        class="position-absolute top-0 start-100 translate-middle p-1.5 bg-danger border border-light rounded-circle" id="notif-badge"></span>
                </button>
                <div x-show="open" x-transition style="display: none; width: 300px; font-size: 13px;"
                    class="solid-panel absolute right-0 mt-2 z-50 rounded-xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-lg p-2" id="notif-dropdown">
                    <div class="px-2 py-1 font-bold text-slate-100">Notifikasi Terbaru</div>
                    @forelse($notifikasi as $n)
                        <div class="px-2 py-2 border-t border-slate-700/60">
                            <div class="font-semibold text-slate-100">{{ $n->judul }}</div>
                            <div class="text-slate-400 text-xs">{{ \Illuminate\Support\Str::limit($n->deskripsi, 80) }}</div>
                            <div class="text-slate-400" style="font-size: 10px;">{{ $n->kategori }} &middot; {{ $n->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="px-2 py-3 text-slate-400 text-center">Belum ada notifikasi.</div>
                    @endforelse
                </div>
            </div>

            <!-- Profile Menu -->
            <div class="relative" x-data="{ menu: false, modal: {{ $bukaProfil ? 'true' : 'false' }}, tab: '{{ $errors->hasBag('password') ? 'password' : 'profil' }}' }" @click.outside="menu = false">
                <button type="button" @click="menu = !menu" class="d-flex items-center gap-2 border-0 bg-transparent p-0 text-left">
                    <div class="text-end d-none d-sm-block">
                        <div class="small font-bold text-slate-100 text-truncate" style="max-width: 130px;" id="header-user-fullname">{{ $user->nama ?? 'User' }}</div>
                        <div class="text-slate-400" style="font-size: 10px;">{{ $tahunAjaranAktif ? 'T.A '.$tahunAjaranAktif->nama : 'Belum ada T.A aktif' }}</div>
                    </div>
                    <div class="avatar-circle font-bold uppercase" style="width: 34px; height: 34px; font-size: 13px;" id="header-avatar-mini">{{ $initials }}</div>
                </button>

                <div x-show="menu" x-transition style="display: none; width: 210px; font-size: 13px;"
                    class="solid-panel absolute right-0 mt-2 z-50 rounded-xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-lg p-1">
                    <button type="button" @click="modal = true; menu = false" class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-left text-slate-100 hover:bg-slate-700/30">
                        <i class="fa-regular fa-id-badge w-4 text-center"></i> Profil Akun
                    </button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-left text-slate-100 hover:bg-slate-700/30">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Keluar
                        </button>
                    </form>
                </div>

                <!-- Modal Profil Akun -->
                <div x-show="modal" style="display: none;" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="modal = false">
                    <div class="solid-panel w-full max-w-md rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl p-5 max-h-[90vh] overflow-y-auto" @click.outside="modal = false">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="font-bold text-slate-100 m-0">Profil Akun</h5>
                            <button type="button" @click="modal = false" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="flex gap-1 mb-4 border-b border-slate-700/60">
                            <button type="button" @click="tab = 'profil'" :class="tab === 'profil' ? 'border-b-2 border-blue-600 text-slate-100' : 'text-slate-400'" class="px-3 py-2 text-sm font-semibold">Data Akun</button>
                            <button type="button" @click="tab = 'password'" :class="tab === 'password' ? 'border-b-2 border-blue-600 text-slate-100' : 'text-slate-400'" class="px-3 py-2 text-sm font-semibold">Ganti Password</button>
                        </div>

                        <form x-show="tab === 'profil'" method="POST" action="{{ route('profil.update') }}" class="space-y-3">
                            @csrf @method('PUT')
                            @foreach([['nama', 'Nama Lengkap', 'text'], ['nip', 'NIP / Username Login', 'text'], ['email', 'Email', 'email'], ['phone', 'No. HP', 'text']] as [$f, $label, $type])
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-400">{{ $label }}</label>
                                    <input type="{{ $type }}" name="{{ $f }}" value="{{ old($f, $user->$f) }}" @if(in_array($f, ['nama', 'nip'])) required @endif class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                                    @error($f, 'profil')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                            <p class="text-xs text-slate-400">NIP dipakai untuk login. Setelah diganti, gunakan NIP baru saat masuk berikutnya.</p>
                            <div class="flex justify-end"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Profil</button></div>
                        </form>

                        <form x-show="tab === 'password'" style="display: none;" method="POST" action="{{ route('profil.password') }}" class="space-y-3">
                            @csrf @method('PUT')
                            @foreach([['password_lama', 'Password Saat Ini'], ['password', 'Password Baru'], ['password_confirmation', 'Ulangi Password Baru']] as [$f, $label])
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-400">{{ $label }}</label>
                                    <input type="password" name="{{ $f }}" required autocomplete="{{ $f === 'password_lama' ? 'current-password' : 'new-password' }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                                    @error($f, 'password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                            <p class="text-xs text-slate-400">Minimal 6 karakter.</p>
                            <div class="flex justify-end"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Ubah Password</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
