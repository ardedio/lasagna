# site/assets — 에셋 드롭인 가이드

이 프로토타입은 **에셋 없이도 렌더링**된다. 실제 파일을 아래 이름으로 떨어뜨리면
코드 수정 없이 자동으로 붙는다. 왼쪽 하단 주황 배지는 아직 비어 있는 슬롯을 알려주고,
다 채워지면 스스로 사라진다.

> 이 두 소스는 **클라우드 세션에서 접근 불가**라 실제 파일이 들어가지 않았다.
> `~/Downloads` 는 이 컨테이너에 없고, `www.lasagna.kr` 은 환경 egress 허용목록에 없다.
> 아래 절차는 김률 디렉터 Mac에서 실행하는 것을 전제로 한다.

---

## 1. 히어로 영상 — `video/background.mp4`

**원본은 이미 서버에 있다** — `/www/background.mp4` (18.5MB).
다운로드할 필요 없이 파일질라로 Mac 에 끌어다 놓고 그 경로를 넘기면 된다.

```bash
cd site/assets
./fetch-hero.sh ~/Downloads/background.mp4    # 로컬 파일에서 (권장)
./fetch-hero.sh                               # 또는 사이트에서 내려받기
```

두 경우 모두 웹용 재인코딩(무음·1080p·8초)과 포스터 추출까지 한 번에 한다.

수동으로 할 경우:

```bash
curl -L -o video/_raw.mp4 https://www.lasagna.kr/background.mp4

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

`kv/manifest.json` 의 `file` 이름으로 저장한다.

**지금 인덱스에 걸린 4건 — 이것만 채우면 된다:**

| 파일명 | 프로젝트 |
|---|---|
| `kv-leesangbong.jpg` | LIE SANGBONG (이상봉) |
| `kv-lie.jpg` | LIE (라이) |
| `kv-synopex.jpg` | SYNOPEX (시노펙스) |
| `kv-haruharu.jpg` | 하루하루 |

케이스 상세 페이지가 쓰는 추가 컷:

| 파일명 | 위치 |
|---|---|
| `kv-synopex-ep1.jpg` · `kv-synopex-ep3.jpg` | SYNOPEX 케이스 스트립 2컷 |

**백로그 5건** (BIOLIN · DAREFIT · TFIT · COCOSPACK · DSS GROUP) 은 자료정리 대기라
지금 넣을 필요 없다. `manifest.json` 의 `_backlog` 에 데이터가 그대로 남아 있으니,
인덱스로 되돌릴 때 `items` 로 옮기고 그때 `kv-<id>.jpg` 를 넣으면 된다.

스펙: **16:9 · 최소 1280×720 · JPG · 220KB 내외**

### 헬퍼 스크립트

원본 파일명을 몰라도 되도록 `collect-kv.sh` 를 붙였다.

```bash
cd site/assets
./collect-kv.sh                    # _yt_upload 폴더 내용 + 해상도 나열
./collect-kv.sh "<파일명>" synopex   # 한 장을 kv-synopex.jpg 로 규격 맞춰 복사
./collect-kv.sh --check            # 지금 몇 개 채워졌는지
```

폴더 경로가 다르면 `LE_KV_SRC=/경로 ./collect-kv.sh`.
`_backlog` 에 있는 프로젝트 id 는 거부한다 — 먼저 `items` 로 옮겨야 한다.

일괄 리사이즈 (직접 할 경우):

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
