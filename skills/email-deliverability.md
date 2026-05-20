# Skill — Email Deliverability

Use when touching email templates, campaigns, Postmark config, webhook, QR email or send queue.

## Rules

- Never hard-code Postmark token.
- Prefer queue-based sending.
- Log provider message id.
- Support webhook idempotency.
- Track bounce/spam complaint safely.
- Allow campaign throttling/hold time.
- Do not trust `opened` as proof human opened email.
- QR-friendly templates must include fallback text/link, not image-only QR.
- For enterprise recipients, minimize tracking footprint when needed.

## Template Requirements

- Table-based HTML for Outlook compatibility.
- No external font dependency unless tested.
- Clear text-first event info.
- QR image plus QR text/link fallback.
- Avoid marketing-heavy copy for transactional event invitations.

## Suggested Tests

- Campaign creates recipients correctly.
- Queue job sends one email idempotently.
- Webhook handles duplicate event.
- Bounce/spam status updates expected records.
- Template renders fallback QR text/link.

## Operational Notes

- Microsoft 365/Outlook may block external images by policy.
- Gmail Promotions is not spam, but can reduce visibility.
- Open tracking can be triggered by security gateway scanning.
