# 06 — Posts are visible to Friends only

**Status:** resolved

**Blocked by:** 03 — Edit, delete, and open a Post; 05 — Respond to Friend Requests and see Friends.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

The Feed stops being own-Posts-only and becomes what it was always meant to be: the person's own Posts plus their Friends' Posts, newest first, with strangers' Posts absent. And a Post that is not Visible to someone cannot be reached at all — guessing its detail URL is refused, not merely unlinked.

This is the ticket where [ADR-0001](../../adr/0001-posts-are-visible-to-friends-only.md) becomes real. Visibility is written **once**, as a single reusable query scope meaning "Visible to this person", and applied everywhere Posts are read. Every later ticket depends on that scope existing and being the only expression of the rule.

## Acceptance criteria

- [x] One query scope expresses visibility: authored by the viewer, or authored by someone in an accepted Friendship with the viewer, matched on either side.
- [x] The rule appears in exactly one place; no screen re-derives it.
- [x] The Feed uses the scope and shows own plus Friends' Posts, newest first.
- [x] The Post detail lookup uses the scope; a Post that is not Visible is refused rather than rendered.
- [x] `PostPolicy` gains `view` alongside `update` and `delete`.
- [x] Test: the Feed contains the viewer's own Posts and their Friends' Posts, and excludes a stranger's.
- [x] Test: a person is refused the detail page of a Post authored by a stranger.
