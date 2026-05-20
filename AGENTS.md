# AGENTS.md — Check-in V4 Workspace Guide

> Root instruction file for the `checkin-v4` workspace.  
> Keep this file short. Detailed rules and skills live in `/rules` and `/skills`.

## Workspace

```txt
checkin-v4/
  AGENTS.md
  api/      # Laravel API backend
  web/      # Web admin/scanner/portal frontend
  docs/     # Product, API, DB, release and prompt trace docs
  rules/    # Mandatory working rules
  skills/   # Task playbooks for agent/dev workflow
```

## Always Read First

Before editing code, read these files in order:

1. [`rules/00-core.md`](rules/00-core.md)
2. [`rules/01-workspace.md`](rules/01-workspace.md)
3. Relevant stack rule:
   - API/backend: [`rules/02-api.md`](rules/02-api.md)
   - Web/frontend: [`rules/03-web.md`](rules/03-web.md)
4. Security-sensitive work: [`rules/04-security-and-data.md`](rules/04-security-and-data.md)
5. Done format: [`rules/05-testing-and-done.md`](rules/05-testing-and-done.md)
6. Prompt trace format: [`rules/06-commit-prompt-map.md`](rules/06-commit-prompt-map.md)

## Skill Index

Use [`skills/00-index.md`](skills/00-index.md) to choose the right playbook.

Common skills:

- Source reconnaissance: [`skills/source-recon.md`](skills/source-recon.md)
- API/backend work: [`skills/api-development.md`](skills/api-development.md)
- Database changes: [`skills/database-change.md`](skills/database-change.md)
- Frontend/web work: [`skills/frontend-feature.md`](skills/frontend-feature.md)
- Check-in safety: [`skills/checkin-safety.md`](skills/checkin-safety.md)
- Email deliverability: [`skills/email-deliverability.md`](skills/email-deliverability.md)
- Payment safety: [`skills/payment-safety.md`](skills/payment-safety.md)
- Import/export: [`skills/import-export.md`](skills/import-export.md)
- Docs-as-code: [`skills/docs-as-code.md`](skills/docs-as-code.md)

## Non-Negotiables

- Không đoán khi có thể đọc source.
- Không sửa DB/API contract âm thầm.
- Không bỏ qua tenant/event boundary.
- Không hard-code secret, token, production key.
- Không phá legacy endpoint/response nếu chưa có migration plan.
- Luôn chạy verify phù hợp hoặc ghi rõ lý do chưa chạy được.
- Luôn cập nhật `docs/commit_prompt_map.md` sau mỗi task.
- Final response phải dùng đúng `## Done` format.

## Required Final Response

Every implementation task must end with:

```md
## Done

### File đã sửa
- `path` — mô tả.

### Lệnh đã chạy
- `command`

### Rủi ro còn lại
- ...

### Checklist verify
- [x] ...

### Commit message
`type(scope): summary`

### Entry trong `docs/commit_prompt_map.md`
```md
...
```
```
