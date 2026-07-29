# 02 — Write and read your own Posts

**Status:** ready-for-agent

**Blocked by:** 01 — Feed route replaces dashboard.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person writes a Post from a composer at the top of their Feed and immediately sees it there. The Feed lists their own Posts newest-first, each showing its Author, its text with the line breaks they typed, when it was written expressed relatively, and its Like and Comment counts — both zero for now, because neither exists yet.

Friendship does not exist yet, so "own Posts only" is the complete, correct behaviour of the Feed at this point, not a placeholder.

## Migration rule

This ticket writes the first migration and sets the pattern every later one follows. Per [ADR-0002](../../adr/0002-hard-deletes-with-database-cascades.md), dependent rows are disposed of by database cascade — `cascadeOnDelete()` on the foreign key — not by model events, which do not fire for mass deletes. The foreign key must be declared **inside** the `Schema::create` that makes the table: SQLite cannot add a constraint afterwards without rebuilding the table, so a later "add the constraint" migration is not an option.

## Acceptance criteria

- [ ] A Post belongs to a non-nullable Author, holds plain text, and records when it was created.
- [ ] A composer sits at the top of the Feed; submitting it creates a Post and returns to the Feed with confirmation.
- [ ] Post text is validated by a Form Request — required, trimmed, capped at 1000 characters — never inline in the controller.
- [ ] Empty or whitespace-only text is rejected with a visible error.
- [ ] Line breaks in the text survive rendering.
- [ ] The Feed lists the person's own Posts, newest first.
- [ ] Like and Comment counts are read from the query via `withCount`, not stored on the Post.
- [ ] Author is eager-loaded wherever Posts are listed.
- [ ] A factory exists for Posts, usable with an explicit Author.
- [ ] `PostCard` and `UserAvatar` exist as reusable components from the start.
- [ ] Test: an authenticated person can create a Post, and it appears in their Feed.
