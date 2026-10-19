@php
    $variant = $variant ?? 'guru';
    $manifestUrl = $variant === 'ortu' ? route('pwa.manifest.ortu') : route('pwa.manifest.guru');
    $swPath = $variant === 'ortu' ? '/sw-ortu.js' : '/sw-guru.js';
    $swScope = $variant === 'ortu' ? '/ortu/' : '/';
    $themeColor = $variant === 'ortu' ? '#059669' : '#2563eb';
    $iconPrefix = $variant === 'ortu' ? 'pwa-ortu' : 'pwa-guru';
    $appTitle = $variant === 'ortu' ? 'Portal Ortu' : 'SIM Akademik';
@endphp
<link rel="manifest" href="{{ $manifestUrl }}">
<meta name="theme-color" content="{{ $themeColor }}">
<link rel="apple-touch-icon" href="{{ asset("assets/img/icons/{$iconPrefix}-192.png") }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $appTitle }}">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ $swPath }}', { scope: '{{ $swScope }}' }).catch(() => {});
        });
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.__pwaInstallPrompt = e;
        window.dispatchEvent(new CustomEvent('pwa-installable'));
    });

    window.addEventListener('appinstalled', () => {
        window.__pwaInstallPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });
</script>
