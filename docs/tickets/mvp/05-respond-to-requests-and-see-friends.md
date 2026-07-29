# 05 — Respond to Friend Requests and see Friends

**Status:** ready-for-agent

**Blocked by:** 04 — Find People and send Friend Requests.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A person sees the Friend Requests sent to them and accepts or rejects each one. Accepting makes the two of them Friends; rejecting makes the request disappear entirely, from both sides. Their Friends list then shows everyone they are connected to, with no distinction between people who asked them and people they asked.

## Acceptance criteria

- [ ] The Requests section lists incoming pending Friend Requests, showing who sent each.
- [ ] Accepting moves the existing Friendship to accepted — the same row, not a new one.
- [ ] Rejecting removes the Friendship entirely; nothing records that it was refused.
- [ ] `FriendshipPolicy` carries `respond`, satisfied only by the Addressee; the Requester attempting to accept their own request is refused.
- [ ] Controllers authorize through the policy and never check the relationship inline.
- [ ] The Friends section lists everyone in an accepted Friendship with the viewer, matching on either side of the relationship.
- [ ] Requests the viewer has sent but which are unanswered are visibly pending in Find People.
- [ ] Test: only the Addressee can accept a Friendship; the Requester is refused.
