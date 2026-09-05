/* ==========================================================================
   LASAGNA — Home v3
   의존성 없음. 정적 호스팅 그대로 동작.
   ========================================================================== */
(function () {
  'use strict';

  var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var KV_DIR = 'assets/kv/';
  var FALLBACK = KV_DIR + '_placeholder.svg';

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }

  /* ------------------------------------------------------------- 리빌 --- */
  var io = null;
  if ('IntersectionObserver' in window && !REDUCED) {
    io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.06 });
  }
  function observe(n) { if (io) io.observe(n); else n.classList.add('in'); }
  document.querySelectorAll('.reveal').forEach(observe);

  /* --------------------------------------------------------- 히어로 영상 --
     영상은 질감이다. 없으면 CSS 그라운드가 남고 아무것도 깨지지 않는다. */
  (function () {
    var hero = document.querySelector('.hero');
    var v = document.getElementById('heroVideo');
    if (!hero || !v) return;

    function ok() { hero.setAttribute('data-video', 'ok'); v.classList.add('ready'); }
    v.addEventListener('loadeddata', ok);
    v.addEventListener('error', function () { hero.setAttribute('data-video', 'missing'); }, true);
    if (v.readyState >= 2) ok();

    var p = v.play();
    if (p && p.catch) {
      p.catch(function () {
        window.addEventListener('pointerdown', function once() {
          var q = v.play(); if (q && q.catch) q.catch(function () {});
          window.removeEventListener('pointerdown', once);
        }, { once: true });
      });
    }
    if (REDUCED) { v.removeAttribute('loop'); v.pause(); }
  })();

  /* ----------------------------------------------------------- KV 프리뷰 - */
  var peek = document.getElementById('kvPeek');
  var peekImg = document.getElementById('kvPeekImg');
  var on = false, tx = 0, ty = 0, raf = null;

  function move() {
    raf = null;
    if (peek) peek.style.transform =
      'translate3d(' + tx + 'px,' + ty + 'px,0) scale(' + (on ? 1 : 0.95) + ')';
  }
  function track(e) { tx = e.clientX - 140; ty = e.clientY - 79; if (!raf) raf = requestAnimationFrame(move); }
  function show(src) {
    if (!peek || !peekImg) return;
    if (peekImg.getAttribute('src') !== src) peekImg.setAttribute('src', src);
    on = true; peek.classList.add('on');
    window.addEventListener('pointermove', track);
  }
  function hide() {
    if (!peek) return;
    on = false; peek.classList.remove('on');
    window.removeEventListener('pointermove', track);
  }
  if (peekImg) {
    peekImg.addEventListener('error', function () {
      if (peekImg.getAttribute('src') !== FALLBACK) peekImg.setAttribute('src', FALLBACK);
    });
  }

  /* --------------------------------------------------------- Work 렌더 -- */
  function renderWork(items) {
    var list = document.getElementById('workList');
    if (!list) return;
    var frag = document.createDocumentFragment();

    items.forEach(function (item, i) {
      var src = KV_DIR + item.file;
      var row = el('li', 'work__row reveal');
      row.style.setProperty('--d', (i * 60) + 'ms');

      row.appendChild(el('span', 'work__i', pad(i + 1)));
      row.appendChild(el('h3', 'work__name', item.name));

      var meta = el('span', 'work__meta', item.meta || '자료 정리 중');
      if (!item.meta) meta.classList.add('pending');
      row.appendChild(meta);

      var tags = el('ul', 'work__tags');
      (item.tags || []).forEach(function (t) { tags.appendChild(el('li', 'work__tag', t)); });
      tags.appendChild(el('li', 'work__tag', item.year));
      row.appendChild(tags);

      var thumb = el('div', 'work__thumb');
      var timg = el('img');
      timg.setAttribute('loading', 'lazy');
      timg.setAttribute('alt', item.name + ' key visual');
      timg.setAttribute('src', src);
      timg.addEventListener('error', function () {
        if (timg.getAttribute('src') !== FALLBACK) timg.setAttribute('src', FALLBACK);
      });
      thumb.appendChild(timg);
      row.appendChild(thumb);

      // 케이스 페이지가 있는 행만 링크. 없으면 404 로 보내지 않는다.
      var hit;
      if (item.page) {
        hit = el('a', 'work__hit');
        hit.setAttribute('href', item.page);
        hit.setAttribute('aria-label', item.name + ' — case study');
      } else {
        hit = el('div', 'work__hit');
        tags.appendChild(el('li', 'work__tag work__tag--soon', '준비 중'));
      }
      hit.addEventListener('mouseenter', function () { show(src); });
      hit.addEventListener('mouseleave', hide);
      hit.addEventListener('focus', hide);
      row.appendChild(hit);

      frag.appendChild(row);
    });

    list.appendChild(frag);
    list.querySelectorAll('.reveal').forEach(observe);
  }

  /* ------------------------------------------------------- 메일 복사 ---- */
  document.querySelectorAll('a[data-mail]').forEach(function (a) {
    a.addEventListener('click', function () {
      var note = a.parentNode.querySelector('.copied');
      var done = function () {
        if (!note) return;
        note.classList.add('show');
        setTimeout(function () { note.classList.remove('show'); }, 2000);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(a.textContent.trim()).then(done, function () {});
      }
    });
  });

  /* ------------------------------------------------------------- boot --- */
  fetch(KV_DIR + 'manifest.json', { cache: 'no-cache' })
    .then(function (r) { if (!r.ok) throw new Error('manifest ' + r.status); return r.json(); })
    .then(function (d) { renderWork((d && d.items) || []); })
    .catch(function (err) {
      var list = document.getElementById('workList');
      if (list && !list.children.length) {
        var li = el('li', 'work__row');
        li.appendChild(el('span', 'work__meta',
          'Work 목록을 불러오지 못했습니다 (' + err.message + '). ' +
          'file:// 로 열면 fetch 가 차단됩니다 — `npx serve site` 로 실행하세요.'));
        list.appendChild(li);
      }
    });
})();
