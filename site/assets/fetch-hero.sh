#!/usr/bin/env bash
# Downloads the studio background loop and prepares the web-ready hero asset.
# Run on the Mac (this repo's cloud session cannot reach www.lasagna.kr).
set -euo pipefail

SRC="${1:-https://www.lasagna.kr/background.mp4}"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/video"
mkdir -p "$DIR"

command -v ffmpeg >/dev/null || { echo "ffmpeg required: brew install ffmpeg"; exit 1; }

echo "→ downloading $SRC"
if ! curl -fL --retry 3 --retry-delay 2 -o "$DIR/_raw.mp4" "$SRC"; then
  # lasagna.kr currently serves over plain HTTP with no valid certificate, so
  # HTTPS can fail until hosting is fixed. Fall back once, loudly.
  FALLBACK="${SRC/https:\/\//http:\/\/}"
  if [ "$FALLBACK" != "$SRC" ]; then
    echo "  HTTPS 실패 — HTTP 로 재시도한다: $FALLBACK"
    echo "  (사이트에 인증서가 붙으면 이 폴백은 필요 없어진다. docs/DEPLOY.md 참고)"
    curl -fL --retry 3 --retry-delay 2 -o "$DIR/_raw.mp4" "$FALLBACK"
  else
    exit 1
  fi
fi

echo "→ encoding web loop (muted, 1080p, 8s, faststart)"
ffmpeg -y -loglevel error -i "$DIR/_raw.mp4" \
  -an -t 8 \
  -vf "scale=-2:1080,fps=30" \
  -c:v libx264 -profile:v high -crf 26 -preset slow \
  -pix_fmt yuv420p -movflags +faststart \
  "$DIR/background.mp4"

echo "→ extracting poster frame"
ffmpeg -y -loglevel error -i "$DIR/background.mp4" -frames:v 1 -q:v 4 "$DIR/background-poster.jpg"

rm -f "$DIR/_raw.mp4"
echo "✓ $(du -h "$DIR/background.mp4" | cut -f1)  $DIR/background.mp4"
echo "  Desaturation is applied in CSS — do not grade the source."
