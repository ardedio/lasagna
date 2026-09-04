# lasagna

라자냐 엠파이어 (Lasagna Empire) — 웹.

## site/

Home 프로토타입 v2. 의존성 없는 정적 HTML/CSS/JS.

```bash
npx serve site
```

`file://` 직접 열기는 지원하지 않는다 (`fetch()` 차단).

```
site/
  index.html
  css/base.css      브랜드 시스템 · 타입 · 그리드 토큰
  css/home.css      Home 섹션 스타일
  js/home.js        Work 렌더링 · KV 프리뷰 · 히어로 영상 · 에셋 감사
  assets/
    kv/manifest.json   Work 섹션의 단일 소스
    video/             background.mp4 (fetch-hero.sh 로 받는다)
    README.md          에셋 드롭인 가이드
```

설계 판단과 남은 결정: [`docs/HOME-V2.md`](docs/HOME-V2.md)
에셋 채우는 법: [`site/assets/README.md`](site/assets/README.md)
