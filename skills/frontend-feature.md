# Skill — Frontend Feature

Use for `/web` UI work.

## Steps

1. Read feature folder and route/page structure.
2. Read existing UI pattern/components.
3. Add/update API client and types.
4. Add/update page/component.
5. Add loading/error/empty states.
6. Add form validation.
7. Add permission-based UI guards.
8. Test build/typecheck/lint.
9. Update docs if behavior changes.
10. Update `docs/commit_prompt_map.md`.

## UX Requirements

Every async action needs:

- loading state.
- disabled duplicate submit.
- success feedback.
- actionable error feedback.
- retry path where appropriate.

## Admin Copy

- Vietnamese by default.
- Avoid mixed English/Vietnamese labels.
- Keep wording concise and operator-friendly.

## Verify

- [ ] Page loads.
- [ ] Empty state works.
- [ ] Loading state works.
- [ ] Error state works.
- [ ] Form validation works.
- [ ] Permission guard works.
- [ ] Build/typecheck passes.
