import json, re, pathlib

ROOT = pathlib.Path('/home/user/lasagna/site')
OUT  = pathlib.Path('/home/user/lasagna/preview/lasagna-home-v3.html')
OUT.parent.mkdir(parents=True, exist_ok=True)

html = (ROOT / 'index.html').read_text(encoding='utf-8')
base = (ROOT / 'css/base.css').read_text(encoding='utf-8')
home = (ROOT / 'css/home.css').read_text(encoding='utf-8')
items = json.loads((ROOT / 'assets/kv/manifest.json').read_text(encoding='utf-8'))['items']

# --- 리빌: JS 없이도 보이게. .js 가 붙었을 때만 숨겼다 나타난다 -------------
base = base.replace("""/* --- 리빌 ----------------------------------------------------------------- */
.reveal {
  opacity: 0; transform: translateY(20px);
  transition: opacity 0.8s var(--ease), transform 0.8s var(--ease);
  transition-delay: var(--d, 0ms);
}
.reveal.in { opacity: 1; transform: none; }""",
"""/* --- 리빌 -----------------------------------------------------------------
   기본은 '보이는' 상태다. JS 가 <html> 에 .js 를 붙였을 때만 숨겼다가
   스크롤에 맞춰 드러낸다. 스크립트가 죽어도 내용은 남는다. */
.reveal { opacity: 1; transform: none; }
.js .reveal {
  opacity: 0; transform: translateY(20px);
  transition: opacity 0.8s var(--ease), transform 0.8s var(--ease);
  transition-delay: var(--d, 0ms);
}
.js .reveal.in { opacity: 1; transform: none; }""")

# --- 미리보기용: 이미지 파일이 없으므로 로고는 워드마크, KV 프리뷰는 제거 ----
home += """

/* ── 단일 파일 미리보기 전용 ───────────────────────────────────────────────
   에셋 파일이 없는 상태에서 보기 위한 것. 실제 사이트에는 적용되지 않는다. */
.nav__logo {
  font-weight: 700; font-size: 0.8125rem;
  letter-spacing: 0.22em; text-transform: uppercase;
}
.preview-note {
  /* 고정이 아니라 문서 끝에 놓는다 — 콘텐츠를 가리지 않도록 */
  border-top: 1px solid var(--rule); background: var(--bg);
  padding: 18px var(--margin) 26px;
  font-family: var(--mono); font-size: 0.625rem;
  letter-spacing: 0.08em; color: var(--fg-5); text-align: center;
}
.preview-note b { color: var(--fg-2); font-weight: 400; }
"""

# --- HTML 조립 --------------------------------------------------------------
# 로고 이미지 → 워드마크
html = re.sub(r'<a class="nav__logo"[^>]*>\s*<img[^>]*>\s*</a>',
              '<a class="nav__logo" href="#top" aria-label="Lasagna — 홈">Lasagna</a>',
              html, count=1)

# 히어로 영상: 파일이 없으니 통째로 제거 (CSS 그라운드가 남는다)
html = re.sub(r'<div class="hero__media">.*?</div>\s*(?=<div class="hero__scrim")',
              '<div class="hero__media"></div>\n  ', html, count=1, flags=re.S)

# KV 프리뷰 레이어 제거
html = re.sub(r'<div class="kv-peek".*?</div>\s*', '', html, count=1, flags=re.S)

# 외부 CSS/JS 링크 제거
html = re.sub(r'\s*<link rel="stylesheet" href="css/[^"]*">', '', html)
html = re.sub(r'\s*<script src="js/home\.js"></script>', '', html)

# 타이틀 — 갤러리에서 식별되도록
html = html.replace('<title>Lasagna — 문제를 바라보는 방식부터 다시 디자인합니다</title>',
                    '<title>Lasagna Home v3</title>')

# CSS 인라인
html = html.replace('</head>', f'<style>\n{base}\n{home}\n</style>\n</head>', 1)

# JS 인라인 (fetch 없이 데이터 내장)
data = json.dumps([{k: i[k] for k in ('name','meta','tags','year','page')} for i in items],
                  ensure_ascii=False, indent=2)

script = """
<div class="preview-note">단일 파일 미리보기 — 이미지 · 영상 <b>미포함</b>. 레이아웃과 타이포 확인용.</div>
<script>
(function () {
  'use strict';
  var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('js');

  function el(t, c, x) { var n = document.createElement(t); if (c) n.className = c;
    if (x != null) n.textContent = x; return n; }
  function pad(n) { return (n < 10 ? '0' : '') + n; }

  var io = null;
  if ('IntersectionObserver' in window && !REDUCED) {
    io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.06 });
  }
  function observe(n) { if (io) io.observe(n); else n.classList.add('in'); }
  document.querySelectorAll('.reveal').forEach(observe);

  var ITEMS = __ITEMS__;

  var list = document.getElementById('workList');
  if (list) {
    ITEMS.forEach(function (item, i) {
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
      if (!item.page) tags.appendChild(el('li', 'work__tag work__tag--soon', '준비 중'));
      row.appendChild(tags);
      list.appendChild(row);
      observe(row);
    });
  }

  document.querySelectorAll('a[data-mail]').forEach(function (a) {
    a.addEventListener('click', function () {
      var note = a.parentNode.querySelector('.copied');
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(a.textContent.trim()).then(function () {
          if (!note) return;
          note.classList.add('show');
          setTimeout(function () { note.classList.remove('show'); }, 2000);
        }, function () {});
      }
    });
  });
})();
</script>
</body>""".replace('__ITEMS__', data)

html = html.replace('</body>', script, 1)

OUT.write_text(html, encoding='utf-8')
print('생성:', OUT)
print('크기:', round(OUT.stat().st_size / 1024, 1), 'KB')
for bad in ['css/base.css', 'js/home.js', 'assets/logo.png', 'assets/kv/manifest.json', 'background.mp4']:
    print(('  남아있음 ' if bad in html else '  제거됨   ') + bad)
