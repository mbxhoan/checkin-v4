# RAG Schema Update Workflow

This document explains how to keep DB artifacts up to date for AI RAG ingestion.

## Output Files

The generator updates:

- `docs/db-schema-rag.json` (full machine-readable schema)
- `docs/ERD.md` (human-readable ERD + table dictionary)
- `docs/schema-ddl.sql` (SQL DDL snapshot)
- `docs/rag-domains/event.json`
- `docs/rag-domains/campaign.json`
- `docs/rag-domains/lucky_draw.json`
- `docs/rag-domains/landing_page.json`
- `docs/rag-domains/print_card_label.json`
- `docs/rag-domains/shared_core.json`
- `docs/rag-domains/system_platform.json`

## When To Regenerate

Run regeneration whenever any of these change:

- New/updated/deleted migration in `database/migrations`
- Schema-impacting code updates (foreign key/index/table changes)
- New module/domain that introduces new tables

## One Command

```bash
python3 scripts/generate_db_rag_docs.py
```

## Recommended Validation

After generating, validate quickly:

```bash
git status --short docs scripts/generate_db_rag_docs.py
```

Optional sanity checks:

```bash
rg -n '^### `' docs/ERD.md | wc -l
```

```bash
ls -la docs/rag-domains
```

## RAG Ingestion Order (Recommended)

1. `docs/db-schema-rag.json` (global context)
2. `docs/rag-domains/*.json` (domain-specific retrieval)
3. `docs/schema-ddl.sql` (SQL-oriented reasoning)
4. `docs/ERD.md` (human narrative fallback)

## Notes

- Generator uses a temporary SQLite database built from top-level migrations.
- `database/migrations/tmp` is not included (same behavior as normal Laravel migration discovery).
- Temporary migration files are normalized only for SQLite index compatibility; source migrations are not modified.
