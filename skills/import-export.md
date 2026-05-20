# Skill — Import / Export

Use when touching Excel/CSV import, attendee import, report export, invitation ZIP generation or bulk processing.

## Import Rules

- Validate file type/size.
- Parse in chunks for large files.
- Validate headers.
- Validate row-level data.
- Return row-level errors.
- Do not mutate failed rows.
- Use queue for heavy import.
- Keep import history/status.

## Export Rules

- Use queue for large export.
- Avoid loading huge dataset in memory.
- Apply tenant/event filters.
- Respect permissions.
- Include generated file status and expiry if applicable.
- Avoid exposing private file URLs without authorization.

## Suggested Tests

- Valid file success.
- Invalid file type rejected.
- Missing header rejected.
- Row-level validation errors returned.
- Partial success behavior if supported.
- Large file path uses queue.
- Export respects tenant/event scope.
