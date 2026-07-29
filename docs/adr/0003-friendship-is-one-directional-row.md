# A Friendship is a single directional row, and rejection deletes it

A Friendship is stored as **one row for the whole life of the relationship**: `requester_id`, `addressee_id`, and a status of `pending` or `accepted`. Direction is preserved because a pending Friendship genuinely needs to know who asked; once accepted, the row is read symmetrically and direction stops carrying meaning. Mirrored rows on accept were rejected (a write that can half-fail), as was canonical `min`/`max` ordering (it discards the initiator that the Requests screen has to display).

Three lifecycle rules follow, none of them obvious from the schema alone:

- **Rejection deletes the row.** A retained `declined` status would make every "are these two connected?" query responsible for excluding it, and it is weak spam protection anyway. The invariant we want is: at most one Friendship row per pair, and its existence always means something positive.
- **A crossing request is refused, not auto-accepted.** When B requests A while A's request to B is still pending, B is told to respond to the existing request. Auto-accepting would let someone reach `accepted` by clicking "Add friend", bypassing the guarantee that `FriendshipPolicy@respond` exists to enforce — only the Addressee turns `pending` into `accepted`.
- **There is no unfriending in the MVP.** The assignment lists it nowhere. An accepted Friendship is permanent for now.

## Consequences

- A unique index on `(requester_id, addressee_id)` catches exact duplicates only. The reverse-direction case is not a database concern and needs an explicit guard in the request path.
- Every "are we friends?" query must check both columns; it can never assume the current user is the Requester.
