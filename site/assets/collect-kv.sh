#!/usr/bin/env bash
# Key-visual helper. Run on the Mac — the cloud session cannot see ~/Downloads.
#
#   ./collect-kv.sh                     원본 폴더의 프로젝트 목록
#   ./collect-kv.sh --find <검색어>       그 프로젝트의 이미지 후보 찾기
#   ./collect-kv.sh <파일> <id>          한 장을 kv-<id>.jpg 로 규격 맞춰 복사
#   ./collect-kv.sh --check             지금 채워진/빈 슬롯 확인
#
# 원본(Client 폴더)은 읽기만 한다. 사이트용 사본만 저장소에 들어간다.
#
# ids: manifest.json 의 items[].id  (leesangbong · lie · synopex · haruharu)
set -euo pipefail

# 원본 아카이브. 여기서 읽기만 하고 절대 수정하지 않는다.
SRC_DIR="${LE_KV_SRC:-$HOME/Lasagna/Lasagna_Business/Client}"
KV_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/kv"
MANIFEST="$KV_DIR/manifest.json"

check_src() {
  [ -d "$SRC_DIR" ] && return 0
  echo "원본 폴더를 찾지 못했다: $SRC_DIR"
  echo "다른 경로면:  LE_KV_SRC=/경로 ./collect-kv.sh"
  exit 1
}

list_source() {
  check_src
  echo "원본: $SRC_DIR"
  echo "(읽기 전용 — 이 폴더는 저장소에 들어가지 않는다)"
  echo
  echo "프로젝트 폴더:"
  find "$SRC_DIR" -mindepth 1 -maxdepth 1 -type d | sort | while read -r d; do
    n=$(find "$d" -type f \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.webp' \) 2>/dev/null | wc -l | tr -d ' ')
    printf "  %-48s 이미지 %s장\n" "$(basename "$d")" "$n"
  done
  echo
  echo "다음:  ./collect-kv.sh --find <프로젝트명 일부>"
}

find_images() {
  check_src
  local q="$1"
  echo "'$q' 검색 결과 — $SRC_DIR"
  echo
  find "$SRC_DIR" -type f \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.webp' \) \
    -ipath "*${q}*" 2>/dev/null | head -40 | while read -r f; do
      dim="?"
      command -v sips >/dev/null && dim="$(sips -g pixelWidth -g pixelHeight "$f" 2>/dev/null \
        | awk '/pixelWidth/{w=$2} /pixelHeight/{h=$2} END{if(w)printf "%sx%s", w, h}')"
      printf "  %-10s %s\n" "$dim" "${f#$SRC_DIR/}"
    done
  echo
  echo "쓸 컷을 정했으면:"
  echo "  ./collect-kv.sh \"<전체경로>\" <id>"
}

check_slots() {
  echo "슬롯 상태 — $KV_DIR"
  echo
  python3 - "$MANIFEST" "$KV_DIR" <<'PY'
import json, os, sys
manifest, kvdir = sys.argv[1], sys.argv[2]
d = json.load(open(manifest, encoding='utf-8'))
missing = 0
for i in d['items']:
    path = os.path.join(kvdir, i['file'])
    if os.path.exists(path):
        print(f"  [있음] {i['file']:26} {i['name']:14} {os.path.getsize(path)//1024} KB")
    else:
        missing += 1
        print(f"  [없음] {i['file']:26} {i['name']:14} placeholder 표시 중")
print()
print(f"  {len(d['items']) - missing}/{len(d['items'])} 채워짐")
PY
}

install_one() {
  local src="$1" id="$2"
  [ -f "$src" ] || src="$SRC_DIR/$src"
  [ -f "$src" ] || { echo "파일 없음: $1"; exit 1; }

  local out
  out="$(python3 - "$MANIFEST" "$id" <<'PY'
import json, sys
manifest, want = sys.argv[1], sys.argv[2]
d = json.load(open(manifest, encoding='utf-8'))
hit = next((i for i in d['items'] if i['id'] == want), None)
if hit:
    print(hit['file'])
else:
    ids = ', '.join(i['id'] for i in d['items'])
    back = ', '.join(i['id'] for i in d.get('_backlog', []))
    sys.stderr.write(f"인덱스에 없는 id: {want}\n")
    sys.stderr.write(f"  사용 가능: {ids}\n")
    if want in back.split(', '):
        sys.stderr.write(f"  '{want}' 은 _backlog 에 있다 — 먼저 items 로 옮겨야 한다.\n")
    sys.exit(1)
PY
  )" || exit 1

  out="$KV_DIR/$out"
  if command -v sips >/dev/null; then
    sips -s format jpeg -Z 1600 "$src" --out "$out" >/dev/null
  else
    cp "$src" "$out"
    echo "  (sips 없음 — 원본 그대로 복사, 크기 확인 필요)"
  fi
  echo "→ $out  ($(du -h "$out" | cut -f1))"
  echo "   manifest.json 에서 이 항목의 \"placeholder\" 를 false 로 바꾸면 끝."
}

case "${1:-}" in
  ""|--list) list_source ;;
  --find)    [ -n "${2:-}" ] || { echo "사용법: ./collect-kv.sh --find <검색어>"; exit 1; }
             find_images "$2" ;;
  --check)   check_slots ;;
  *)         [ $# -eq 2 ] || { echo "사용법: ./collect-kv.sh <파일> <id>"; exit 1; }
             install_one "$1" "$2" ;;
esac
