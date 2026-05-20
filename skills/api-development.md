# Skill — API Development

Use for new or changed backend endpoints.

## Steps

1. Read related routes/controllers/services.
2. Define or update route.
3. Add/update FormRequest validation.
4. Add/update policy/middleware.
5. Implement service/action.
6. Use transaction for multi-table mutation.
7. Return Resource/DTO/consistent JSON.
8. Add feature/unit tests.
9. Update API docs if contract changed.
10. Update `/web` API client/types if consumed by frontend.
11. Update `docs/commit_prompt_map.md`.

## Must Verify

- Auth and permission.
- Tenant/event boundary.
- Validation errors.
- Success response shape.
- Failure response shape.
- Backward compatibility.

## Suggested Tests

- Authorized success.
- Unauthorized.
- Forbidden wrong tenant/event.
- Validation failure.
- Not found.
- Business rule edge case.
