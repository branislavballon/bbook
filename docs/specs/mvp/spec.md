# MVP — Mini Social Network

Status: ready-for-agent

The full MVP described in `docs/Assignment.md`, specified as one document. Terminology follows `CONTEXT.md`; the three hard-to-reverse decisions are recorded in `docs/adr/`.

## Problem Statement

The application is a Laravel React starter kit and nothing more. A person can register, log in, manage their account settings, and land on a placeholder dashboard. There is nothing to read, nothing to write, and no one to connect to.

Someone using it wants to write short pieces of text, see what the people they know have written without seeing the whole world's output, respond to those pieces of text, and control who can read theirs. None of that exists.

## Solution

A small social network with three moving parts:

**Posts.** A person writes plain text from a composer at the top of their Feed. They can edit or delete anything they wrote, and nothing anyone else wrote.

**Friendship.** A person browses a list of everyone on the network, sends a Friend Request, and the Addressee accepts or rejects it. Accepting makes the two people Friends, mutually and permanently.

**Visibility.** A Post is readable by its Author and the Author's Friends and nobody else. The Feed is therefore the person's own Posts plus their Friends' Posts, newest first. The same rule governs the Post detail page, the post list on a Profile, and every action taken against a Post — Liking it, or Commenting on it.

Around those: Likes, Comments on the Post detail page, a public Profile per person showing identity and the relationship action available, and a seeded demo graph so none of it is empty on first run.

## User Stories

### Authoring

1. As an authenticated person, I want a composer at the top of my Feed, so that writing a Post is the first thing available to me after logging in.
2. As an authenticated person, I want my Post to be rejected when it is empty or whitespace-only, so that I do not publish nothing by accident.
3. As an authenticated person, I want a limit on Post length, so that the interface never has to render text it cannot lay out.
4. As an authenticated person, I want the line breaks I typed to survive, so that my Post reads the way I wrote it.
5. As an authenticated person, I want to be returned to my Feed with confirmation after posting, so that I can see my Post has landed.
6. As an authenticated person, I want to edit a Post I wrote, so that I can correct a mistake without deleting and rewriting it.
7. As an authenticated person, I want the edit form pre-filled with my existing text, so that I amend rather than retype.
8. As an authenticated person, I want to be refused when I try to open the edit page for someone else's Post, so that the rule is enforced at the point of access and not only at the point of saving.
9. As an authenticated person, I want to delete a Post I wrote, so that I can retract something.
10. As an authenticated person, I want to confirm before a delete completes, so that a misclick does not destroy my writing.
11. As an authenticated person, I want deleting a Post to take its Likes and Comments with it, so that no orphaned remains of it exist.
12. As an authenticated person, I want to be refused when I try to edit or delete a Post I did not write, so that my writing is mine alone to change.

### Reading and the Feed

13. As an authenticated person, I want my Feed to show my own Posts and my Friends' Posts, so that what I read is from people I chose.
14. As an authenticated person, I want strangers' Posts kept out of my Feed, so that the Friendship graph actually determines what I see.
15. As an authenticated person, I want my Feed newest-first, so that the most recent writing is what I encounter.
16. As an authenticated person, I want each Post in the Feed to show its Author, its text, when it was written, its Like count and its Comment count, so that I can judge it without opening it.
17. As an authenticated person, I want a Post's creation time expressed relatively, so that recency is legible at a glance.
18. As an authenticated person, I want the Feed paginated, so that it stays fast as the network grows.
19. As an authenticated person, I want an explanatory empty state when my Feed has nothing in it, so that a new account does not look broken.
20. As an authenticated person, I want that empty state to link me to where I find people, so that I know what to do next.
21. As an authenticated person, I want to open a Post's detail page, so that I can read it in full alongside its Comments.
22. As an authenticated person, I want to be refused the detail page of a Post that is not Visible to me, so that guessing a URL is not a way around the rule.
23. As an unauthenticated visitor, I want to be redirected to login when I request any part of the network, so that nothing is readable without an account.

### Friendship

24. As an authenticated person, I want a list of everyone on the network, so that I can find people to connect to.
25. As an authenticated person, I want that list to exclude myself, so that it offers only real possibilities.
26. As an authenticated person, I want that list to show my current relationship to each person, so that I do not send a request I already sent.
27. As an authenticated person, I want that list paginated, so that it stays usable as the network grows.
28. As an authenticated person, I want to send a Friend Request, so that I can ask to see someone's Posts.
29. As an authenticated person, I want to be stopped from sending a second request to the same person, so that I cannot spam them.
30. As an authenticated person, I want to be told to respond to their existing request when I try to send one to someone who already asked me, so that the two requests do not race each other.
31. As an authenticated person, I want to be stopped from sending a request to someone who is already my Friend, so that the relationship stays single.
32. As an authenticated person, I want to be stopped from sending a request to myself, so that the graph stays sane.
33. As an authenticated person, I want to see the Friend Requests sent to me, so that I can respond to them.
34. As an authenticated person, I want to accept a Friend Request, so that we become Friends and can read each other's Posts.
35. As an authenticated person, I want to reject a Friend Request, so that I can decline without explanation.
36. As an authenticated person, I want a rejected request to disappear entirely, so that it does not linger on either person's screen.
37. As an authenticated person, I want to be refused when I try to accept or reject a request that was not sent to me, so that only the Addressee decides.
38. As an authenticated person, I want to see my list of Friends, so that I know who is in my network.
39. As an authenticated person, I want my Friends list to show people who accepted my request and people whose request I accepted, without distinction, so that Friendship reads as mutual.
40. As an authenticated person, I want explanatory empty states on the Friends and Requests views, so that emptiness reads as a state and not a fault.
41. As an authenticated person, I want the requests I have sent but which are unanswered to be visibly pending, so that I know I am waiting rather than un-asked.

### Profiles

42. As an authenticated person, I want a Profile page for anyone on the network, so that I can see who they are before connecting.
43. As an authenticated person, I want to see a person's name and initials avatar on their Profile, so that I can identify them.
44. As an authenticated person, I want to see a stranger's Posts withheld with an explanation that they are visible to Friends, so that I understand why the page is sparse rather than assuming they never write.
45. As an authenticated person, I want to see a Friend's Posts on their Profile, so that Friendship has a visible payoff.
46. As an authenticated person, I want the relationship action on a Profile to match our actual state — send a request, a disabled pending marker, accept and reject controls, or nothing because we are already Friends — so that the page is always actionable and never misleading.
47. As an authenticated person, I want my own Profile to show my own Posts and no relationship action, so that it reads as mine.
48. As an authenticated person, I want to reach my own Profile from the navigation, so that I can see how others see me.
49. As an authenticated person, I want to edit my name in settings, so that my Profile shows what I want it to.

### Likes

50. As an authenticated person, I want to Like a Post that is Visible to me, so that I can signal approval without writing.
51. As an authenticated person, I want to remove my Like, so that the signal is reversible.
52. As an authenticated person, I want the Like count to update after I act, so that what I see is the truth on the server.
53. As an authenticated person, I want my scroll position preserved when I Like something in the Feed, so that acting does not lose my place.
54. As an authenticated person, I want to see whether I have already Liked a Post, so that the control reflects my own state.
55. As an authenticated person, I want a second Like from me to be impossible, so that the count cannot be inflated by a double-tap or a retry.
56. As an authenticated person, I want to be refused when I try to Like a Post that is not Visible to me, so that a hidden Post cannot be acted on.

### Comments

57. As an authenticated person, I want to add a Comment on a Post's detail page, so that I can respond in words.
58. As an authenticated person, I want an empty Comment rejected, so that the thread is not padded with nothing.
59. As an authenticated person, I want Comments shown oldest-first, so that the thread reads as a conversation.
60. As an authenticated person, I want each Comment to show its Author and when it was written, so that I can follow who said what.
61. As an authenticated person, I want to be refused when I try to Comment on a Post that is not Visible to me, so that the visibility rule holds for writes as well as reads.
62. As an authenticated person, I want an empty state when a Post has no Comments, so that the absence is explicit.

### Demo and first run

63. As a reviewer, I want a seeded account with published credentials, so that I can be inside the application immediately.
64. As a reviewer, I want that account to have Friends who have written Posts, so that the Feed demonstrates itself.
65. As a reviewer, I want that account to have an incoming Friend Request and an outgoing one, so that every Friendship state is visible without my creating it.
66. As a reviewer, I want strangers with Posts to exist in the seed, so that I can confirm the visibility rule by observing what I cannot see.
67. As a reviewer, I want Likes and Comments spread across the seeded Posts, so that no count is uniformly zero.
68. As a reviewer, I want a second seeded account with no Friends and no Posts, so that I can see the new-person path without registering.

## Implementation Decisions

### Domain model

Four new tables: `posts`, `comments`, `likes`, `friendships`.

- **Post** — belongs to an Author (`user_id`), holds `body` text and timestamps. Has many Likes and Comments.
- **Comment** — belongs to a Post and an Author. No editing or deletion, no nesting.
- **Like** — belongs to a Post and a person. **Unique index on `(user_id, post_id)`** so a second Like is impossible at the storage layer rather than by convention.
- **Friendship** — `requester_id`, `addressee_id`, `status` (`pending` | `accepted`) as a backed enum. **Unique index on `(requester_id, addressee_id)`.** Per [ADR-0003](../../adr/0003-friendship-is-one-directional-row.md): one row for the life of the relationship, direction preserved, rejection deletes the row, no unfriending. The reverse-direction duplicate is not catchable by an index and is guarded explicitly in the request path.

Author is non-nullable everywhere, per [ADR-0002](../../adr/0002-hard-deletes-with-database-cascades.md). Deleting a Post disposes of its Likes and Comments; deleting a person disposes of their Posts, their Comments, their Likes, and every Friendship where they appear on **either** side.

The cascade is enforced at the **database**, by `cascadeOnDelete()` on every foreign key — measured working on this project's SQLite, in both the file database and the `:memory:` test database. Model `deleting` events were considered and rejected: they do not fire for mass deletes, so any query-builder deletion would silently orphan rows.

Two consequences follow. Every foreign key must be declared inside the `Schema::create` that makes the table, because SQLite cannot add a constraint to an existing table without rebuilding it. And `friendships` needs the cascade on **both** `requester_id` and `addressee_id` — declaring it on one side leaves constraint-violating rows when the person on the other side is deleted.

### Visibility

Per [ADR-0001](../../adr/0001-posts-are-visible-to-friends-only.md), visibility is a privacy rule, not a Feed filter. It is expressed **once**, as a query scope on Post meaning "Visible to this person" — authored by them, or authored by someone with whom they have an `accepted` Friendship in either direction. That single scope is the only place the rule is written.

It is applied at four points: the Feed query, the Post detail lookup, the post list on a Profile, and any write targeting a Post. `PostPolicy` carries `view`, `update`, `delete`. Likes and Comments authorize against the parent Post's `view` ability — otherwise a stranger could act on a Post they cannot read. `FriendshipPolicy` carries `respond`, satisfied only by the Addressee.

### Routes and naming

`profile.*` is already taken by the settings screens, and `dashboard` currently occupies the post-login landing slot.

- The `dashboard` route and its placeholder page are **removed**. `feed` replaces it as the post-login destination; the Fortify home redirect is repointed.
- `users.show` for public Profiles, route-model-bound. A person's own Profile is the same route pointed at themselves — one component, no special case.
- `friends.index`, `friends.requests`, `friends.find` — three real routes rendering one page component with a variant prop, so each tab is linkable, back-navigable, and directly testable.
- Posts follow resourceful naming: index is the Feed, plus show, edit, update, destroy, store. No `create` route — the composer lives on the Feed.
- Likes are `POST` and `DELETE` on a nested like route. Not a toggle: a toggle does opposite things on identical requests and undoes itself on a retry.
- Friendship actions: store (send), update (accept), destroy (reject).

Front-end route access is via Wayfinder-generated helpers, never hardcoded URLs.

### Interaction

Likes use a partial reload with `preserveScroll`, refreshing only the posts prop. No optimistic client state — the counts come from `withCount`, and mirroring them client-side would reintroduce the synchronization risk that choice was made to avoid.

Post creation redirects to the Feed with a flash message; editing redirects to the Post detail. Comments are confined to the Post detail page; Feed cards show the count as a link.

### Validation and authorization

Form Requests only, never inline validation: `StorePostRequest`, `UpdatePostRequest`, `StoreCommentRequest`, `StoreFriendshipRequest`. Post and Comment bodies are required, trimmed, and capped at 1000 characters. `StoreFriendshipRequest` carries the four refusals: self, duplicate, reverse-pending, already-friends.

Authorization is by Policy, never an inline controller check.

### Reading and pagination

Feed and Find People paginate at 10, offset-based. Friends, Requests and Comments are unpaginated. Offset pagination's known drift — a Post created between page views shifting the boundary — is accepted; cursor pagination's `created_at` tiebreaker is a trap with factory-seeded timestamps.

Counts come from `withCount('likes', 'comments')` on every Post query. Author is eager-loaded everywhere Posts are listed. Whether the current person has Liked each Post is resolved in the same query, not per-card.

### Front end

The Profile receives a single `friendship_status` prop computed server-side; the component switches on it. No client-side reasoning about the graph.

Components are built as reusable pieces from the start, not refactored into shape at the end: `PostCard`, `PostForm` (shared by create and edit), `CommentItem`, `LikeButton`, `UserAvatar`, `EmptyState`, `Paginator`. shadcn/ui primitives underneath. Sidebar shell variant, three navigation items — Feed, Friends, My Profile — with the kit's existing user menu untouched for settings and logout.

### Demo data

Factories for every model, with states (`Friendship::factory()->accepted()`, `Post::factory()->for($author)`) so tests compose the same shapes the seeder does.

The seeder builds a deliberate graph rather than random data: a known `demo@example.com` account with several accepted Friends who have Posts, one incoming pending request, one outgoing pending request, several strangers with Posts that must not be visible, Likes and Comments distributed so no count is zero, and a second account with nothing so the new-person path is demonstrable.

## Testing Decisions

**What makes a good test here.** Tests assert externally observable behaviour — a status code, a redirect, what appears in a response, what is or is not in the database afterwards. They do not assert that a particular scope was called or a particular query was built. A test that would survive rewriting the implementation, and fail if the behaviour changed, is the target.

**One seam: the HTTP boundary.** Every existing test in the repo is an HTTP-level feature test using `route()` and `actingAs`, with `RefreshDatabase` applied globally in `tests/Pest.php`. All of this MVP is reachable through that seam, including both delete cascades — a Post's via its destroy route, a person's via the existing `DELETE /settings/profile` route. No unit or model-level seam is introduced.

**Prior art.** `tests/Feature/DashboardTest.php` for the guest-redirect and authenticated-access shape; `tests/Feature/Settings/ProfileUpdateTest.php` for the act-then-assert-database-state shape. `DashboardTest.php` is replaced by the Feed test when the dashboard route is removed.

**What is tested.** Risk, not line count — the dangerous logic is the visibility rule (one scope, four enforcement points, easy to forget one) and the Friendship lifecycle (three rules invented in design, none obvious from the schema).

1. An unauthenticated request to the Feed redirects to login.
2. A person can create a Post, and it appears in their Feed.
3. A person cannot delete a Post authored by someone else — `PostPolicy`, named explicitly in the assignment.
4. The Feed contains the person's own Posts and their Friends' Posts, and excludes a stranger's.
5. A person is refused the detail page of a Post authored by a stranger.
6. Only the Addressee can accept a Friendship; the Requester is refused.
7. A Friend Request to someone who already has a pending request outstanding to you is refused.
8. Liking twice does not error, and does not produce two Likes.
9. Deleting a Post removes its Likes and Comments.

**What is deliberately not tested, and why.** Registration and login are Fortify's code and are already covered by the kit's own tests; testing them again tests the framework. This is stated in the README as a judgment, not left as a silent omission.

## Out of Scope

Everything in the assignment's "Not to implement for now": messenger and chat, sharing Posts, full-text user search, groups, pages, reels, stories, any image or video upload including avatars, and administration.

Additionally, and by decision rather than omission:

- **Unfriending.** No transition out of an accepted Friendship ([ADR-0003](../../adr/0003-friendship-is-one-directional-row.md)).
- **A retained `declined` state.** Rejection deletes the row, so nothing records that a request was refused and nothing throttles a re-request.
- **Editing and deleting Comments** — excluded by the assignment.
- **Infinite scroll**, notifications, realtime updates, tagging, hashtags, bookmarks — all filed as future extensions.
- **Optimistic UI** on the Like button.
- **A verbatim AI prompt log.** The process record is this spec, the tickets and their `Status:` transitions, the ADRs, and the conventional commit history.

## Further Notes

**Build order departs from the assignment's**, which lists Feed at step 4 and Friends at step 6. Under friends-only visibility the Feed cannot be built before the Friendship graph exists — the visibility scope four screens depend on has nothing to resolve against. `docs/Assignment.md` has been amended to reflect the corrected order. Actual sequence: Post CRUD → Friendship → visibility scope and Feed → Likes → Comments → Profile → seeder → tests.

**The cut order, decided in advance** so it is not decided under time pressure. Load-bearing and never cut: Post CRUD with `PostPolicy`, the Friendship lifecycle with `FriendshipPolicy`, the visibility scope and Feed, Form Requests, the seeder, and the `PostPolicy` denial test. Cut bottom-up if needed: pagination first, then Likes, then Comments, then the four-state Profile degraded to two states, then test breadth reduced to the three named tests — never to zero. The seeder is protected at all costs; an application that runs against an empty database shows the reviewer nothing.

**The visibility rule weakens discovery.** A stranger's Profile shows a name and no Posts, so the decision to send a Friend Request rests on identity alone. Accepted, and recorded in [ADR-0001](../../adr/0001-posts-are-visible-to-friends-only.md).

**Conventional commits** (`feat:` / `fix:` / `refactor:`), one per vertical slice, so the history reads as the development plan executing.
