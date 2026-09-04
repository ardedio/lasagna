/* ==========================================================================
   LASAGNA EMPIRE — Home v2
   No dependencies. Static-host safe.
   ========================================================================== */
(function () {
  'use strict';

  var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var KV_DIR  = 'assets/kv/';
  var FALLBACK_KV = KV_DIR + '_placeholder.svg';

  /* ---------------------------------------------------------------- utils */
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }

  /* ------------------------------------------------------- scroll reveal */
  var io = null;
  if ('IntersectionObserver' in window && !REDUCED) {
    io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
  }
  function observe(node) {
    if (io) io.observe(node); else node.classList.add('is-in');
  }
  document.querySelectorAll('.reveal').forEach(observe);

  /* ------------------------------------------------------------ hero video
     The loop is texture. If background.mp4 is missing, the CSS gradient
     ground stays and nothing breaks. */
  (function heroVideo() {
    var hero  = document.querySelector('.hero');
    var video = document.getElementById('heroVideo');
    if (!hero || !video) return;

    function ok() {
      hero.setAttribute('data-video', 'ok');
      video.classList.add('is-ready');
    }
    function fail() { hero.setAttribute('data-video', 'missing'); }

    video.addEventListener('loadeddata', ok);
    video.addEventListener('error', fail, true);
    if (video.readyState >= 2) ok();

    // Some browsers block autoplay even when muted; retry once on first input.
    var kick = function () {
      var p = video.play();
      if (p && p.catch) p.catch(function () {});
      window.removeEventListener('pointerdown', kick);
    };
    var attempt = video.play();
    if (attempt && attempt.catch) {
      attempt.catch(function () { window.addEventListener('pointerdown', kick, { once: true }); });
    }

    // Reduced motion: hold a single frame instead of looping.
    if (REDUCED) { video.removeAttribute('loop'); video.pause(); }
  })();

  /* ---------------------------------------------------------------- ticker
     Duplicate the sequence so the -50% keyframe loops seamlessly. */
  (function ticker() {
    var track = document.getElementById('tickerTrack');
    if (!track) return;
    var seq = track.querySelector('.ticker__seq');
    if (!seq) return;
    track.appendChild(seq.cloneNode(true));
  })();

  /* ------------------------------------------------------------ KV preview */
  var peek    = document.getElementById('kvPeek');
  var peekImg = document.getElementById('kvPeekImg');
  var peekOn  = false;
  var tx = 0, ty = 0, raf = null;

  function movePeek() {
    raf = null;
    if (peek) peek.style.transform =
      'translate3d(' + tx + 'px,' + ty + 'px,0) scale(' + (peekOn ? 1 : 0.94) + ')';
  }
  function onMove(e) {
    tx = e.clientX - 150;                 // half of .kv-peek width
    ty = e.clientY - 84;                  // roughly half its height
    if (!raf) raf = requestAnimationFrame(movePeek);
  }
  function showPeek(src) {
    if (!peek || !peekImg) return;
    if (peekImg.getAttribute('src') !== src) peekImg.setAttribute('src', src);
    peekOn = true;
    peek.classList.add('is-on');
    window.addEventListener('pointermove', onMove);
  }
  function hidePeek() {
    if (!peek) return;
    peekOn = false;
    peek.classList.remove('is-on');
    window.removeEventListener('pointermove', onMove);
  }
  if (peekImg) {
    peekImg.addEventListener('error', function () {
      if (peekImg.getAttribute('src') !== FALLBACK_KV) peekImg.setAttribute('src', FALLBACK_KV);
    });
  }

  /* -------------------------------------------------------- work index --- */
  function renderWork(items) {
    var list = document.getElementById('workList');
    if (!list) return;
    var frag = document.createDocumentFragment();

    items.forEach(function (item, i) {
      var src = KV_DIR + item.file;

      var row = el('li', 'work__row reveal');
      row.style.setProperty('--reveal-delay', (i * 55) + 'ms');

      row.appendChild(el('span', 'work__idx t-num', pad(i + 1)));

      var name = el('h3', 'work__name', item.name);
      row.appendChild(name);

      row.appendChild(el('span', 'work__meta', item.meta));

      var tags = el('ul', 'work__tags');
      (item.tags || []).forEach(function (t) { tags.appendChild(el('li', 'work__tag', t)); });
      tags.appendChild(el('li', 'work__tag', item.year));
      row.appendChild(tags);   // chips may still be appended below

      // Mobile inline thumb (hover preview is pointer-only).
      var thumb = el('div', 'work__thumb');
      var timg  = el('img');
      timg.setAttribute('loading', 'lazy');
      timg.setAttribute('alt', item.name + ' key visual');
      timg.setAttribute('src', src);
      timg.addEventListener('error', function () {
        if (timg.getAttribute('src') !== FALLBACK_KV) timg.setAttribute('src', FALLBACK_KV);
      });
      thumb.appendChild(timg);
      row.appendChild(thumb);

      // Whole row is the target, but only where a case page actually exists.
      // Rows without one stay unlinked rather than pointing at a 404.
      var hit;
      if (item.page) {
        hit = el('a', 'work__link');
        hit.setAttribute('href', item.page);
        hit.setAttribute('aria-label', item.name + ' — ' + item.meta + ' — case study');
      } else {
        hit = el('div', 'work__link');
        row.classList.add('is-unlinked');
        tags.appendChild(el('li', 'work__tag work__tag--soon', '준비 중'));
      }
      hit.addEventListener('mouseenter', function () { showPeek(src); });
      hit.addEventListener('mouseleave', hidePeek);
      hit.addEventListener('focus', hidePeek);
      row.appendChild(hit);

      frag.appendChild(row);
    });

    list.appendChild(frag);
    list.querySelectorAll('.reveal').forEach(observe);
  }

  /* ---------------------------------------------------------- asset audit
     Dev-only. Names the slots still holding placeholders. Never renders once
     every manifest entry is real and background.mp4 is loading. */
  function audit(items) {
    var box    = document.getElementById('audit');
    var body   = document.getElementById('auditBody');
    var chip   = document.getElementById('auditChip');
    var toggle = document.getElementById('auditToggle');
    var hide   = document.getElementById('auditHide');
    var hero   = document.querySelector('.hero');
    if (!box || !body || !chip || !toggle) return;

    try { if (sessionStorage.getItem('le.audit.off') === '1') return; } catch (e) {}

    var pending = items.filter(function (i) { return i.placeholder; })
                       .map(function (i) { return i.name; });
    var heroMissing = !hero || hero.getAttribute('data-video') !== 'ok';

    var lines = [];
    if (pending.length) {
      lines.push('KV ' + pending.length + '/' + items.length + ' \u2014 ' + pending.join(', '));
    }
    if (heroMissing) lines.push('HERO \u2014 assets/video/background.mp4 \uc5c6\uc74c');
    if (!lines.length) return;

    var count = (pending.length ? 1 : 0) + (heroMissing ? 1 : 0);
    chip.textContent = count + ' asset slot' + (count > 1 ? 's' : '') + ' pending';
    body.textContent = lines.join('. ') + '. \uc124\uce58\ubc29\ubc95: site/assets/README.md';
    box.hidden = false;

    toggle.addEventListener('click', function () {
      var open = box.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(open));
      var chev = toggle.querySelector('.audit__chev');
      if (chev) chev.textContent = open ? '\u2212' : '+';
    });

    if (hide) {
      hide.addEventListener('click', function () {
        box.hidden = true;
        try { sessionStorage.setItem('le.audit.off', '1'); } catch (e) {}
      });
    }
  }

  /* ------------------------------------------------------------------ boot */
  fetch(KV_DIR + 'manifest.json', { cache: 'no-cache' })
    .then(function (r) {
      if (!r.ok) throw new Error('manifest ' + r.status);
      return r.json();
    })
    .then(function (data) {
      var items = (data && data.items) || [];
      renderWork(items);
      // Give the hero video a beat to resolve before auditing it.
      setTimeout(function () { audit(items); }, 1600);
    })
    .catch(function (err) {
      // file:// blocks fetch in some browsers — say so rather than showing nothing.
      var list = document.getElementById('workList');
      if (list && !list.children.length) {
        var note = el('li', 'work__row');
        note.appendChild(el('span', 'work__meta',
          'Work 목록을 불러오지 못했습니다 (' + err.message + '). ' +
          'file:// 로 열면 fetch가 차단됩니다 — `npx serve site` 로 실행하세요.'));
        list.appendChild(note);
      }
    });
})();
