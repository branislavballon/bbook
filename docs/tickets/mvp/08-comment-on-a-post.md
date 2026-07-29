# 08 — Comment on a Post

**Status:** resolved

**Blocked by:** 06 — Posts are visible to Friends only.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens a Post's detail page, reads the Comments on it in the order they were written, and adds their own. The Feed shows only the count, linking through to the detail page — Comments are never composed from the Feed. A Post that is not Visible cannot be Commented on.

Comments are never edited or deleted in this MVP, and never reply to each other.

## Acceptance criteria

- [x] A Comment belongs to a Post and to a non-nullable Author, and records when it was written.
- [x] The Post detail page lists Comments oldest-first, each with its Author and time.
- [x] A form on the detail page adds a Comment, validated by a Form Request — required, trimmed, capped — never inline.
- [x] Empty or whitespace-only Comments are rejected with a visible error.
- [x] A Post with no Comments shows an explicit empty state.
- [x] The Feed's Comment count links to the detail page; no composer appears on Feed cards.
- [x] Commenting on a Post that is not Visible to the viewer is refused, authorized against the parent Post.
- [x] `CommentItem` exists as a reusable component.
- [x] Deleting a Post disposes of its Comments.
- [x] Test: deleting a Post removes its Likes and Comments.

## Notes on delivery

**The composer is `PostForm`, not a new component.** It already takes its
label, placeholder, submit label and Wayfinder form props from the caller, so
the comment composer is that component with different words rather than a
near-duplicate of it. Only `CommentItem` is new on the front end.

**The Form Request is `StoreCommentRequest extends BodyRequest`.** The spec
states one rule for both — *"Post and Comment bodies are required, trimmed, and
capped at 1000 characters"* — so the abstract `PostRequest` from ticket 02 is
renamed `BodyRequest` and now parents the comment request alongside the two
post ones. Each subclass supplies only the wording of its refusal. Nothing
about post validation changed; `StorePostRequest` and `UpdatePostRequest` keep
their own messages and their own tests.

**Comments are ordered `created_at` then `id`.** Oldest-first on the timestamp
alone is not deterministic for two comments written in the same second, which
is exactly what the factories and the coming seeder produce. There is a test
for it.

**The comment count is now a real `withCount('comments')` aggregate** on both
the Feed query and the detail lookup, replacing the hardcoded zero
`postPayload()` has been sending since ticket 02. That closes the aggregate
criterion left open on [02](02-write-and-read-your-own-posts.md) and the
cascade criterion left open on
[03](03-edit-delete-and-open-a-post.md); both are ticked there now.

**Verified in the browser** as well as by the tests: writing a comment on the
detail page appends it to the thread and moves both counts, a whitespace-only
comment comes back with a visible `Write something before commenting.` against
the field, the empty state renders on a post with no comments, and the Feed's
count links through with no composer on the card. Console clean, the
`POST /posts/{post}/comments` request returning 302 and the partial reload 200.
