# 03 — Edit, delete, and open a Post

**Status:** ready-for-agent

**Blocked by:** 02 — Write and read your own Posts.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens a Post from the Feed onto its own detail page. From there, or from the Feed, they can edit a Post they wrote — landing on an edit page pre-filled with their existing text — or delete it after confirming. Neither is possible for a Post someone else wrote: the refusal happens when they try to open the edit page, not only when they try to save.

## Acceptance criteria

- [ ] A Post detail page shows the Post in full with its Author, text, time and counts.
- [ ] An edit page renders pre-filled with the Post's current text and saves changes, returning to the Post detail.
- [ ] Creating and editing share one `PostForm` component, parameterised by initial values, submit label and target.
- [ ] Editing is validated by its own Form Request with the same rules as creation.
- [ ] Deleting asks for confirmation first, reusing the pattern already used for account deletion.
- [ ] Deleting a Post disposes of everything attached to it, by database cascade.
- [ ] `PostPolicy` carries `update` and `delete`; controllers authorize through it and never check ownership inline.
- [ ] Opening the edit page for another person's Post is refused, as is saving or deleting it.
- [ ] Test: a person cannot delete a Post authored by someone else.
