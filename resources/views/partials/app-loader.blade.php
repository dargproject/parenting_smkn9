<style>
    #app-loader {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .6);
        backdrop-filter: blur(2px);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }
    #app-loader.is-visible { opacity: 1; pointer-events: all; }
    #app-loader .app-loader-bar {
        position: absolute;
        top: 0; left: 0;
        height: 3px; width: 100%;
        background-image: linear-gradient(90deg, transparent, #3b82f6, transparent);
        background-size: 40% 100%;
        background-repeat: no-repeat;
        animation: app-loader-sweep 1.1s ease-in-out infinite;
    }
    @keyframes app-loader-sweep {
        0% { background-position: -40% 0; }
        100% { background-position: 140% 0; }
    }
    #app-loader .app-loader-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .75rem;
        padding: 1.75rem 2.25rem;
        border-radius: 1rem;
        background: #1e293b;
        border: 1px solid rgba(148, 163, 184, .25);
        box-shadow: 0 20px 40px rgba(0, 0, 0, .35);
    }
    #app-loader .app-loader-icon {
        width: 56px; height: 56px;
        border-radius: 9999px;
        background: #2563eb;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 22px;
        overflow: hidden;
        animation: app-loader-pulse 1.2s ease-in-out infinite;
    }
    #app-loader .app-loader-icon img {
        width: 100%; height: 100%;
        object-fit: contain;
        padding: 6px;
    }
    @keyframes app-loader-pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, .55); }
        50% { transform: scale(1.08); box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
    }
    #app-loader .app-loader-text {
        color: #e2e8f0;
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .03em;
        text-transform: uppercase;
    }
</style>
<div id="app-loader">
    <div class="app-loader-bar"></div>
    <div class="app-loader-card">
        <div class="app-loader-icon">
            @if(setting('logo_sekolah'))
                <img src="{{ asset('storage/'.setting('logo_sekolah')) }}" alt="Logo Sekolah">
            @else
                <i class="fa-solid fa-graduation-cap"></i>
            @endif
        </div>
        <p class="app-loader-text" id="app-loader-text">Memuat...</p>
    </div>
</div>
<script>
    (function () {
        var loader = document.getElementById('app-loader');
        if (!loader) return;

        var startedAt = Date.now();
        var MIN_MS = 350;
        var hideTimer = null;

        function show() {
            loader.classList.add('is-visible');
            clearTimeout(hideTimer);
            // Safety net: a click can trigger a file download or print dialog instead of a
            // real navigation, so 'load' never fires again. Never let the overlay get stuck.
            hideTimer = setTimeout(hide, 6000);
        }

        function hide() {
            clearTimeout(hideTimer);
            var wait = Math.max(0, MIN_MS - (Date.now() - startedAt));
            setTimeout(function () { loader.classList.remove('is-visible'); }, wait);
        }

        show();
        window.addEventListener('load', hide);

        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var a = e.target.closest('a[href]');
            if (!a) return;
            var href = a.getAttribute('href') || '';
            if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
            if (a.target && a.target !== '_self') return;
            if (a.hasAttribute('download') || a.dataset.noLoader !== undefined) return;
            if (a.origin && a.origin !== window.location.origin) return;
            show();
        });

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (e.defaultPrevented || (form.method || '').toLowerCase() === 'get' || form.dataset.noLoader !== undefined) return;
            show();
        });

        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { clearTimeout(hideTimer); loader.classList.remove('is-visible'); }
        });
    })();
</script>
