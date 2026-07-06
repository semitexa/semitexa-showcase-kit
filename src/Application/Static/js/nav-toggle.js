/**
 * ShowcaseKit — mobile navigation drawer (burger).
 *
 * Progressive enhancement: marks <html> with `sk-nav-ready` so the CSS switches
 * the sidebar from an in-flow block (no-JS fallback) to an off-canvas drawer the
 * burger toggles. Closes on scrim click, nav-link follow, Escape, or when the
 * viewport widens past the mobile breakpoint.
 */
(function () {
  'use strict';

  function init() {
    var root = document.documentElement;
    var toggle = document.querySelector('[data-sk-nav-toggle]');
    if (!toggle) return;

    root.classList.add('sk-nav-ready');
    var sidebar = document.getElementById('sk-sidebar');
    var scrim = document.querySelector('[data-sk-nav-close]');

    function isOpen() { return root.getAttribute('data-sk-nav') === 'open'; }

    function setOpen(open) {
      root.setAttribute('data-sk-nav', open ? 'open' : 'closed');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (scrim) { if (open) { scrim.removeAttribute('hidden'); } else { scrim.setAttribute('hidden', ''); } }
      document.body.style.overflow = open ? 'hidden' : '';
    }

    toggle.addEventListener('click', function () { setOpen(!isOpen()); });

    document.addEventListener('click', function (e) {
      if (!isOpen()) return;
      if (e.target.closest('[data-sk-nav-close]')) { setOpen(false); return; }
      if (sidebar && sidebar.contains(e.target) && e.target.closest('a[href]')) { setOpen(false); }
    });

    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Escape' || e.key === 'Esc') && isOpen()) setOpen(false);
    });

    var mq = window.matchMedia('(min-width: 861px)');
    var onWide = function () { if (mq.matches) setOpen(false); };
    if (mq.addEventListener) { mq.addEventListener('change', onWide); } else if (mq.addListener) { mq.addListener(onWide); }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
