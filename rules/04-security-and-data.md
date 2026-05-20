# Rules — Security, Data Integrity & Sensitive Flows

## 1. Secret Handling

Never create or expose:

- Real `.env` with secrets.
- Postmark server token.
- OnePay hash secret / merchant secret.
- DB password.
- Laravel production `APP_KEY`.
- Access token/session token.
- Customer PII dumps.

Use placeholders:

```env
POSTMARK_TOKEN=__SET_IN_ENV__
ONEPAY_HASH_SECRET=__SET_IN_ENV__
DB_PASSWORD=__SET_IN_ENV__
```

If a secret is found in source:

1. Do not repeat it in response.
2. Remove from tracked code if scope allows.
3. Recommend rotating the secret.
4. Add note in risks.

## 2. Permission Matrix Baseline

| Action | System Admin | Company Admin | Manager | User | Scanner |
|---|---:|---:|---:|---:|---:|
| Manage tenants | yes | no | no | no | no |
| Manage company users | yes | yes | no/limited | no | no |
| Create event | yes | yes | yes/limited | no | no |
| Edit event | yes | yes | yes if assigned | no | no |
| Configure custom fields | yes | yes | yes if assigned | no | no |
| Import clients | yes | yes | yes if assigned | limited | no |
| Edit clients | yes | yes | yes if assigned | limited | no |
| Scan check-in | yes | yes | yes | yes if assigned | yes assigned event only |
| Send email campaign | yes | yes | yes if assigned | no | no |
| View reports | yes | yes | yes if assigned | limited | no |
| Manual payment approval | yes | yes with permission | no by default | no | no |
| Audit logs | yes | limited | no | no | no |

Always verify actual permissions in code.

## 3. Data Model Baseline

Core entities:

- `companys` / tenants.
- `users`.
- `events`.
- `clients`.
- `checkins`.
- `custom_field_templates`.
- `campaigns`.
- `campaign_details`.
- `emails`.
- `landing_pages`.
- `cards` / invitation cards.
- `labels`.
- Optional payment/ticket: `portal_users`, `registrations`, `orders`, `payment_attempts`, `registration_items`, `tickets`, `ticket_issuances`, `registration_files`.

High-risk constraints:

- QR uniqueness should be event-scoped.
- Payment transaction references must be globally unique.
- Webhook message IDs must be idempotent.
- File IDs/paths must be unique and non-guessable.

## 4. File Upload Rules

- Validate max size server-side.
- Validate mime and extension.
- Store outside public path unless public access is required.
- Generate random file id/path.
- Keep original name only as metadata.
- Add virus scanning hook if enterprise release.
- Use temp → active lifecycle for registration flows.
- Support replacement without orphaning old file.
- Do not process heavy image/PDF synchronously in request if avoidable.

## 5. Security Review Checklist

Before Done for sensitive task:

- [ ] Auth checked?
- [ ] Tenant/event boundary checked?
- [ ] Input validated?
- [ ] Output escaped?
- [ ] Secrets protected?
- [ ] Rate limit needed?
- [ ] Audit log needed?
- [ ] Idempotency needed?
- [ ] Permission test added?
