<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal Orang Tua | {{ setting('nama_sekolah', 'SMKN 9 MALANG') }}</title>

    @include('partials.pwa-head', ['variant' => 'ortu'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>select { color-scheme: light; } select option { color: #1e293b !important; background-color: #ffffff !important; }</style>
</head>
<body
    x-data="{ darkMode: false }"
    x-init="darkMode = JSON.parse(localStorage.getItem('darkModeOrtu')) || false; $watch('darkMode', value => localStorage.setItem('darkModeOrtu', JSON.stringify(value)))"
    :class="{ 'dark': darkMode }"
>
    @include('partials.app-loader')

    @auth('orangtua')
        @if(Auth::guard('orangtua')->user()->harus_lengkapi_profil)
            <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700/60 dark:bg-slate-800">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-blue-500">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-bold text-slate-900 dark:text-slate-100">Lengkapi Akun Anda</h5>
                            <p class="m-0 text-xs text-slate-500 dark:text-slate-400">Akun ini dibuatkan oleh sekolah dengan nama &amp; password sementara. Silakan ganti dahulu sebelum melanjutkan.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('ortu.akun.update') }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Nama Orang Tua / Wali</label>
                            <input type="text" name="nama" value="{{ old('nama', Auth::guard('orangtua')->user()->nama) }}" required class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm dark:border-slate-600">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Password Baru</label>
                            <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm dark:border-slate-600">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Ulangi Password Baru</label>
                            <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi password baru" class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm dark:border-slate-600">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan &amp; Lanjutkan</button>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
    <header class="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-2">
                @if(setting('logo_sekolah'))
                    <img src="{{ asset('storage/'.setting('logo_sekolah')) }}" alt="Logo Sekolah" class="h-7 w-7 rounded object-contain">
                @else
                    <i class="fa-solid fa-graduation-cap text-xl text-blue-600 dark:text-blue-400"></i>
                @endif
                <span class="font-bold text-slate-900 dark:text-white">Portal Orang Tua &mdash; {{ setting('nama_sekolah', 'SMKN 9 Malang') }}</span>
            </div>
            <div class="flex items-center gap-3">
                @include('partials.pwa-install-button', [
                    'buttonClass' => 'rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700',
                ])
                <button @click="darkMode = !darkMode" class="rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-moon" x-show="!darkMode"></i>
                    <i class="fa-solid fa-sun" x-show="darkMode" style="display: none;"></i>
                </button>
                @auth('orangtua')
                    <a href="{{ route('ortu.akun.edit') }}" class="rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700" title="Akun Saya">
                        <i class="fa-solid fa-user-gear"></i>
                    </a>
                    <form method="POST" action="{{ route('ortu.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-rose-300 px-3 py-1.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-400 dark:hover:bg-rose-500/10">
                            <i class="fa-solid fa-power-off mr-1"></i> Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-8">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    @include('partials.notify')

    <footer class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">&copy; {{ date('Y') }} SMKN 9 Malang. All rights reserved.</footer>
    </div>
</body>
</html>
