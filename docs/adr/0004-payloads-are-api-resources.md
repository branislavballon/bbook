# ADR-0004 — Inertia payloads are API Resources

**Status:** accepted

**Date:** 2026-07-29

## Context

Every controller shaped its own props by hand, in a private `postPayload` /
`personPayload` method. That held while each shape had one reader. It stopped
holding at ticket 09: a profile lists posts the feed already lists, and shows a
person the friends sections already show. Two more copies of both shapes, in a
third controller, each free to drift.

Drift here is not cosmetic. `PostCard` and `PersonRow` are shared components
with one TypeScript type apiece; a payload that omits `liked` or renames
`friendship_id` breaks them at runtime, and the private-method arrangement gave
the compiler nothing to check against.

`PostController::postPayload` already carried `// TODO: This should be resource
in the future.`

## Decision

Shared prop shapes are Eloquent API Resources in `app/Http/Resources`, one per
shape, named for what the screen reads rather than for the model:

- `PostResource` — the feed, the post detail page, the post list on a profile.
- `PersonResource` — the three friends sections and the profile page.

A resource is the single definition of its shape. Its TypeScript counterpart in
`resources/js/types` is the same shape written once on the other side.

Resources that take more than the model — `PersonResource` needs the viewer and
the friendship between them — accept it through the constructor rather than
through a setter, so a half-built resource cannot be rendered.

`JsonResource::withoutWrapping()` is set globally in `AppServiceProvider`.
Resources here are read as Inertia props, never as a JSON API envelope, so the
`data` key would only be something every page had to unwrap.

Payloads with exactly one reader stay inline. `PostController::commentPayload`
is one: comments are rendered only by the post detail page, and are never
edited or deleted, so there are no abilities to carry and nothing to keep in
step. It becomes a resource when a second screen wants comments.

## Consequences

Adding a field to a post payload is one edit in `PostResource` and one in
`types/posts.ts`, and every screen gets it. The counts and the viewer's own
like still come from the query that loaded the posts, per ADR-0001's companion
`Post::scopeWithLikeState` — a resource shapes what a query resolved, it does
not resolve anything itself, and a resource that queried per row would
reintroduce the N+1 those scopes exist to avoid.

`Post::scopeReadableBy` pairs with `PostResource` the way `withLikeState`
pairs with the card: the scope guarantees the resource's fields are loaded.

The cost is indirection — a prop's shape is no longer visible in the controller
that renders it. The tests pin the contract instead, asserting the component
name and the props each page consumes.
