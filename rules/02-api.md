# Rules — `/api` Laravel Backend

## 1. Backend Responsibilities

`/api` owns:

- Authentication/session/token lifecycle.
- Tenant/company boundary.
- Event management.
- Client/attendee management.
- QR generation and check-in/check-out.
- Landing page API.
- Email campaign orchestration and Postmark webhook.
- Payment integration if retained in API service.
- File upload and storage policy.
- Queue jobs for import/export/email/report/QR generation.
- Audit logs and activity logs.

## 2. Coding Rules

- Follow Laravel conventions.
- Prefer FormRequest for validation.
- Prefer API Resources/DTOs for response shape.
- Use Service/Action classes for business logic.
- Use policies/middleware for authorization.
- Use DB transactions for multi-table mutations.
- Use queues for slow operations.
- Use enums/constants for statuses, types, provider names.
- Avoid raw SQL unless necessary; if used, bind parameters.

## 3. API Response Rules

For new APIs, prefer:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

For legacy endpoints that already return `status/msg/data`, do not break the response shape unless there is a migration plan.

## 4. Validation Rules

All write APIs must validate:

- Required fields.
- Type and format.
- Tenant/event ownership.
- Enum values.
- Unique constraints.
- File mime/size.
- Business state transition.
- Idempotency key when request can be retried.

## 5. Authorization Rules

Every protected endpoint must answer:

- Who is the actor?
- Which tenant/company is the actor in?
- Which event does the action target?
- Does actor have permission for that action and scope?
- Is the account/device active and not expired?

## 6. Tenant/Event Safety

Event-bound query must include event or tenant boundary.

```php
Client::query()
    ->where('event_id', $eventId)
    ->where('qrcode', $qrcode)
    ->first();
```

Do not lookup QR globally unless explicitly designed.

## 7. Queue/Job Rules

Use queue for:

- Email sending.
- Import CSV/Excel.
- Export report.
- QR/image/card generation.
- Payment sync side effects.
- Large webhook processing.

Jobs should be idempotent, retry-safe, logged with context, and scoped by tenant/event.

## 8. Logging Rules

Log:

- request/correlation id if available.
- actor id/type.
- tenant/company id.
- event id/code.
- entity id.
- action.
- provider message/payment id if relevant.

Never log password, token, payment secret, full sensitive payload, uploaded file content.
