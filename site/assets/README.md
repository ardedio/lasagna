# site/assets — 에셋 드롭인 가이드

이 프로토타입은 **에셋 없이도 렌더링**된다. 실제 파일을 아래 이름으로 떨어뜨리면
코드 수정 없이 자동으로 붙는다. 왼쪽 하단 주황 배지는 아직 비어 있는 슬롯을 알려주고,
다 채워지면 스스로 사라진다.

> 이 두 소스는 **클라우드 세션에서 접근 불가**라 실제 파일이 들어가지 않았다.
> `~/Downloads` 는 이 컨테이너에 없고, `www.lasagna.kr` 은 환경 egress 허용목록에 없다.
> 아래 절차는 김률 디렉터 Mac에서 실행하는 것을 전제로 한다.

---

## 1. 히어로 영상 — `video/background.mp4`

원본: `http://www.lasagna.kr/background.mp4`

```bash
cd site/assets
./fetch-hero.sh          # 다운로드 + 웹용 재인코딩 + 포스터 추출
```

수동으로 할 경우:

```bash
curl -L -o video/_raw.mp4 http://www.lasagna.kr/background.mp4

# 웹 루프용: 무음, faststart, 1080p 상한, ~8초
ffmpeg -y -i video/_raw.mp4 \
  -an -t 8 \
  -vf "scale=-2:1080,fps=30" \
  -c:v libx264 -profile:v high -crf 26 -preset slow \
  -pix_fmt yuv420p -movflags +faststart \
  video/background.mp4

# 포스터 프레임 (영상 로드 전 표시)
ffmpeg -y -i video/background.mp4 -frames:v 1 -q:v 4 video/background-poster.jpg
```

**저채도는 CSS가 처리한다** — `home.css` 의
`filter: saturate(0.12) contrast(1.14) brightness(0.46)`.
소스는 그레이딩하지 말고 원본 그대로 넣는다. 톤 조정은 그 한 줄만 고치면 된다.

목표: **2MB 이하**. 넘으면 `-crf` 를 28~30으로 올린다.

---

## 2. 키비주얼 — `kv/*.jpg`

원본: `~/Downloads/Lasagna Film/_yt_upload/` 의 유튜브 썸네일.

`kv/manifest.json` 의 `file` 이름으로 저장한다:

| 파일명 | 프로젝트 |
|---|---|
| `kv-biolin.jpg` | BIOLIN |
| `kv-darefit.jpg` | DAREFIT |
| `kv-tfit.jpg` | TFIT |
| `kv-synopex.jpg` | SYNOPEX |
| `kv-lie.jpg` | LIE COLLECTION |
| `kv-cocospack.jpg` | COCOSPACK |
| `kv-dss.jpg` | DSS GROUP |

스펙: **16:9 · 최소 1280×720 · JPG · 220KB 내외**

일괄 리사이즈:

```bash
cd site/assets/kv
for f in *.jpg; do
  sips -Z 1280 "$f" --out "$f" >/dev/null   # macOS 내장
done
```

넣은 뒤 `manifest.json` 에서 해당 항목의 `"placeholder": true` → `false` 로 바꾼다.

`og.jpg` (1200×630) 도 같은 폴더에 넣으면 OG 카드에 잡힌다.

---

## 3. 확인

```bash
npx serve site        # 또는: python3 -m http.server -d site 8080
```

`file://` 로 직접 열면 `fetch()` 가 차단돼 Work 목록이 비어 보인다. 반드시 서버로 띄울 것.
