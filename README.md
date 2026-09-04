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
  work/
    biolin.html     케이스 상세
    synopex.html
  css/base.css      브랜드 시스템 · 타입 · 그리드 토큰
  css/home.css      Home 섹션 스타일
  css/case.css      케이스 상세 스타일
  js/home.js        Work 렌더링 · KV 프리뷰 · 히어로 영상 · 에셋 감사
  js/case.js        케이스 리빌 · KV 폴백 · Draft 표시
  assets/
    kv/manifest.json   Work 인덱스의 단일 소스 (page 필드가 케이스 링크)
    video/             background.mp4 (fetch-hero.sh 로 받는다)
    README.md          에셋 드롭인 가이드
```

- 설계 판단과 남은 결정: [`docs/HOME-V2.md`](docs/HOME-V2.md)
- 케이스 페이지 추가법 · 카피 원칙 · 컴플라이언스: [`docs/CASE-PAGES.md`](docs/CASE-PAGES.md)
- 에셋 채우는 법: [`site/assets/README.md`](site/assets/README.md)
