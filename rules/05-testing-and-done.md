# Rules — Testing, Verification & Done Format

## 1. Verification Commands

Inspect `composer.json`, `package.json`, lockfiles and CI config first. Use actual package manager in repo.

### API

```bash
cd api && composer install
cd api && php artisan test
cd api && php artisan test --filter=<TestName>
cd api && php artisan route:list
cd api && php artisan migrate --pretend
cd api && php artisan config:clear
cd api && php artisan cache:clear
cd api && php artisan queue:work --once
```

If available:

```bash
cd api && ./vendor/bin/pint --test
cd api && ./vendor/bin/phpstan analyse
cd api && ./vendor/bin/pest
```

### Web

```bash
cd web && npm install
cd web && npm run lint
cd web && npm run typecheck
cd web && npm run test
cd web && npm run build
```

or Yarn:

```bash
cd web && yarn install
cd web && yarn lint
cd web && yarn typecheck
cd web && yarn test
cd web && yarn build
```

### Docs

```bash
npx markdownlint-cli2 "**/*.md"
```

If no markdown linter exists, manually verify headings, code fences and links.

## 2. Definition of Done

A task is done only when:

- Code implements requested behavior.
- Scope is controlled and no unrelated refactor is included.
- Tests/build/lint relevant to changed area are run or reason is documented.
- Docs are updated when behavior/schema/API/permission changes.
- `docs/commit_prompt_map.md` has a new entry.
- Final response follows the required Done format.

## 3. Required Done Format

```md
## Done

### File đã sửa
- `path/to/file.ext` — mô tả ngắn thay đổi.

### Lệnh đã chạy
- `command`

### Rủi ro còn lại
- Mô tả rủi ro hoặc `Không ghi nhận rủi ro đáng kể trong phạm vi thay đổi.`

### Checklist verify
- [x] Đã kiểm tra scope thay đổi.
- [x] Đã chạy test/build phù hợp.
- [x] Đã cập nhật docs nếu cần.
- [x] Đã cập nhật `docs/commit_prompt_map.md`.

### Commit message
`type(scope): summary`

### Entry trong `docs/commit_prompt_map.md`
```md
## YYYY-MM-DD HH:mm — <short title>

- Prompt summary:
- Ticket/Issue ID:
- Scope:
- Main files changed:
  - `path/to/file.ext`
- Tests run:
  - `command`
- Commit message: `type(scope): summary`
- Notes/Risks:
```
```

If no command could be run:

```md
### Lệnh đã chạy
- Chưa chạy được lệnh verify vì: <reason>.
```

Do not hide failed commands.

## 4. Agent Self-Check

Before final response:

- [ ] Did I modify only requested scope?
- [ ] Did I inspect existing source/docs first?
- [ ] Did I preserve tenant/event boundary?
- [ ] Did I avoid leaking secrets?
- [ ] Did I run relevant command or explain why not?
- [ ] Did I update docs if contract/behavior changed?
- [ ] Did I update `docs/commit_prompt_map.md`?
- [ ] Did I provide exactly one commit message?
- [ ] Did I list remaining risks honestly?
