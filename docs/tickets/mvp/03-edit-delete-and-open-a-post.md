# 03 — Edit, delete, and open a Post

**Status:** resolved

**Blocked by:** 02 — Write and read your own Posts.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens a Post from the Feed onto its own detail page. From there, or from the Feed, they can edit a Post they wrote — landing on an edit page pre-filled with their existing text — or delete it after confirming. Neither is possible for a Post someone else wrote: the refusal happens when they try to open the edit page, not only when they try to save.

## Acceptance criteria

- [x] A Post detail page shows the Post in full with its Author, text, time and counts.
- [x] An edit page renders pre-filled with the Post's current text and saves changes, returning to the Post detail.
- [x] Creating and editing share one `PostForm` component, parameterised by initial values, submit label and target.
- [x] Editing is validated by its own Form Request with the same rules as creation.
- [x] Deleting asks for confirmation first, reusing the pattern already used for account deletion.
- [x] Deleting a Post disposes of everything attached to it, by database cascade.
- [x] `PostPolicy` carries `update` and `delete`; controllers authorize through it and never check ownership inline.
- [x] Opening the edit page for another person's Post is refused, as is saving or deleting it.
- [x] Test: a person cannot delete a Post authored by someone else.

## Notes on delivery

Two criteria were ticked with a qualification, and one was left open. Both are now settled.

**Counts** on the detail page rendered as the same hardcoded zeroes the Feed used. They are now `withCount()` aggregates: likes from ticket 07, comments from [08](08-comment-on-a-post.md).

**The cascade was unticked.** Nothing was attached to a Post yet — there were no `likes` or `comments` tables — so there was nothing to cascade and nothing honest to test. This criterion was really a constraint on the Likes and Comments tickets: declare the foreign key inside `Schema::create` with `cascadeOnDelete()`, per [ADR-0002](../../adr/0002-hard-deletes-with-database-cascades.md), and prove it with the spec's test 9. Both tables did, and the spec's test 9 — *deleting a Post removes its Likes and Comments* — lands in `tests/Feature/CommentTest.php` with 08, so the criterion is ticked as of 08.

**Authorization runs as `->can()` route middleware** rather than a `Gate::authorize()` call in the controller body. Still `PostPolicy`, still never an inline ownership check — but the policy now runs *before* the Form Request validates, so a stranger saving invalid text at someone else's Post is refused rather than told their text is wrong.

**`PostPolicy` has no `view`**, so the detail page is readable by any authenticated person. That matches the Feed, which is still own-Posts-only. Both are closed by the visibility ticket.
