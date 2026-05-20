# Rules — `/web` Frontend/Admin/Scanner

## 1. Frontend Responsibilities

`/web` owns:

- Admin dashboard.
- Event management UI.
- Client/attendee import and management UI.
- Check-in scanner UI for PC/mobile/PDA.
- Landing page builder/preview if included.
- Email campaign UI/template editor.
- Reports and export UI.
- Portal registration/payment UI if included.
- Loading, error and empty states.

## 2. Coding Rules

- Use TypeScript strictly where possible.
- Prefer feature-based structure under `src/features`.
- Keep API client functions separated from UI components.
- Keep form schema/validation close to feature.
- Centralize status label mapping.
- Do not hard-code production URLs in components.
- Use accessible buttons, labels, loading and error states.

## 3. UX Rules

Every async action must have:

- Loading state.
- Disabled duplicate submit protection.
- Success feedback.
- Error feedback with actionable message.
- Retry path where appropriate.

Never let user think the app is frozen.

## 4. Scanner UI Rules

Scanner UI must prioritize speed and clarity:

- Big success/failure/duplicate states.
- Keyboard scanner support.
- Camera scanner support if enabled.
- Clear event selection state.
- Offline state indicator if supported.
- Do not block next scan with unnecessary modal.
- Keep last scan result visible enough for operator.

## 5. Admin UI Rules

- Vietnamese copy by default for operator-facing UI.
- Avoid mixed English/Vietnamese labels unless product term.
- Provide preview before destructive/bulk actions.
- Bulk send/import must require confirmation.
- Dangerous operations should require explicit confirmation text if applicable.

## 6. API Client Rules

- Centralize base URL and auth handling.
- Handle 401/403/404/422/500 distinctly.
- Never expose provider secrets in client bundle.
- Normalize legacy API response shape in API client layer if needed.
- Use typed request/response contracts.

## 7. Report UI Rules

- Use server-side pagination/filtering for large data.
- Export should be async if data is large.
- Show export job status.
- Avoid rendering huge tables all at once.
- Date filters must be timezone-aware.
