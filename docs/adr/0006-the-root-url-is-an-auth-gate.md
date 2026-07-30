# ADR-0006 — The root URL is an auth gate, not a page

**Status:** accepted

**Date:** 2026-07-30

## Context

`/` rendered the starter kit's `welcome` page: a marketing panel for the
framework the application happens to be built on. Under
[ADR-0001](0001-posts-are-visible-to-friends-only.md) nothing in this network
is readable without an account, so whatever stands at `/` cannot show a
visitor any of the product — no Feed, no Profile, not even a name of a person.
A public page here can only ever describe the application, never demonstrate
it.

That leaves a choice with consequences beyond the ticket that removes the
framework branding: does the application have a public face at all?

## Decision

**`/` is a redirect, not a page.** A guest asking for it is sent to login; an
authenticated person is sent to the Feed. The `home` route name survives the
change, so `home()` keeps resolving in Wayfinder and in the layouts that link
to it. `resources/js/pages/welcome.tsx` is deleted rather than rewritten.

**The login screen is therefore the front door**, and is treated as one: it
carries the application's mark and name rather than a bare icon labelled with
the page title.

The alternative — a small branded landing page with the logo, a line of copy
and Log in / Sign up buttons — was considered and rejected. It would be a
second identity surface to design, test and keep in step with the sidebar, in
exchange for a page that says what the application is to a visitor who is one
click from seeing it.

## Consequences

**Registration is reachable only through login.** The login screen's "Sign
up" link is the sole path to `register`, since no page above it offers one.
That link is now load-bearing and must not be removed casually.

**There is no shareable public URL.** Every address in the application answers
either with a redirect to login or with a 403/302 for a guest, so nothing can
be linked to from outside — no preview, no crawlable page. Accepted: a
friends-only network with no public content has nothing to preview.

**An anchor pointing at `home()` from an auth screen is a loop.** For a guest,
`home` resolves to login, so a logo link on the login page would land on
itself. The auth layout's logo is therefore not a link at all.

The move is reversible: reinstating a landing page means giving the `home`
route a component again, and the identity pieces it would need — mark and
wordmark — already exist as shared components.
