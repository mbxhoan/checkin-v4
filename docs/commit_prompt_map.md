# Commit Prompt Map

File này lưu mapping giữa prompt/user request và commit message để truy vết.

## Format

```md
## YYYY-MM-DD HH:mm — <short title>

- Prompt summary:
- Ticket/Issue ID:
- Scope:
- Main files changed:
- Tests run:
- Commit message: `<type>(<scope>): <summary>`
- Notes/Risks:
```

---

## 2026-05-20 00:00 — Khởi tạo bộ agent workspace Check-in V4

- Prompt summary: Tách file `AGENTS.md` quá dài thành bộ file gọn hơn gồm entrypoint `AGENTS.md`, rules và skills liên quan cho workspace Check-in V4 với `/api`, `/web`, `/docs`.
- Ticket/Issue ID: N/A
- Scope: `/docs` workspace agent rules only; không đổi source `/api` hoặc `/web`.
- Main files changed:
  - `AGENTS.md`
  - `README.md`
  - `rules/00-core.md`
  - `rules/01-workspace.md`
  - `rules/02-api.md`
  - `rules/03-web.md`
  - `rules/04-security-and-data.md`
  - `rules/05-testing-and-done.md`
  - `rules/06-commit-prompt-map.md`
  - `skills/00-index.md`
  - `skills/source-recon.md`
  - `skills/api-development.md`
  - `skills/database-change.md`
  - `skills/frontend-feature.md`
  - `skills/checkin-safety.md`
  - `skills/email-deliverability.md`
  - `skills/payment-safety.md`
  - `skills/import-export.md`
  - `skills/docs-as-code.md`
  - `docs/commit_prompt_map.md`
- Tests run:
  - Chưa chạy lệnh verify trong repo thực tế vì đây là bộ file markdown được tạo ngoài workspace source.
- Commit message: `docs(workspace): split agent guide into rules and skills`
- Notes/Risks:
  - Cần copy toàn bộ folder vào root repo `checkin-v4` và merge thủ công nếu repo đã có `docs/commit_prompt_map.md` để tránh mất lịch sử cũ.
```
