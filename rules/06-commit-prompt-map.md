# Rules — Commit Prompt Map

`docs/commit_prompt_map.md` is mandatory for every implementation task.

## 1. File Purpose

This file maps user prompt/request to implementation scope and commit message so the project can trace why a change exists.

## 2. Required Format

```md
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
```

## 3. Entry Rules

- Time should use local project timezone if known; otherwise current system time.
- Short title must be Vietnamese and specific.
- Prompt summary must paraphrase the user request.
- Scope must clearly state `/api`, `/web`, `/docs` or combined.
- Main files changed must include `docs/commit_prompt_map.md`.
- Tests run must list actual commands, not intended commands.
- Commit message must match final response commit message exactly.
- Notes/Risks must mention breaking changes, DB/API contract changes or remaining verification gaps.

## 4. Commit Message Rules

Use Conventional Commits:

```txt
<type>(<scope>): <summary>
```

Allowed types:

- `feat`
- `fix`
- `refactor`
- `perf`
- `test`
- `docs`
- `chore`
- `style`
- `revert`

Recommended scopes:

- `api`
- `web`
- `auth`
- `event`
- `client`
- `checkin`
- `landing-page`
- `email`
- `payment`
- `report`
- `upload`
- `rbac`
- `docs`

Examples:

```txt
feat(checkin): add offline sync idempotency guard
fix(email): add qr text fallback for outlook templates
refactor(client): move registration upsert logic into service
docs(workspace): split agent guide into rules and skills
```
