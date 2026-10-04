<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal Orang Tua | {{ setting('nama_sekolah', 'SMKN 9 MALANG') }}</title>

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
                <button @click="darkMode = !darkMode" class="rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-moon" x-show="!darkMode"></i>
                    <i class="fa-solid fa-sun" x-show="darkMode" style="display: none;"></i>
                </button>
                @auth('orangtua')
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
