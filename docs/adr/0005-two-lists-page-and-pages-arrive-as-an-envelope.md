# ADR-0005 — Two lists page, and a page arrives as an envelope

**Status:** accepted

**Date:** 2026-07-30

## Context

Two lists in this application grow without bound. The Feed grows with every
Post its viewer or their Friends write, and Find People grows with every
person who registers. Both rendered everything they could see. The other
lists do not have the problem: Friends and Requests are bounded by one
person's own graph, and a Comment thread by one Post, so at this scale they
are naturally small.

Paginating a list forces three decisions that outlive the ticket asking for
it — how the page is addressed, what the payload looks like, and what the sort
has to guarantee — so they are recorded here rather than left in the two
controllers that happen to make them first.

## Decision

**Offset pagination, not cursor.** `paginate()`, addressed by a `page` query
parameter. Its known flaw is real: a Post written between two page views
shifts the boundary, so an item can repeat. That is accepted at demo scale.
Cursor pagination trades it for a requirement that the sort key be unique,
and the seeder and the factories both produce rows sharing a `created_at`
second — precisely the case where a cursor needs a tiebreaker to work at all.

**Ten rows a page, written once.** `Controller::PER_PAGE`. Both lists page at
the same rate, so the number is one thing rather than two that have to be kept
in step.

**A page arrives as an envelope.** `PostResource::collection($paginator)` and
`PersonResource::collection($paginator)` serialize to `data`, `links` and
`meta`. The page component reads `posts.data` / `people.data` and hands the
whole envelope to one `Paginator` component, which reads `links.prev`,
`links.next` and `meta`. Both paginated screens therefore share one control,
and the page number lives in the URL: a page is linkable and the back button
walks back through it.

**The sort must be total.** Offset pagination over a sort that can break
either way does not merely reorder — it can hand the same row back on two
pages while dropping another entirely. `Post::scopeReadableBy` therefore ends
`->latest()->latest('id')`, the same tiebreaker `PostController::show` already
applies to a Comment thread. Without it, tied timestamps came back in
insertion order, which is the reverse of the newest-first the Feed promises.

## Relationship to ADR-0004

ADR-0004 sets `JsonResource::withoutWrapping()` globally, because "the `data`
key would only be something every page had to unwrap". That still holds for
every unpaginated payload, and most payloads are unpaginated.

A paginated resource collection is the exception, and not by choice:
`links` and `meta` have to travel alongside the rows, so Laravel's
`PaginatedResourceResponse` nests the rows under `data` whether or not
wrapping is switched off. The two paginated screens unwrap; nothing else does.
The alternative — flattening the envelope by hand in each controller to keep
ADR-0004 literally true — would cost both screens their shared `Paginator` and
buy nothing, since a paged list needs the metadata either way.

## Consequences

Adding pagination to a third list is `->paginate(self::PER_PAGE)`, a
`Paginated<T>` prop, and the existing `Paginator`. Nothing new is designed.

The cost is that two page components read one level deeper than the rest, and
that a `Paginated<T>` in TypeScript is a shape the server's envelope has to
keep matching. The feature tests pin it: they assert `posts.data` and
`posts.meta`, so a change to the envelope fails there rather than in a
browser.

Offset's repeated-item flaw is accepted, not solved. If it ever matters,
the tiebreaker this ADR already requires is exactly what a cursor
implementation would need, so the move is available.
