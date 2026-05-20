# Skill — Docs-as-Code

Use whenever behavior, API contract, DB schema, permissions or release process changes.

## Update Targets

| Change type | Docs to update |
|---|---|
| API behavior | `docs/api.md` |
| Product/business rule | `docs/brd.md` |
| DB schema/index/enum | `docs/database_schema.md` |
| Permission/RBAC | `docs/permission_matrix.md` |
| Operational/release | `docs/release_checklist.md` |
| Every task | `docs/commit_prompt_map.md` |

## Rules

- Keep docs in Vietnamese unless code/API identifiers require English.
- Use tables for contracts/matrices.
- Use Mermaid for flows/architecture where useful.
- Mention backward compatibility and migration notes.
- Do not put secrets in docs.

## Commit Prompt Map

Every implementation task must add a new entry in `docs/commit_prompt_map.md` matching final Done response.
