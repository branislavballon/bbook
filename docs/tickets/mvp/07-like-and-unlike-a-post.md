# 07 — Like and unlike a Post

**Status:** resolved

**Blocked by:** 06 — Posts are visible to Friends only.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person Likes a Post in their Feed and the count goes up; they Like it again and the Like is withdrawn and the count goes down. The control shows whether they have already Liked it. Acting does not lose their place on the page. A Post that is not Visible to them cannot be Liked at all.

## Acceptance criteria

- [x] A Like belongs to one person and one Post, with a unique constraint making a second Like from the same person impossible at the storage layer.
- [x] Liking and unliking are separate operations, not one toggle endpoint — a repeated request must not undo itself.
- [x] The count shown after acting comes from the server, not from client-side state.
- [x] Acting on a Post in the Feed preserves scroll position and refreshes only the Posts.
- [x] Whether the viewer has Liked each Post is resolved in the same query that loads the Posts, not per card.
- [x] Liking a Post that is not Visible to the viewer is refused, authorized against the parent Post.
- [x] `LikeButton` is a reusable presentational component holding no fetch logic.
- [x] Deleting a Post disposes of its Likes.
- [x] Test: Liking twice does not error and does not produce two Likes.
