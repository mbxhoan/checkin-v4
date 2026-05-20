# Check-in V4 Agent Pack

Bộ file này tách `AGENTS.md` dài thành cấu trúc dễ maintain hơn.

## Cách dùng

Copy toàn bộ nội dung folder này vào root workspace `checkin-v4`:

```txt
checkin-v4/
  AGENTS.md
  rules/
  skills/
  docs/commit_prompt_map.md
```

Nếu repo đã có `docs/commit_prompt_map.md`, hãy merge phần format thay vì ghi đè lịch sử cũ.

## Cấu trúc

| File/Folder | Mục đích |
|---|---|
| `AGENTS.md` | Entry point ngắn, điều hướng sang rules/skills. |
| `rules/` | Các luật bắt buộc cho workspace, API, web, security, Done format. |
| `skills/` | Playbook theo loại task để agent/dev triển khai chắc chắn. |
| `docs/commit_prompt_map.md` | File truy vết prompt/user request → commit message. |
