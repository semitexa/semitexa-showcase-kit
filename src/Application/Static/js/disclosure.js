/**
 * ShowcaseKit — Progressive Disclosure Controller
 *
 * Ported from the demo's disclosure.js. Manages the L1 → L2 → L3 progressive
 * reveal: expandable sections ([data-expandable]), deep-dive drawers
 * ([data-drawer]), and disclosure-prompt triggers ([data-disclosure-trigger]).
 * State persists per-page via sessionStorage. Palette-independent (structure
 * only); colours come from the skin via CSS tokens.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'showcase-kit-disclosure';

  function getState() {
    try {
      var parsed = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (e) {
      return {};
    }
  }

  function saveState(state) {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      // sessionStorage may be unavailable
    }
  }

  function syncTriggerState(targetId, expanded) {
    document.querySelectorAll('[data-disclosure-trigger="' + targetId + '"]').forEach(function (trigger) {
      trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  }

  function toggleSection(targetId, forceOpen) {
    var target = document.getElementById(targetId);
    if (!target) return false;

    var isExpanded = target.getAttribute('data-expanded') === 'true';
    var shouldOpen = forceOpen !== undefined ? forceOpen : !isExpanded;

    target.setAttribute('data-expanded', shouldOpen ? 'true' : 'false');
    syncTriggerState(targetId, shouldOpen);

    var state = getState();
    state[targetId] = shouldOpen;
    saveState(state);

    if (shouldOpen) {
      requestAnimationFrame(function () {
        var rect = target.getBoundingClientRect();
        if (rect.top < 0 || rect.top > window.innerHeight * 0.7) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    }

    return shouldOpen;
  }

  function closeDrawer(targetId) {
    var target = document.getElementById(targetId);
    if (!target) return;

    var trigger = document.querySelector('[data-disclosure-trigger="' + targetId + '"]');
    if (trigger) {
      trigger.focus();
    } else if (document.activeElement instanceof HTMLElement && target.contains(document.activeElement)) {
      document.body.focus();
    }

    target.setAttribute('data-expanded', 'false');
    syncTriggerState(targetId, false);

    var state = getState();
    state[targetId] = false;
    saveState(state);
  }

  function restoreState() {
    var state = getState();
    Object.keys(state).forEach(function (targetId) {
      if (state[targetId]) {
        var target = document.getElementById(targetId);
        if (target) {
          target.setAttribute('data-expanded', 'true');
          syncTriggerState(targetId, true);
        }
      }
    });
  }

  function markFirstViewPrompts() {
    var state = getState();
    document.querySelectorAll('[data-disclosure-trigger]').forEach(function (trigger) {
      var targetId = trigger.getAttribute('data-disclosure-trigger');
      if (!state[targetId]) {
        trigger.setAttribute('data-first-view', 'true');
      }
    });
  }

  // Event delegation for disclosure triggers + drawer close controls.
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-disclosure-trigger]');
    if (trigger) {
      var targetId = trigger.getAttribute('data-disclosure-trigger');
      var expanded = toggleSection(targetId);
      trigger.removeAttribute('data-first-view');
      if (expanded) {
        trigger.dispatchEvent(new CustomEvent('disclosure:expand', {
          bubbles: true,
          detail: { targetId: targetId, source: 'user' }
        }));
      }
      return;
    }

    var closeBtn = e.target.closest('[data-drawer-close]');
    if (closeBtn) {
      var drawer = closeBtn.closest('[data-drawer]');
      if (drawer) {
        closeDrawer(drawer.id);
      }
    }
  });

  // Close drawer on Escape.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var openDrawer = document.activeElement instanceof Element
        ? document.activeElement.closest('[data-drawer][data-expanded="true"]')
        : null;
      if (!openDrawer) {
        openDrawer = document.querySelector('[data-drawer][data-expanded="true"]');
      }
      if (openDrawer) {
        closeDrawer(openDrawer.id);
      }
    }
  });

  // Programmatic open requests.
  document.addEventListener('disclosure:expand', function (e) {
    if (e.detail && e.detail.targetId && e.detail.source !== 'user') {
      toggleSection(e.detail.targetId, true);
    }
  });

  function init() {
    restoreState();
    markFirstViewPrompts();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
