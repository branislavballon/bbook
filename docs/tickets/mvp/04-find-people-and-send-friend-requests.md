# 04 — Find People and send Friend Requests

**Status:** ready-for-agent

**Blocked by:** 01 — Feed route replaces dashboard.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens Friends and finds a Find People view listing everyone on the network except themselves, each row showing where they currently stand with that person. From there they send a Friend Request, and the row updates to show it is pending.

The Friends destination gains its three sections — Friends, Requests, Find People — as three real routes rendering one page, so each is directly linkable and independently testable. Only Find People does anything yet; the other two show their empty states.

## Acceptance criteria

- [ ] A Friendship records who asked, who was asked, and a status of pending or accepted, per [ADR-0003](../../adr/0003-friendship-is-one-directional-row.md).
- [ ] A unique constraint prevents the identical request being stored twice.
- [ ] Three named routes exist for the Friends sections, rendering one page component with a variant.
- [ ] Find People lists every other person, excluding the viewer, with their current relationship state shown per row.
- [ ] Sending a Friend Request creates it as pending and updates the row.
- [ ] A Form Request refuses all four bad cases: to yourself, to someone you already asked, to someone who already asked you (with a message pointing at their pending request), and to someone who is already your Friend.
- [ ] The Friends and Requests sections render explanatory empty states.
- [ ] Navigation gains a `Friends` item, and the Feed's empty state gains its link to Find People — both deferred from ticket 01, which could not link at routes that did not yet exist.
- [ ] Factories exist for Friendships, with states for pending and accepted.
- [ ] Test: a Friend Request to someone who already has a pending request outstanding to you is refused.
