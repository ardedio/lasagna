#!/usr/bin/env bash
# 히어로 영상을 웹용으로 준비한다. Mac 에서 실행.
#
#   ./fetch-hero.sh ~/Downloads/background.mp4     ← 파일이 이미 있을 때 (권장)
#   ./fetch-hero.sh                                ← 사이트에서 내려받기
#   ./fetch-hero.sh https://.../background.mp4     ← 다른 주소에서
#
# 참고: 원본은 이미 서버 /www/background.mp4 에 올라가 있다.
#       파일질라로 Mac 에 끌어다 놓고 그 경로를 넘기는 게 가장 빠르다.
set -euo pipefail

SRC="${1:-https://www.lasagna.kr/background.mp4}"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/video"
mkdir -p "$DIR"

command -v ffmpeg >/dev/null || { echo "ffmpeg 필요: brew install ffmpeg"; exit 1; }

RAW="$DIR/_raw.mp4"

if [ -f "$SRC" ]; then
  # 로컬 파일 — 원본을 건드리지 않도록 복사해서 작업한다.
  echo "→ 로컬 파일 사용: $SRC"
  cp "$SRC" "$RAW"
else
  case "$SRC" in
    http://*|https://*) ;;
    *) echo "파일을 찾을 수 없다: $SRC"; exit 1 ;;
  esac
  echo "→ 다운로드: $SRC"
  if ! curl -fL --retry 3 --retry-delay 2 -o "$RAW" "$SRC"; then
    # 인증서가 붙기 전이라면 HTTPS 가 실패할 수 있다. 한 번만, 알리고 폴백.
    FALLBACK="${SRC/https:\/\//http:\/\/}"
    if [ "$FALLBACK" != "$SRC" ]; then
      echo "  HTTPS 실패 — HTTP 로 재시도: $FALLBACK"
      curl -fL --retry 3 --retry-delay 2 -o "$RAW" "$FALLBACK"
    else
      exit 1
    fi
  fi
fi

echo "  원본 $(du -h "$RAW" | cut -f1)"
echo "→ 웹용 인코딩 (무음 · 1080p · 8초 · faststart)"
ffmpeg -y -loglevel error -i "$RAW" \
  -an -t 8 \
  -vf "scale=-2:1080,fps=30" \
  -c:v libx264 -profile:v high -crf 26 -preset slow \
  -pix_fmt yuv420p -movflags +faststart \
  "$DIR/background.mp4"

echo "→ 포스터 프레임 추출"
ffmpeg -y -loglevel error -i "$DIR/background.mp4" -frames:v 1 -q:v 4 "$DIR/background-poster.jpg"

rm -f "$RAW"
echo
echo "✓ $(du -h "$DIR/background.mp4" | cut -f1)  site/assets/video/background.mp4"
echo "✓ $(du -h "$DIR/background-poster.jpg" | cut -f1)  site/assets/video/background-poster.jpg"
echo
echo "  저채도는 CSS 가 처리한다 — 소스를 그레이딩하지 마라."
echo "  2MB 를 넘으면 스크립트의 -crf 26 을 28~30 으로 올려서 다시 실행."
