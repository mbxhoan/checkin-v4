# Skill — Payment Safety

Use when touching orders, payment attempts, OnePay callback/IPN, cash/manual approval or paid status sync.

## Rules

- Never trust frontend payment status.
- Verify gateway signature/hash server-side.
- Verify amount, currency, merchant ref, order state.
- Use payment attempts and immutable logs.
- Make callback/IPN idempotent.
- Manual cash approval must require permission and audit log.
- Never mark paid without verified gateway success or authorized manual approval.
- Paid state transition must be transaction-safe.

## Suggested Tests

- Valid callback marks attempt/order paid.
- Invalid hash rejected.
- Wrong amount rejected.
- Wrong merchant/order ref rejected.
- Callback replay does not double-process.
- Manual approval requires permission.
- Manual approval writes audit log.

## Done Risk Notes Must Mention

- Whether payment gateway contract changed.
- Whether DB/payment state machine changed.
- Whether manual approval behavior changed.
- Whether callback replay/idempotency was verified.
