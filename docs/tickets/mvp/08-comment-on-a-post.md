# 08 — Comment on a Post

**Status:** ready-for-agent

**Blocked by:** 06 — Posts are visible to Friends only.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens a Post's detail page, reads the Comments on it in the order they were written, and adds their own. The Feed shows only the count, linking through to the detail page — Comments are never composed from the Feed. A Post that is not Visible cannot be Commented on.

Comments are never edited or deleted in this MVP, and never reply to each other.

## Acceptance criteria

- [ ] A Comment belongs to a Post and to a non-nullable Author, and records when it was written.
- [ ] The Post detail page lists Comments oldest-first, each with its Author and time.
- [ ] A form on the detail page adds a Comment, validated by a Form Request — required, trimmed, capped — never inline.
- [ ] Empty or whitespace-only Comments are rejected with a visible error.
- [ ] A Post with no Comments shows an explicit empty state.
- [ ] The Feed's Comment count links to the detail page; no composer appears on Feed cards.
- [ ] Commenting on a Post that is not Visible to the viewer is refused, authorized against the parent Post.
- [ ] `CommentItem` exists as a reusable component.
- [ ] Deleting a Post disposes of its Comments.
- [ ] Test: deleting a Post removes its Likes and Comments.
