# Rules — Workspace & Product Context

## 1. Workspace Map

```txt
checkin-v4/
  AGENTS.md
  api/      # Laravel API backend
  web/      # Web admin/scanner/portal frontend
  docs/     # Documentation and prompt trace
  rules/    # Working rules
  skills/   # Playbooks
```

## 2. Product Domain

Check-in V4 là Event Management SaaS gồm:

- Multi-tenant company/event management.
- QR-based check-in/check-out.
- Public landing page registration.
- Client/attendee import and update.
- Email invitation/campaign with QR code.
- Event card/invitation generation.
- Reports and real-time dashboard.
- Scanner accounts for event operation.
- Optional ticket/payment/portal modules depending on release scope.

## 3. Critical Business Flows

1. **Event setup:** create event → configure custom fields → import/register attendees → generate QR → send invitation → scan check-in → report.
2. **Landing registration:** landing page registration → create/update client → generate QR → confirmation email → check-in.
3. **Email campaign:** create template → sync recipient list → queue sending → webhook tracking → report.
4. **Scanner:** scanner login → select event → scan QR → duplicate validation → success/fail screen.
5. **Payment optional:** order/payment attempt → verified paid/manual approved → ticket/QR issuance → check-in eligibility.

## 4. Service Boundary

| Area | Owns | Must not own |
|---|---|---|
| Auth | login, token, session, password, role helpers | event business rules |
| Tenant/Company | tenant config, limits, company settings | direct check-in mutation |
| Event | event metadata, event settings, custom fields | payment reconciliation |
| Client/Attendee | attendee profile, QR, custom fields | gateway callback validation |
| Check-in | scan validation, duplicate prevention, scan logs | email template editing |
| Email | templates, campaigns, queue, provider webhook | attendee source of truth beyond send status |
| Landing Page | public page content, public registration adapter | admin-only mutation without permission |
| Payment | orders, attempts, callback/IPN, manual approval | check-in scan history |
| Report | aggregates, export jobs | source-of-truth mutations |

## 5. Dependency Direction

```txt
Controller/Route
  -> Request Validator / DTO
  -> Policy / Permission
  -> Service / Action
  -> Repository / Query
  -> Model / Database
  -> Resource / Response DTO
```

## 6. Cross-Repo Contract Protocol

If `/api` request/response changes:

1. Update API code.
2. Update `/web` API client/types.
3. Update docs/API contract.
4. Add/update tests.
5. Mention breaking/non-breaking in Done.
6. Add entry in `docs/commit_prompt_map.md`.

## 7. Naming Rules

- UI copy: Vietnamese by default.
- Code identifiers/API fields: keep existing project style.
- API JSON: use existing snake_case unless contract says otherwise.
- Frontend internals: camelCase is acceptable, convert at boundary.
- Commit messages: English Conventional Commit.
