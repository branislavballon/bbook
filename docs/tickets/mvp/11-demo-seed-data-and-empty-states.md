# 11 — Demo seed data and empty states

**Status:** ready-for-agent

**Blocked by:** 07 — Like and unlike a Post; 08 — Comment on a Post; 09 — Public Profiles with relationship state.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

Someone who clones the repository, migrates and seeds is inside a populated network within seconds, with every state of the application visible without creating anything themselves: a Feed with content, Friends who have written, a Friend Request waiting for a response, a request they sent that is still pending, and — crucially — strangers whose Posts they cannot see, so the visibility rule can be confirmed by observation rather than taken on trust.

A second seeded account has nothing at all, so the new-person experience is reachable without registering.

This ticket is protected in the cut order. An application that runs against an empty database shows a reviewer nothing, and every hour spent on the rest becomes invisible.

## Acceptance criteria

- [ ] A known demo account exists with a published, documented password.
- [ ] It has several accepted Friends, all of whom have written Posts.
- [ ] It has at least one incoming pending Friend Request, so the Requests section is not empty.
- [ ] It has at least one outgoing pending Friend Request, so the pending state is visible in Find People and on a Profile.
- [ ] Strangers exist who have written Posts that the demo account must not be able to see.
- [ ] Likes and Comments are distributed across the seeded Posts so no count is uniformly zero.
- [ ] A second seeded account has no Friends and no Posts, demonstrating the empty-state path.
- [ ] The graph is composed deliberately by the seeder; factories supply filler, not structure.
- [ ] Every empty state is audited end to end: Feed, Friends, Requests, Comments, and a Profile with no visible Posts.
- [ ] Seeding a fresh database twice in a row succeeds.
