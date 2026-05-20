# Skill — Source Recon

Use before every code change.

## Steps

1. Search routes/endpoints.
2. Search model/table/migration.
3. Search service/action/controller.
4. Search frontend feature/API client.
5. Search tests.
6. Read docs if present.
7. Identify current behavior before proposing new behavior.

## Output to keep in working notes

- Affected files.
- Current flow.
- Risky dependencies.
- Minimal safe change.
- Tests/build to run.

## Checklist

- [ ] I know the current route/component/model involved.
- [ ] I know whether this is tenant/event scoped.
- [ ] I know whether there is a legacy API response contract.
- [ ] I know the tests or build command to run.
