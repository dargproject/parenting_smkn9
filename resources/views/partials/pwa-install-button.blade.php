@php
    $buttonClass = $buttonClass ?? 'p-2 text-slate-400 hover:text-slate-100 transition-colors';
    $iconClass = $iconClass ?? 'fa-solid fa-circle-down';
@endphp
<div
    x-data="{
        show: false,
        ios: false,
        iosModal: false,
        init() {
            this.show = !!window.__pwaInstallPrompt;
            window.addEventListener('pwa-installable', () => { this.show = true; });
            window.addEventListener('pwa-installed', () => { this.show = false; });
            const ua = navigator.userAgent.toLowerCase();
            const isIos = /iphone|ipad|ipod/.test(ua);
            const isStandalone = navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;
            if (isIos && !isStandalone) { this.ios = true; this.show = true; }
        }
    }"
    class="contents"
>
    <button
        x-show="show"
        style="display: none;"
        @click="ios ? (iosModal = true) : (window.__pwaInstallPrompt?.prompt(), window.__pwaInstallPrompt?.userChoice.then(() => { window.__pwaInstallPrompt = null; show = false; }))"
        class="{{ $buttonClass }}"
        title="Pasang aplikasi ke perangkat"
    >
        <i class="{{ $iconClass }}"></i>
    </button>

    <div
        x-show="iosModal"
        style="display: none;"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="iosModal = false"
        @click.self="iosModal = false"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 text-slate-700 shadow-xl dark:bg-slate-800 dark:text-slate-200">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Pasang ke Layar Utama</h3>
                <button @click="iosModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <ol class="list-decimal space-y-2 pl-5 text-sm">
                <li>Ketuk ikon <i class="fa-solid fa-arrow-up-from-bracket mx-1"></i> <strong>Bagikan</strong> di bar browser.</li>
                <li>Pilih <strong>&ldquo;Tambah ke Layar Utama&rdquo;</strong> (Add to Home Screen).</li>
                <li>Ketuk <strong>&ldquo;Tambah&rdquo;</strong> di pojok kanan atas.</li>
            </ol>
        </div>
    </div>
</div>
