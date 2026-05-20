# Skill — Database Change

Use for migrations, indexes, enums, model relationships and data integrity.

## Steps

1. Inspect existing migrations.
2. Inspect model casts/fillable/relations.
3. Inspect current indexes and unique constraints.
4. Confirm table size/risk if known.
5. Design forward migration and rollback.
6. Add indexes intentionally.
7. Backfill safely if required.
8. Update seeders/factories if relevant.
9. Add tests for query/business behavior.
10. Update `docs/database_schema.md` if present.
11. Update `docs/commit_prompt_map.md`.

## Rules

- Avoid destructive migration without backup and explicit approval.
- Avoid adding non-null column to large table without default/backfill plan.
- Use event/tenant-scoped uniqueness where business requires it.
- Document new enum/status values.

## High-Value Indexes

Consider indexes for:

- `company_id`
- `event_id`
- `event_code`
- `qrcode`
- `email`
- `status`
- `scan_time`
- provider transaction references
- webhook message ids
