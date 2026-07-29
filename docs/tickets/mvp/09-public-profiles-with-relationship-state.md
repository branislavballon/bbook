# 09 — Public Profiles with relationship state

**Status:** resolved

**Blocked by:** 05 — Respond to Friend Requests and see Friends; 06 — Posts are visible to Friends only.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person opens anyone's Profile from Find People, the Friends list, or a Post's Author, and sees who they are and what they can do about it. A Friend's Posts are listed. A stranger's are withheld with an explanation that they are visible to Friends — not an empty list, which would read as "never posted". The action offered always matches the actual relationship: send a request, a pending marker, accept and reject controls, or nothing at all because they are already Friends.

The viewer's own Profile is the same page pointed at themselves, showing their Posts and no relationship action.

## Acceptance criteria

- [x] A named route renders a Profile for any person, bound by the person in the URL.
- [x] It does not collide with the settings screens, which already own the profile naming.
- [x] The Profile shows name and initials avatar.
- [x] A Friend's Posts are listed, using the same visibility scope as everywhere else.
- [x] A stranger's Posts are withheld with an explanation naming Friendship as the reason.
- [x] All four relationship states render: stranger, request sent, request received, already Friends.
- [x] The relationship state arrives as a single value computed on the server; the component does not reason about the graph.
- [x] Accepting or rejecting from a Profile goes through the same operations the Requests section uses.
- [x] The viewer's own Profile shows their Posts and offers no relationship action.
- [x] The Profile is reachable from navigation, Find People, the Friends list and Post authors.
- [x] Navigation gains a `My Profile` item pointing at the viewer's own `users.show` — deferred from ticket 01, which could not link at a route that did not yet exist.
