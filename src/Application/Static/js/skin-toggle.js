/*
 * ShowcaseKit skin-mode toggle. Wires every [data-skin-toggle] button to flip
 * the standard `data-skin-mode` light/dark contract (persisted in localStorage
 * under `semitexa_skin_mode`) and updates [data-skin-text] labels + aria. The
 * kit layout's <head> already pre-paints the mode before first paint; this adds
 * the click behaviour so any kit-consuming site (platform, demo, …) gets a
 * working toggle without site-specific JS.
 */
(function () {
    var STORAGE_KEY = 'semitexa_skin_mode';

    function normalize(mode) {
        return mode === 'dark' ? 'dark' : 'light';
    }

    function readStored() {
        try {
            var stored = window.localStorage.getItem(STORAGE_KEY);
            if (stored === 'dark' || stored === 'light') {
                return stored;
            }
        } catch (e) { /* ignore */ }
        return null;
    }

    function resolveInitial() {
        var stored = readStored();
        if (stored) return stored;
        var docMode = document.documentElement.getAttribute('data-skin-mode');
        if (docMode === 'dark' || docMode === 'light') return docMode;
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
        return 'light';
    }

    function apply(mode) {
        var resolved = normalize(mode);
        var body = document.body;
        var darkLabel = body ? (body.getAttribute('data-theme-label-dark') || 'Dark mode') : 'Dark mode';
        var lightLabel = body ? (body.getAttribute('data-theme-label-light') || 'Light mode') : 'Light mode';
        var nextActionLabel = resolved === 'dark' ? lightLabel : darkLabel;

        document.documentElement.setAttribute('data-skin-mode', resolved);

        document.querySelectorAll('[data-skin-toggle]').forEach(function (toggle) {
            toggle.setAttribute('aria-pressed', resolved === 'dark' ? 'true' : 'false');
            toggle.setAttribute('aria-label', resolved === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            toggle.setAttribute('title', resolved === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });

        document.querySelectorAll('[data-skin-text]').forEach(function (label) {
            label.textContent = nextActionLabel;
        });
    }

    document.querySelectorAll('[data-skin-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-skin-mode') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            apply(next);
            try { window.localStorage.setItem(STORAGE_KEY, next); } catch (e) { /* ignore */ }
        });
    });

    apply(resolveInitial());
})();
