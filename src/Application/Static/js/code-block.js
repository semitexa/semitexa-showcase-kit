/**
 * ShowcaseKit — Code Block Controller
 *
 * Ported from the demo's code-tabs.js. Handles tab switching between source
 * panels and copy-to-clipboard. Delegated from the block root, so panels
 * revealed later by the disclosure controller need no re-mount. Structure only;
 * colours come from the skin via CSS tokens.
 */
(function () {
  'use strict';

  function mount(block) {
    if (!(block instanceof Element) || block.hasAttribute('data-code-block-mounted')) {
      return;
    }

    block.setAttribute('data-code-block-mounted', 'true');

    block.addEventListener('click', function (event) {
      var tab = event.target.closest('[data-code-tab]');
      if (tab && block.contains(tab)) {
        activateTab(block, tab);
        return;
      }

      var copyButton = event.target.closest('[data-copy-source]');
      if (copyButton && block.contains(copyButton)) {
        copySource(copyButton);
      }
    });
  }

  function activateTab(block, tab) {
    block.querySelectorAll('.code-block__tab').forEach(function (candidate) {
      candidate.classList.remove('code-block__tab--active');
      candidate.setAttribute('aria-selected', 'false');
    });
    block.querySelectorAll('.code-block__panel').forEach(function (panel) {
      panel.classList.remove('code-block__panel--active');
      panel.setAttribute('hidden', '');
    });

    tab.classList.add('code-block__tab--active');
    tab.setAttribute('aria-selected', 'true');

    var panel = document.getElementById(tab.getAttribute('aria-controls'));
    if (panel) {
      panel.classList.add('code-block__panel--active');
      panel.removeAttribute('hidden');
    }
  }

  function copySource(button) {
    // Prefer the raw textarea: the highlighted markup carries line numbers,
    // which must never reach the clipboard.
    var sourceId = button.getAttribute('data-copy-raw-source') || button.getAttribute('data-copy-source');
    var source = document.getElementById(sourceId);
    if (!source) {
      return;
    }

    var text = ('value' in source ? source.value : source.textContent) || '';

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () {
        showFeedback(button, 'Copied!');
      }).catch(function () {
        showFeedback(button, 'Failed');
      });
      return;
    }

    var scratch = document.createElement('textarea');
    scratch.value = text;
    scratch.style.position = 'fixed';
    scratch.style.opacity = '0';
    document.body.appendChild(scratch);
    scratch.select();
    try {
      document.execCommand('copy');
      showFeedback(button, 'Copied!');
    } catch (e) {
      showFeedback(button, 'Failed');
    }
    document.body.removeChild(scratch);
  }

  function showFeedback(button, message) {
    if (button.hasAttribute('data-copy-feedback')) {
      return;
    }

    var original = button.textContent;
    button.setAttribute('data-copy-feedback', 'true');
    button.textContent = message;
    setTimeout(function () {
      button.textContent = original;
      button.removeAttribute('data-copy-feedback');
    }, 1500);
  }

  function mountAll() {
    document.querySelectorAll('[data-code-block]').forEach(mount);
  }

  if (window.SemitexaComponent && typeof window.SemitexaComponent.register === 'function') {
    window.SemitexaComponent.register('showcase-kit-code-block', mount);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountAll, { once: true });
  } else {
    mountAll();
  }
})();
