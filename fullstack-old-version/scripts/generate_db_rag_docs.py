#!/usr/bin/env python3
"""
Generate DB schema artifacts for RAG from Laravel migrations.

Outputs:
- docs/db-schema-rag.json
- docs/ERD.md
- docs/schema-ddl.sql
- docs/rag-domains/*.json
"""

from __future__ import annotations

import json
import os
import re
import shutil
import sqlite3
import subprocess
import tempfile
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path


ROOT_DIR = Path(__file__).resolve().parents[1]
MIGRATIONS_DIR = ROOT_DIR / "database" / "migrations"
DOCS_DIR = ROOT_DIR / "docs"
DOMAIN_DIR = DOCS_DIR / "rag-domains"


SYSTEM_TABLES = {
    "migrations",
    "jobs",
    "failed_jobs",
    "sessions",
    "password_resets",
    "personal_access_tokens",
    "telescope_entries",
    "telescope_entries_tags",
    "telescope_monitoring",
    "media",
    "media_libraries",
}


TABLE_DESCRIPTIONS = {
    "users": "System users and profile/access metadata.",
    "companys": "Organizations that own events and users.",
    "events": "Core event entity and runtime configuration.",
    "clients": "Attendees/participants linked to events.",
    "checkins": "Check-in logs per event and operator.",
    "campaigns": "Campaigns linked to events.",
    "campaign_details": "Detailed campaign configuration rows.",
    "emails": "Outbound email records for clients/campaigns.",
    "smss": "Outbound SMS records for clients/events.",
    "event_settings": "Hierarchical settings per event.",
    "language_defines": "Localized key/value translations by event.",
    "languages": "Language catalog.",
    "tickets": "Ticket definitions for events.",
    "client_tickets": "Client-ticket join records.",
    "orders": "Order records linked to clients.",
    "lucky_draws": "Lucky draw sessions per event.",
    "lucky_draw_rewards": "Lucky draw reward definitions.",
    "lucky_draw_clients": "Winners/participants in lucky draw runs.",
    "lucky_draw_layouts": "Lucky draw reward layout config.",
    "labels": "Label templates per event.",
    "label_details": "Label element details.",
    "print_devices": "Logical print devices mapped to labels/printers.",
    "printers": "Physical printer inventory per event.",
    "print_logs": "Printing audit logs.",
    "cards": "Card templates/definitions.",
    "card_details": "Card element details.",
    "landing_pages": "Landing page configuration per event.",
    "landing_page_campaigns": "Campaign blocks on landing pages.",
    "landing_page_cards": "Card/content blocks on landing pages.",
    "event_types": "Event type catalog.",
    "event_areas": "Event area catalog.",
    "countrys": "Country catalog.",
    "provinces": "Province/state catalog.",
    "custom_field_templates": "Template for dynamic form fields.",
    "webhook_postmarks": "Inbound Postmark webhook events.",
    "n8n_chat_sessions": "n8n chat sessions.",
    "n8n_chat_messages": "Messages inside n8n chat sessions.",
}


DOMAIN_SPECS = {
    "event": {
        "description": "Event operations and runtime management.",
        "tables": {
            "events",
            "event_settings",
            "event_files",
            "event_file_logs",
            "event_types",
            "event_areas",
            "provinces",
            "persons",
            "checkins",
            "export_datas",
            "impexp_files",
            "custom_field_templates",
            "summerizes",
            "page_access_logs",
        },
    },
    "campaign": {
        "description": "Campaign, communication, and outbound messaging.",
        "tables": {
            "campaigns",
            "campaign_details",
            "campaign_attachments",
            "tags",
            "emails",
            "smss",
            "email_templates",
            "webhook_postmarks",
            "api_client_logs",
            "newsletter_subscriptions",
            "historys",
        },
    },
    "lucky_draw": {
        "description": "Lucky draw flows, rewards, and winner tracking.",
        "tables": {
            "lucky_draws",
            "lucky_draw_rewards",
            "lucky_draw_clients",
            "lucky_draw_layouts",
        },
    },
    "landing_page": {
        "description": "Landing page builder and content blocks.",
        "tables": {
            "landing_pages",
            "landing_page_campaigns",
            "landing_page_cards",
            "audios",
        },
    },
    "print_card_label": {
        "description": "Printing, labels, cards, tickets, and ordering.",
        "tables": {
            "labels",
            "label_details",
            "printers",
            "print_devices",
            "print_logs",
            "cards",
            "card_details",
            "tickets",
            "client_tickets",
            "orders",
        },
    },
    "shared_core": {
        "description": "Shared master data and identity/access tables.",
        "tables": {
            "users",
            "roles",
            "role_user",
            "companys",
            "clients",
            "countrys",
            "languages",
            "language_defines",
            "settings",
            "packages",
            "client_backups",
            "n8n_chat_sessions",
            "n8n_chat_messages",
            "posts",
            "comments",
            "likes",
        },
    },
    "system_platform": {
        "description": "Framework/platform-level operational tables.",
        "tables": {
            "migrations",
            "jobs",
            "failed_jobs",
            "sessions",
            "password_resets",
            "personal_access_tokens",
            "media",
            "media_libraries",
            "telescope_entries",
            "telescope_entries_tags",
            "telescope_monitoring",
        },
    },
}


@dataclass
class GeneratedPaths:
    schema_json: Path
    erd_markdown: Path
    ddl_sql: Path
    domain_dir: Path


def utc_now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def run_command(cmd: list[str], env: dict[str, str] | None = None) -> None:
    subprocess.run(cmd, cwd=ROOT_DIR, check=True, env=env)


def copy_top_level_migrations(tmp_migrations: Path) -> None:
    tmp_migrations.mkdir(parents=True, exist_ok=True)
    for path in sorted(MIGRATIONS_DIR.glob("*.php")):
        shutil.copy2(path, tmp_migrations / path.name)


def patch_sqlite_compat(tmp_migrations: Path) -> None:
    # Patch problematic migration for SQLite: drop unique index before dropping column.
    target = tmp_migrations / "2019_12_14_000001_create_personal_access_tokens_table.php"
    if target.exists():
        text = target.read_text()
        old = """        Schema::table('users', function (Blueprint $table) {\n            $table->dropColumn('api_token');\n        });"""
        new = """        Schema::table('users', function (Blueprint $table) {\n            if (Schema::hasColumn('users', 'api_token')) {\n                try {\n                    $table->dropUnique('users_api_token_unique');\n                } catch (\\Throwable $e) {\n                }\n                $table->dropColumn('api_token');\n            }\n        });"""
        if old in text:
            target.write_text(text.replace(old, new))

    # Remove explicit index/unique names and index(id) lines to avoid SQLite name collisions.
    for path in sorted(tmp_migrations.glob("*.php")):
        text = path.read_text()
        text = re.sub(r"->index\((.+),\s*'[^']+'\)", r"->index(\1)", text)
        text = re.sub(r"->unique\((.+),\s*'[^']+'\)", r"->unique(\1)", text)
        lines = []
        for line in text.splitlines(keepends=True):
            if re.search(r"->index\(\s*'id'\s*\)", line):
                continue
            lines.append(line)
        path.write_text("".join(lines))


def build_schema_db(tmp_db_path: Path, tmp_migrations: Path) -> None:
    tmp_db_path.touch()
    env = {
        **dict(os.environ),
        "DB_CONNECTION": "sqlite",
        "DB_DATABASE": str(tmp_db_path),
    }
    run_command(
        [
            "php",
            "artisan",
            "migrate:fresh",
            "--force",
            "--path",
            str(tmp_migrations),
            "--realpath",
        ],
        env=env,
    )


def classify_table_kind(table: str) -> str:
    if table in SYSTEM_TABLES or table.startswith("telescope_"):
        return "system"
    return "domain"


def describe_table(table: str) -> str:
    if table in TABLE_DESCRIPTIONS:
        return TABLE_DESCRIPTIONS[table]
    if classify_table_kind(table) == "system":
        return f"System/platform table: {table.replace('_', ' ')}."
    return f"Business domain table: {table.replace('_', ' ')}."


def read_schema(db_path: Path) -> dict:
    conn = sqlite3.connect(str(db_path))
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()

    tables = [
        row["name"]
        for row in cur.execute(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        ).fetchall()
    ]

    schema = {
        "generated_at_utc": utc_now_iso(),
        "source": {
            "type": "sqlite_from_laravel_migrations",
            "database_path": "<temporary sqlite build>",
            "notes": [
                "Schema generated from top-level files in database/migrations.",
                "database/migrations/tmp is intentionally excluded (Laravel default behavior).",
                "Temporary migration copy was normalized for SQLite index compatibility only.",
            ],
        },
        "tables": [],
        "relationships": [],
    }

    for table_name in tables:
        column_rows = cur.execute(f'PRAGMA table_info("{table_name}")').fetchall()
        columns = [
            {
                "name": row["name"],
                "type": row["type"] if row["type"] else "UNKNOWN",
                "nullable": not bool(row["notnull"]),
                "default": row["dflt_value"],
                "is_primary_key": bool(row["pk"]),
            }
            for row in column_rows
        ]

        fk_rows = cur.execute(f'PRAGMA foreign_key_list("{table_name}")').fetchall()
        foreign_keys = []
        for row in fk_rows:
            fk = {
                "from_column": row["from"],
                "to_table": row["table"],
                "to_column": row["to"],
                "on_update": row["on_update"],
                "on_delete": row["on_delete"],
            }
            foreign_keys.append(fk)
            schema["relationships"].append({"from_table": table_name, **fk})

        index_rows = cur.execute(f'PRAGMA index_list("{table_name}")').fetchall()
        indexes = []
        for idx in index_rows:
            idx_name = idx["name"]
            idx_columns = [
                c["name"]
                for c in cur.execute(f'PRAGMA index_info("{idx_name}")').fetchall()
            ]
            indexes.append(
                {
                    "name": idx_name,
                    "is_unique": bool(idx["unique"]),
                    "columns": idx_columns,
                }
            )

        schema["tables"].append(
            {
                "name": table_name,
                "kind": classify_table_kind(table_name),
                "description": describe_table(table_name),
                "column_count": len(columns),
                "columns": columns,
                "foreign_keys": foreign_keys,
                "indexes": indexes,
            }
        )

    schema["stats"] = {
        "table_count": len(schema["tables"]),
        "relationship_count": len(schema["relationships"]),
        "column_count": sum(table["column_count"] for table in schema["tables"]),
    }
    conn.close()
    return schema


def write_schema_json(schema: dict, path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(schema, ensure_ascii=True, indent=2) + "\n")


def write_erd_markdown(schema: dict, path: Path) -> None:
    lines: list[str] = []
    lines.append("# Database ERD and Data Dictionary")
    lines.append("")
    lines.append("Generated from the latest migration state.")
    lines.append("")
    lines.append(f"- Generated at (UTC): `{schema['generated_at_utc']}`")
    lines.append(f"- Table count: `{schema['stats']['table_count']}`")
    lines.append(f"- Relationship count (FK): `{schema['stats']['relationship_count']}`")
    lines.append(f"- Column count: `{schema['stats']['column_count']}`")
    lines.append("")
    lines.append("## Mermaid ERD")
    lines.append("")
    lines.append("```mermaid")
    lines.append("erDiagram")

    for rel in sorted(
        schema["relationships"],
        key=lambda x: (x["to_table"], x["from_table"], x["from_column"]),
    ):
        parent = rel["to_table"].upper()
        child = rel["from_table"].upper()
        lines.append(f'  {parent} ||--o{{ {child} : "{rel["from_column"]}"')

    lines.append("```")
    lines.append("")
    lines.append("## Table Dictionary")
    lines.append("")

    for table in schema["tables"]:
        lines.append(f"### `{table['name']}`")
        lines.append(f"- Kind: `{table['kind']}`")
        lines.append(f"- Description: {table['description']}")
        lines.append(f"- Columns: `{table['column_count']}`")
        lines.append(f"- Foreign keys: `{len(table['foreign_keys'])}`")
        lines.append("")
        lines.append("| Column | Type | Nullable | Default | PK |")
        lines.append("|---|---|---|---|---|")
        for col in table["columns"]:
            default = "" if col["default"] is None else str(col["default"]).replace("|", "\\|")
            lines.append(
                f"| `{col['name']}` | `{col['type']}` | "
                f"{'YES' if col['nullable'] else 'NO'} | `{default}` | "
                f"{'YES' if col['is_primary_key'] else 'NO'} |"
            )

        if table["foreign_keys"]:
            lines.append("")
            lines.append("| FK Column | Ref Table | Ref Column | On Update | On Delete |")
            lines.append("|---|---|---|---|---|")
            for fk in table["foreign_keys"]:
                lines.append(
                    f"| `{fk['from_column']}` | `{fk['to_table']}` | `{fk['to_column']}` | "
                    f"`{fk['on_update']}` | `{fk['on_delete']}` |"
                )

        if table["indexes"]:
            lines.append("")
            lines.append("| Index | Unique | Columns |")
            lines.append("|---|---|---|")
            for idx in table["indexes"]:
                idx_cols = ", ".join(f"`{col}`" for col in idx["columns"])
                lines.append(
                    f"| `{idx['name']}` | "
                    f"{'YES' if idx['is_unique'] else 'NO'} | {idx_cols} |"
                )
        lines.append("")

    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text("\n".join(lines).rstrip() + "\n")


def write_schema_ddl(db_path: Path, path: Path) -> None:
    conn = sqlite3.connect(str(db_path))
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()

    table_rows = cur.execute(
        """
        SELECT name, sql
        FROM sqlite_master
        WHERE type='table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL
        ORDER BY name
        """
    ).fetchall()

    index_rows = cur.execute(
        """
        SELECT tbl_name, name, sql
        FROM sqlite_master
        WHERE type='index' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL
        ORDER BY tbl_name, name
        """
    ).fetchall()

    indexes_by_table: dict[str, list[sqlite3.Row]] = {}
    for row in index_rows:
        indexes_by_table.setdefault(row["tbl_name"], []).append(row)

    lines: list[str] = []
    lines.append("-- Auto-generated DDL from migration-built SQLite schema")
    lines.append(f"-- Generated at (UTC): {utc_now_iso()}")
    lines.append("")

    for row in table_rows:
        table_name = row["name"]
        lines.append(f"-- Table: {table_name}")
        sql = row["sql"].strip()
        lines.append(sql if sql.endswith(";") else f"{sql};")
        for idx_row in indexes_by_table.get(table_name, []):
            idx_sql = idx_row["sql"].strip()
            lines.append(idx_sql if idx_sql.endswith(";") else f"{idx_sql};")
        lines.append("")

    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text("\n".join(lines).rstrip() + "\n")
    conn.close()


def filter_domain(schema: dict, domain_name: str, table_names: set[str], description: str) -> dict:
    tables_by_name = {table["name"]: table for table in schema["tables"]}
    domain_tables = [tables_by_name[name] for name in sorted(table_names) if name in tables_by_name]
    domain_table_set = {table["name"] for table in domain_tables}

    relationships = [
        rel
        for rel in schema["relationships"]
        if rel["from_table"] in domain_table_set or rel["to_table"] in domain_table_set
    ]

    external_refs = sorted(
        {
            ref
            for rel in relationships
            for ref in (rel["from_table"], rel["to_table"])
            if ref not in domain_table_set
        }
    )

    return {
        "domain": domain_name,
        "description": description,
        "generated_at_utc": schema["generated_at_utc"],
        "source": schema["source"],
        "tables": domain_tables,
        "relationships": relationships,
        "external_reference_tables": external_refs,
        "stats": {
            "table_count": len(domain_tables),
            "relationship_count": len(relationships),
            "column_count": sum(table["column_count"] for table in domain_tables),
        },
    }


def write_domain_files(schema: dict, output_dir: Path) -> None:
    output_dir.mkdir(parents=True, exist_ok=True)
    for domain_name, spec in DOMAIN_SPECS.items():
        payload = filter_domain(
            schema=schema,
            domain_name=domain_name,
            table_names=set(spec["tables"]),
            description=spec["description"],
        )
        out_path = output_dir / f"{domain_name}.json"
        out_path.write_text(json.dumps(payload, ensure_ascii=True, indent=2) + "\n")


def generate() -> GeneratedPaths:
    with tempfile.TemporaryDirectory(prefix="checkin-rag-schema-") as tmp_dir:
        tmp_root = Path(tmp_dir)
        tmp_db = tmp_root / "schema.sqlite"
        tmp_migrations = tmp_root / "migrations"

        copy_top_level_migrations(tmp_migrations)
        patch_sqlite_compat(tmp_migrations)
        build_schema_db(tmp_db, tmp_migrations)

        schema = read_schema(tmp_db)

        schema_json = DOCS_DIR / "db-schema-rag.json"
        erd_markdown = DOCS_DIR / "ERD.md"
        ddl_sql = DOCS_DIR / "schema-ddl.sql"

        write_schema_json(schema, schema_json)
        write_erd_markdown(schema, erd_markdown)
        write_schema_ddl(tmp_db, ddl_sql)
        write_domain_files(schema, DOMAIN_DIR)

    return GeneratedPaths(
        schema_json=schema_json,
        erd_markdown=erd_markdown,
        ddl_sql=ddl_sql,
        domain_dir=DOMAIN_DIR,
    )


def main() -> None:
    paths = generate()
    print("Generated RAG DB artifacts:")
    print(f"- {paths.schema_json}")
    print(f"- {paths.erd_markdown}")
    print(f"- {paths.ddl_sql}")
    print(f"- {paths.domain_dir}")


if __name__ == "__main__":
    main()
