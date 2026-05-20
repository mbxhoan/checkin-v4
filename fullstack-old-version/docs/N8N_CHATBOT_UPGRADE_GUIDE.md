# N8N Chatbot Upgrade Guide

## What Was Upgraded

### 1) Advanced reporting + export

The chatbot now supports richer report intents:

- Running events
- Clients by event
- Event overview (daily check-in + source distribution chart)
- Monthly statistics
- Yearly report with charts
- Top events by check-in
- Export latest report to CSV

Export flow is handled in backend and returns clickable actions in chat:

- `open_url` to download CSV
- `open_url` to download HTML report with charts
- `copy_text` for quick link copy
- `send_preset` for quick follow-up prompts

### 2) Issue reporting / support mode

A new chatbot mode was added: `SUPPORT`.

Capabilities:

- Create issue ticket directly from chat
- Auto-assign severity/category by message hints
- Return tracking code (format `INC-YYYYMMDD-XXXXXX`)
- Query ticket status by code
- Issue summary dashboard (status + severity charts)
- Auto-switch from `GUIDE` to `SUPPORT` when issue-like messages are detected

## Database Change

Run migration:

```bash
php artisan migrate
```

New table:

- `n8n_chat_issue_reports`

## n8n Workflow Change

In updated workflow JSON:

- Node `Respond Output` now returns full JSON payload (`={{ $json }}`), not only plain `output`.

Why:

- Laravel can now consume `meta/actions/charts` from n8n responses and render richer UI behavior.

## Expected n8n response format (recommended)

```json
{
  "output": "Markdown response...",
  "meta": {
    "intent": "guide",
    "scope": "company"
  },
  "actions": [
    { "action": "send_preset", "label": "Thử tiếp", "message": "..." },
    { "action": "open_url", "label": "Mở tài liệu", "url": "https://..." }
  ],
  "charts": []
}
```

## Frontend behavior now supported

The chatbot widget now supports these action types:

- `set_mode`
- `open_url`
- `copy_text`
- `send_preset`

## Notes

- Existing `GUIDE` and `REPORT` flows remain compatible.
- `REPORT` mode remains deterministic in backend for data safety and permission scope control.
- n8n still handles RAG guide flow.
