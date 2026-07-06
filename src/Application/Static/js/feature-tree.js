/**
 * ShowcaseKit — Feature tree sidebar
 *
 * Ported from the demo's feature-tree toggle behaviour. Each section header has
 * a [feature-tree__toggle] button that expands/collapses its section body
 * (.feature-tree__section-body[data-expanded]). Initial open state is read from
 * the server-rendered aria-expanded attribute. Structure only — no palette.
 */
(function () {
  'use strict';

  function initFeatureTree() {
    document.querySelectorAll('.feature-tree__toggle').forEach(function (toggle) {
      // Idempotency guard: if the script is ever included more than once, bind
      // each toggle only the first time — a second click listener would flip
      // aria-expanded twice per click (expand then collapse).
      if (toggle.dataset.ftBound === '1') return;
      toggle.dataset.ftBound = '1';

      var section = toggle.closest('.feature-tree__section');
      if (!section) return;
      var body = section.querySelector('.feature-tree__section-body');

      var startOpen = toggle.getAttribute('aria-expanded') === 'true';
      section.classList.toggle('feature-tree__section--open', startOpen);
      if (body) {
        body.setAttribute('data-expanded', startOpen ? 'true' : 'false');
      }

      toggle.addEventListener('click', function () {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        var next = expanded ? 'false' : 'true';
        toggle.setAttribute('aria-expanded', next);
        section.classList.toggle('feature-tree__section--open', next === 'true');
        if (body) {
          body.setAttribute('data-expanded', next);
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFeatureTree);
  } else {
    initFeatureTree();
  }
})();
