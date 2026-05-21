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

## 2026-05-20 17:34 — Hoàn tất nền API và shim legacy core

- Prompt summary: Dựa trên `docs/implementation_plan.md` kiểm tra thay đổi hiện có và triển khai nốt nền API Laravel gồm auth/RBAC/multi-tenant/check-in/report/audit log, đồng thời giữ dual support cho legacy core shim.
- Ticket/Issue ID: N/A
- Scope: `/api` và `/docs`; hoàn tất runtime/API contract/test cho nền backend mới và shim legacy core, không đổi `/web`.
- Main files changed:
  - `api/routes/api.php`
  - `api/bootstrap/app.php`
  - `api/app/Http/Controllers/Api/V1/*`
  - `api/app/Http/Requests/Api/V1/*`
  - `api/app/Http/Resources/*`
  - `api/app/Policies/*`
  - `api/app/Services/*`
  - `api/tests/Feature/Api/V1/*`
  - `api/resources/views/scribe/*`
  - `api/public/vendor/scribe/*`
  - `docs/commit_prompt_map.md`
- Tests run:
  - `cd api && find app routes database -name '*.php' -print0 | xargs -0 -n1 php -l`
  - `cd api && php artisan route:list --path=api/v1`
  - `cd api && php artisan migrate:fresh --seed`
  - `cd api && php artisan scribe:generate`
  - `cd api && ./vendor/bin/pint`
  - `cd api && ./vendor/bin/pint --test`
  - `cd api && php artisan test`
- Commit message: `feat(api): complete foundation routes and legacy core shims`
- Notes/Risks:
  - Legacy shim hiện giữ transport/envelope và map field cốt lõi; không tái tạo toàn bộ special-case behavior từ `fullstack-old-version`.
  - `clients/find` và `clients/register` của legacy shim đang được giữ public theo hướng tương thích tối thiểu; nếu cần siết auth giống middleware custom cũ thì nên tách thành follow-up security pass.

## 2026-05-21 09:16 — Chuẩn hóa README cho API

- Prompt summary: Cập nhật lại `api/README.md` cho chuẩn chỉnh và chuyên nghiệp sau quá trình thay đổi foundation API.
- Ticket/Issue ID: N/A
- Scope: `/api` docs only; viết lại README để phản ánh chính xác setup, runtime, seed credentials, docs UI, route groups và lưu ý legacy shim.
- Main files changed:
  - `api/README.md`
  - `docs/commit_prompt_map.md`
- Tests run:
  - `cd api && php artisan route:list --path=api/v1`
  - `cd api && php artisan about`
- Commit message: `docs(api): rewrite readme for foundation backend`
- Notes/Risks:
  - README phản ánh trạng thái runtime và route surface hiện tại; nếu thay đổi auth/seed/docs route sau này cần cập nhật lại.
  - Chưa chạy markdown linter vì repo hiện không có công cụ lint markdown sẵn trong workspace.
