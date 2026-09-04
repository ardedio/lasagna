/* ==========================================================================
   LASAGNA EMPIRE — Case detail
   Self-contained. Reveal on scroll + the draft-copy notice.
   ========================================================================== */
(function () {
  'use strict';

  var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------- scroll reveal */
  if ('IntersectionObserver' in window && !REDUCED) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(function (n) { io.observe(n); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (n) { n.classList.add('is-in'); });
  }

  /* --------------------------------------------------- key visual fallback
     These <img> carry an inline src, so a 404 can fire before this script
     runs. Attaching a listener alone would miss it — images that already
     failed are caught by the complete/naturalWidth check. */
  var FALLBACK_KV = '../assets/kv/_placeholder.svg';

  function swap(img) {
    if (img.getAttribute('src') !== FALLBACK_KV) img.setAttribute('src', FALLBACK_KV);
  }

  document.querySelectorAll('img[data-kv]').forEach(function (img) {
    img.addEventListener('error', function () { swap(img); });
    if (img.complete && img.naturalWidth === 0) swap(img);
  });

  /* ------------------------------------------------------- draft notice --
     Case copy describes live client work. Anything not yet signed off is
     listed here, on the page, rather than tracked in someone's head. */
  (function draft() {
    var box    = document.getElementById('draft');
    var toggle = document.getElementById('draftToggle');
    var hide   = document.getElementById('draftHide');
    if (!box || !toggle) return;

    var key = 'le.draft.off.' + (document.body.getAttribute('data-case') || 'x');
    try { if (sessionStorage.getItem(key) === '1') return; } catch (e) {}

    box.hidden = false;

    toggle.addEventListener('click', function () {
      var open = box.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(open));
      var chev = toggle.querySelector('.draft__chev');
      if (chev) chev.textContent = open ? '−' : '+';
    });

    if (hide) {
      hide.addEventListener('click', function () {
        box.hidden = true;
        try { sessionStorage.setItem(key, '1'); } catch (e) {}
      });
    }
  })();
})();
