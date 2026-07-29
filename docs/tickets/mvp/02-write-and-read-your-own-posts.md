# 02 — Write and read your own Posts

**Status:** resolved

**Blocked by:** 01 — Feed route replaces dashboard.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person writes a Post from a composer at the top of their Feed and immediately sees it there. The Feed lists their own Posts newest-first, each showing its Author, its text with the line breaks they typed, when it was written expressed relatively, and its Like and Comment counts — both zero for now, because neither exists yet.

Friendship does not exist yet, so "own Posts only" is the complete, correct behaviour of the Feed at this point, not a placeholder.

## Migration rule

This ticket writes the first migration and sets the pattern every later one follows. Per [ADR-0002](../../adr/0002-hard-deletes-with-database-cascades.md), dependent rows are disposed of by database cascade — `cascadeOnDelete()` on the foreign key — not by model events, which do not fire for mass deletes. The foreign key must be declared **inside** the `Schema::create` that makes the table: SQLite cannot add a constraint afterwards without rebuilding the table, so a later "add the constraint" migration is not an option.

## Acceptance criteria

- [x] A Post belongs to a non-nullable Author, holds plain text, and records when it was created.
- [x] A composer sits at the top of the Feed; submitting it creates a Post and returns to the Feed with confirmation.
- [x] Post text is validated by a Form Request — required, trimmed, capped at 1000 characters — never inline in the controller.
- [x] Empty or whitespace-only text is rejected with a visible error.
- [x] Line breaks in the text survive rendering.
- [x] The Feed lists the person's own Posts, newest first.
- [x] Like and Comment counts are read from the query via `withCount`, not stored on the Post.
- [x] Author is eager-loaded wherever Posts are listed.
- [x] A factory exists for Posts, usable with an explicit Author.
- [x] `PostCard` and `UserAvatar` exist as reusable components from the start.
- [x] Test: an authenticated person can create a Post, and it appears in their Feed.

## Notes on delivery

One criterion was left open, and is now closed.

**The `withCount` aggregates were unticked.** Likes and Comments did not exist
yet — there were no `likes` or `comments` tables — so `postPayload()` sent
hardcoded zeroes and there was nothing honest to aggregate. The criterion was
really a constraint on tickets 07 and 08: read both counts from the query with
`withCount()`, never from a column on the Post.

Both now do. Ticket 07 resolved the like count through
`Post::scopeWithLikeState`, and [08](08-comment-on-a-post.md) added
`withCount('comments')` to the Feed query and the detail lookup. Neither count
is stored on the Post, and the criterion is ticked as of 08.
