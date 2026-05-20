# Skill — Check-in Safety

Use when touching QR scan, check-in, check-out, scanner UI, duplicate detection or offline sync.

## Must Preserve

- QR validation.
- Event existence check.
- Tenant/event boundary.
- Scanner account permission/expiry.
- Duplicate handling.
- Accurate `scan_time`.
- Clear success/failure/duplicate message.
- PC/WebPortal vs PDA/Mobile behavior if code differs.

## Duplicate Prevention Cases

Consider:

- Same QR in same event.
- Same QR same day if per-day duplicate check is enabled.
- Same account/person if account-based duplicate check is enabled.
- Offline sync retry/replay.
- Bulk scan duplicates inside same payload.

## Suggested Tests

- Valid check-in.
- Invalid event.
- Invalid QR.
- QR not found.
- Duplicate QR blocked.
- Duplicate QR allowed when setting enables it.
- Unauthorized scanner.
- Expired/inactive scanner.
- Bulk partial success.
- Offline sync idempotency.

## UI Verify

- Operator can continue scanning fast.
- Success/fail state is visually clear.
- No modal blocks next scan unnecessarily.
- Last scan result remains visible.
