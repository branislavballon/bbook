# 10 — Paginate the Feed and Find People

**Status:** ready-for-agent

**Blocked by:** 06 — Posts are visible to Friends only.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

The two lists that grow without bound — the Feed and Find People — page ten at a time instead of rendering everything. Nothing else paginates: Friends, Requests and Comments are naturally small at this scale, and paginating them is ceremony.

Offset pagination, deliberately. Its known flaw — a Post created between page views shifting the boundary and repeating an item — is accepted at demo scale; cursor pagination's need for a tiebreaker on identical timestamps is a real trap with factory-seeded data.

## Acceptance criteria

- [ ] The Feed returns ten Posts per page, newest first, with the visibility scope still applied.
- [ ] Find People returns ten people per page.
- [ ] One reusable paginator component serves both.
- [ ] Pagination state lives in the URL, so a page is linkable and the back button works.
- [ ] Counts and eager loading still apply on paginated queries — pagination must not reintroduce a query per row.
- [ ] Friends, Requests and Comments remain unpaginated.
- [ ] Test: the Feed returns the first ten Posts, and the eleventh appears on the second page.
