# 11 — Demo seed data and empty states

**Status:** resolved

**Blocked by:** 07 — Like and unlike a Post; 08 — Comment on a Post; 09 — Public Profiles with relationship state.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

Someone who clones the repository, migrates and seeds is inside a populated network within seconds, with every state of the application visible without creating anything themselves: a Feed with content, Friends who have written, a Friend Request waiting for a response, a request they sent that is still pending, and — crucially — strangers whose Posts they cannot see, so the visibility rule can be confirmed by observation rather than taken on trust.

A second seeded account has nothing at all, so the new-person experience is reachable without registering.

This ticket is protected in the cut order. An application that runs against an empty database shows a reviewer nothing, and every hour spent on the rest becomes invisible.

## Acceptance criteria

- [ ] A known demo account exists with a published, documented password.
- [x] It has several accepted Friends, all of whom have written Posts.
- [x] It has at least one incoming pending Friend Request, so the Requests section is not empty.
- [x] It has at least one outgoing pending Friend Request, so the pending state is visible in Find People and on a Profile.
- [x] Strangers exist who have written Posts that the demo account must not be able to see.
- [x] Likes and Comments are distributed across the seeded Posts so no count is uniformly zero.
- [x] A second seeded account has no Friends and no Posts, demonstrating the empty-state path.
- [x] The graph is composed deliberately by the seeder; factories supply filler, not structure.
- [x] Every empty state is audited end to end: Feed, Friends, Requests, Comments, and a Profile with no visible Posts.
- [x] Seeding a fresh database twice in a row succeeds.

## Notes on delivery

`DemoSeeder` builds the graph inside one transaction; `DatabaseSeeder` does
nothing but call it, and the kit's `Test User` is gone.

Fourteen people. `demo@example.com` — four Friends, two of the friendships asked
for and two asked of it, so the Friends list cannot be one-directional; two
incoming Friend Requests; one outgoing, still pending. `newcomer@example.com` has
nothing. Five strangers write to each other. Everyone's password is `password`.

Twenty-five Posts, fifteen of them readable by the demo account — so both the
Feed and Find People run to a second page — with the remaining ten proving the
visibility rule by their absence. Likes and Comments are dealt out by the Post's
place in the writing order rather than at random, so the counts vary, some are
zero, some of the Posts are already Liked by the demo account and some are not,
and every Like and Comment comes from a Friend of its Author. The structure is
written out in the seeder; factories supply the prose inside a Post or Comment,
and `Friendship::factory()->accepted()`/`->pending()` carry the status, so the
seeder composes friendships out of the same pieces the tests do.

The seeder returns early if the demo account is already there, so `db:seed` twice
in a row is a no-op rather than a unique-constraint error; the transaction is
what keeps that guard honest, since a half-written graph would otherwise be
indistinguishable from a finished one.

The unticked criterion — a **published** password. The account exists and the
credentials are documented in the `DemoSeeder` docblock and above, but nothing
publishes them where someone who has just cloned the repository would look.
`docs/tickets/mvp/12-readme-and-process-docs.md` owns that, along with the setup
sequence a clone runs; `composer setup` deliberately still stops at `migrate`.

Verified: `DemoSeederTest` (18 tests, driving the HTTP seam — the graph is
asserted through the screens a reviewer reads, not through the visibility scope)
plus a new empty-Feed case in `FeedTest`; the full suite at 141 passing, 4
pre-existing skips; Pint clean; PHPStan clean apart from a pre-existing
`UserFactory::withTwoFactor()` error from the starter kit. The empty-state audit
was driven in Chrome as both accounts: empty Feed, Friends, Requests and own
Profile for the newcomer, withheld Posts on a stranger's Profile from both sides,
the no-Comments state on a Post, and a stranger's Post detail answering 403.
Console carried no React or Inertia errors.

`docs/specs/mvp/spec.md` said Likes and Comments were "distributed so no count is
zero", which contradicts both this ticket and story 67 and would rule out the
no-Comments empty state; corrected to match.

Found during the audit and left alone, because it is ticket 08's code and needs
its own ticket: a Post card's Comment count has the screen-reader label
"1 comments", where the Like button beside it pluralises correctly.
