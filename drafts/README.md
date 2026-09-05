# drafts/

**배포 트리 밖.** `site/` 만 서빙되므로 여기 있는 파일은 공개되지 않는다.

인덱스에서 내렸지만 작업물은 버리지 않은 케이스 초안을 둔다.
경로 깊이가 `site/work/` 와 같아서(`../css/…`) **되돌릴 때 그대로 옮기면 렌더링된다.**

```bash
mv drafts/work/biolin.html site/work/biolin.html
# 그리고 manifest.json 에서 해당 항목을 _backlog → items 로 옮기고
# "page": "work/biolin.html" 로 설정
```

| 파일 | 보류 사유 |
|---|---|
| `work/biolin.html` | 상표 클리어런스 전 · 런칭 전. 링크를 떼도 URL 로는 열리므로 배포 트리에서 뺐다. |
