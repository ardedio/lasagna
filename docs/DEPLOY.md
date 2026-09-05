# 배포 · HTTPS

## 문제

`www.lasagna.kr` 이 **HTTP 로만 서빙**된다. 브라우저 주소창에 `안전하지 않음`.

확인 (2026-09-05, 이 세션에서는 도메인 접근이 차단돼 브라우저 기준으로만 확인됨):
현재 사이트는 TLS 인증서 없이 평문 HTTP 로 응답한다.

## 왜 지금 고쳐야 하나

체감상 "자물쇠 아이콘" 문제로 보이지만 실제 손해는 더 크다.

1. **신뢰.** 크리에이티브 스튜디오 사이트에 `안전하지 않음` 이 붙으면,
   그 자체가 첫 인상이다. 클라이언트가 제안서를 받기 전에 보는 화면이다.
2. **검색.** Google 은 HTTPS 를 랭킹 신호로 쓴다. HTTP 사이트는 불리하다.
3. **혼합 콘텐츠.** HTTPS 로 바꾸고 나면 페이지 안의 `http://` 리소스는
   **브라우저가 차단한다.** 영상·이미지가 조용히 안 나온다.
4. **폼·메일 링크.** 문의를 받는 순간 평문 전송은 방어가 안 된다.

## 코드 쪽은 이미 정리했다

이 저장소의 `http://` 참조는 전부 `https://` 로 바꿨다
(`fetch-hero.sh`, `site/assets/README.md`, `docs/HOME-V2.md`).

프로토타입은 **혼합 콘텐츠 위험이 없다** — 히어로 영상을 원격 URL 로 물지 않고
`site/assets/video/background.mp4` 로 **로컬에 두고 참조**한다. 외부 CDN·웹폰트도
쓰지 않는다(시스템 폰트). 그래서 HTTPS 로 올리면 그대로 잠긴다.

> `fetch-hero.sh` 는 HTTPS 로 먼저 시도하고, 인증서가 아직 없으면 HTTP 로 한 번
> 폴백한다. 인증서가 붙으면 그 폴백은 자동으로 안 쓰인다.

## 고치는 법 — 호스팅에 달렸다

**어디에 올라가 있는지 알려주면 정확한 절차를 준다.** 케이스별로:

| 호스팅 | 조치 |
|---|---|
| 카페24 · 가비아 · 후이즈 등 국내 호스팅 | 관리 콘솔에서 **무료 SSL(Let's Encrypt)** 신청 → 적용 후 HTTP→HTTPS 리다이렉트 켜기 |
| Cloudflare 를 이미 쓰는 경우 | SSL/TLS 모드를 **Full (strict)** 로, `Always Use HTTPS` 켜기 |
| 자체 서버 (nginx/Apache) | `certbot --nginx -d lasagna.kr -d www.lasagna.kr` → 자동 갱신 확인 |
| Vercel · Netlify · Cloudflare Pages | 도메인 연결만 하면 **인증서 자동 발급** |

### 권장 — 정적 호스팅으로 옮기기

지금 사이트를 이 저장소 기준으로 다시 만들고 있으니, 이참에 옮기는 게 깔끔하다.
`site/` 는 **빌드 과정이 없는 순수 정적 파일**이라 아래 어디든 그대로 올라간다:

- **Cloudflare Pages** — 무료, 자동 HTTPS, 국내 속도 양호
- **Vercel** / **Netlify** — 무료, 자동 HTTPS, GitHub 연동 시 푸시하면 배포

셋 다 **인증서 발급·갱신을 알아서 한다.** 갱신 만료로 사이트가 죽는 사고가 없다.
빌드 설정도 필요 없다 — 출력 디렉터리만 `site` 로 지정하면 끝.

### 인증서 붙인 뒤 반드시 할 것

1. **HTTP → HTTPS 301 리다이렉트.** 인증서만 붙이고 리다이렉트를 안 걸면
   `http://` 주소가 그대로 살아 있어서 문제가 반만 해결된다.
2. **HSTS 헤더** — `Strict-Transport-Security: max-age=31536000; includeSubDomains`
   (리다이렉트가 정상 동작하는 걸 확인한 뒤에 켠다. 잘못 켜면 되돌리기 어렵다.)
3. **혼합 콘텐츠 점검** — 브라우저 콘솔에 `Mixed Content` 경고가 없는지.
4. **apex ↔ www 정리** — `lasagna.kr` 과 `www.lasagna.kr` 중 하나를 정본으로 정하고
   나머지는 리다이렉트. 둘 다 인증서에 포함돼야 한다.

### 확인 명령

```bash
# 인증서 발급 여부와 만료일
echo | openssl s_client -connect www.lasagna.kr:443 -servername www.lasagna.kr 2>/dev/null \
  | openssl x509 -noout -subject -dates

# HTTP 가 HTTPS 로 넘어가는지 (301 + Location: https:// 가 떠야 정상)
curl -sI http://www.lasagna.kr/ | head -5
```
