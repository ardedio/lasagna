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
    synopex.html    케이스 상세
  css/base.css      브랜드 시스템 · 타입 · 그리드 토큰
  css/home.css      Home 섹션 스타일
  css/case.css      케이스 상세 스타일
  js/home.js        Work 렌더링 · KV 프리뷰 · 히어로 영상 · 에셋 감사
  js/case.js        케이스 리빌 · KV 폴백 · Draft 표시
  assets/
    kv/manifest.json   Work 인덱스의 단일 소스 (page 필드가 케이스 링크)
    video/             background.mp4 (fetch-hero.sh 로 받는다)
    README.md          에셋 드롭인 가이드
    fetch-hero.sh      히어로 영상 다운로드 + 웹 인코딩
    collect-kv.sh      썸네일 목록 확인 · 슬롯 채우기
```

`drafts/` 는 배포 트리 밖이다 — 인덱스에서 내렸지만 보존하는 케이스 초안이 들어간다.

- **카페24 HTTPS 현황과 해결책**: [`docs/CAFE24-HTTPS.md`](docs/CAFE24-HTTPS.md)
  (인증서 ✅ / http→https 자동전환 ❌ — 서버가 nginx 라 `.htaccess` 가 안 먹는다)
- 배포 전반 · 호스팅 선택: [`docs/DEPLOY.md`](docs/DEPLOY.md)
- 설계 판단과 남은 결정: [`docs/HOME-V2.md`](docs/HOME-V2.md)
- 케이스 페이지 추가법 · 카피 원칙 · 컴플라이언스: [`docs/CASE-PAGES.md`](docs/CASE-PAGES.md)
- 에셋 채우는 법: [`site/assets/README.md`](site/assets/README.md)
