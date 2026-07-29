# bbook

A mini social network: people write posts, react to them, and connect to each other as friends. This glossary fixes the vocabulary the code, tickets, and tests all use.

## Language

### People

**Author**:
The person who wrote a Post or a Comment. Never absent — deleting a person deletes what they wrote.
_Avoid_: owner, creator, poster

**Profile**:
The public page of one person: their identity, their Posts if they are Visible to the viewer, and the action available on the Friendship between viewer and subject. Distinct from *settings*, which is where a person edits their own account.
_Avoid_: wall, account page

### Friendship

**Friendship**:
The single record of the relationship between two people, from the moment it is requested until it ends. It carries a status — `pending` or `accepted` — and never changes its `requester`/`addressee` sides.
_Avoid_: friend link, connection, relation

**Requester**:
The person who initiated a Friendship.
_Avoid_: sender, `user_id`

**Addressee**:
The person a Friendship was directed at, and the only one who may accept or reject it.
_Avoid_: recipient, target, `friend_id`

**Friend Request**:
A Friendship in `pending` status, seen from the Addressee's side. Not a separate record.
_Avoid_: invite, invitation

**Friend**:
The other person in an `accepted` Friendship. Friendship is mutual — direction stops carrying meaning once accepted.

### Posts

**Post**:
A piece of plain text written by an Author at a point in time. The unit everything else in the network attaches to.
_Avoid_: status, update, entry

**Comment**:
A piece of plain text an Author attaches to a Post. Never edited or deleted in this MVP, and never a reply to another Comment.

**Like**:
One person's single mark of approval on a Post. A person either has Liked a Post or has not — there is no second Like and no other reaction.
_Avoid_: reaction, favourite, upvote

### Visibility

**Visible**:
A Post is visible to its Author and to the Author's Friends, and to nobody else. Visibility governs reading a Post anywhere it appears, and any action taken on it. See [ADR-0001](docs/adr/0001-posts-are-visible-to-friends-only.md).
_Avoid_: public, shared

**Feed**:
The Posts visible to a person, newest first — i.e. their own Posts and their Friends' Posts. A view of the Posts, not a thing that is stored.
_Avoid_: timeline, wall, stream
