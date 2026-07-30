# Bbook

A mini social network — write Posts, Like and Comment on them, and connect to
people as Friends — built as an interview assignment with AI agentic coding.
The assignment is in [docs/Assignment.md](docs/Assignment.md).

Laravel 13, Inertia 3, React 19 + TypeScript, Tailwind 4, shadcn/ui, SQLite.

This README is a launcher and a map: how to run it, and where the thinking
behind it is written down. What the application does is quicker to see than to
read about — sign in and look.

## Try the live demo

A working deployment runs at **<https://book.branislavballon.com>** — that is
the primary place to test the application, no local setup needed. It carries the
same seeded demo graph and the same accounts as the [Sign in](#sign-in) table
below.

The site is gated: it opens through a special access URL, which is sent
separately rather than written down here.

Running it locally is the fallback, and is described next.

## Run it

You need PHP 8.5 with the SQLite extension, Composer and Node 20+ before the
first command. If any of them is missing:
[install PHP and Composer](https://laravel.com/docs/13.x/installation#installing-php),
[the Laravel installation guide](https://laravel.com/docs/13.x/installation),
[Composer](https://getcomposer.org/download/), [Node.js](https://nodejs.org/en/download).

```bash
git clone git@github.com:branislavballon/bbook.git
cd bbook
composer setup   # installs, writes .env, generates a key, migrates, seeds, builds
composer dev     # http://localhost:8000
```

`composer setup` seeds the demo graph, so the application is populated on first
run. To rebuild it from scratch later: `php artisan migrate:fresh --seed`.

## Sign in

Every seeded account has the password `password`.

| Account                | Password   | What it shows                                                                                                     |
| ---------------------- | ---------- | ----------------------------------------------------------------------------------------------------------------- |
| `demo@example.com`     | `password` | The populated experience: four Friends who write, two incoming Friend Requests, one outgoing, a Feed past page one |
| `newcomer@example.com` | `password` | The new-person path: no Friends, no Posts, every empty state                                                        |

The seed also contains five strangers who write to each other. None of their
Posts is visible to the demo account — that absence is how the visibility rule
is confirmed by observation rather than taken on trust. `docs/tickets/mvp/11-demo-seed-data-and-empty-states.md`
describes the graph; `database/seeders/DemoSeeder.php` builds it.

## Test it

```bash
php artisan test              # the Pest suite
composer ci:check             # lint, format, types (PHP and TS), then the suite
```

Everything else the project checks with:

```bash
vendor/bin/pint               # PHP formatting
composer types:check          # PHPStan / Larastan
npm run types:check           # tsc --noEmit
npm run lint:check            # ESLint
```

## Decisions

The reasoning lives in three places, none of which is the code.

- **[CONTEXT.md](CONTEXT.md)** — the glossary. The vocabulary the code, the
  tickets and the tests all use: Author, Friendship, Requester, Addressee,
  Like, Visible, Feed, Relationship State.
- **[docs/specs/mvp/spec.md](docs/specs/mvp/spec.md)** — the MVP as one
  document: user stories, the domain model, routes and naming, what is tested
  and why, and the cut order decided in advance.
- **docs/adr/** — the decisions that were hard to reverse:
    - [ADR-0001](docs/adr/0001-posts-are-visible-to-friends-only.md) — a Post
      is readable by its Author and their Friends and nobody else. Visibility
      is a privacy rule, not a Feed filter, so it is one query scope applied at
      every read and every write.
    - [ADR-0002](docs/adr/0002-hard-deletes-with-database-cascades.md) — hard
      deletes with `cascadeOnDelete()` on every foreign key, so an Author is
      never nullable and no layer needs a "deleted user" branch.
    - [ADR-0003](docs/adr/0003-friendship-is-one-directional-row.md) — a
      Friendship is one row for the life of the relationship; rejection deletes
      it, a crossing request is refused rather than auto-accepted, and there is
      no unfriending.
    - [ADR-0004](docs/adr/0004-payloads-are-api-resources.md) — prop shapes
      read by more than one screen are API Resources, so a shared component's
      payload has one definition instead of one per controller.
    - [ADR-0005](docs/adr/0005-two-lists-page-and-pages-arrive-as-an-envelope.md)
      — the Feed and Find People paginate by offset, ten to a page, and a page
      arrives as a `data`/`links`/`meta` envelope both screens share.

## The development process

The assignment asks for the decisions, the plan and the history to be
documented. They are, as four artefacts that were written as the work happened
rather than reconstructed afterwards:

1. **The spec** — [docs/specs/mvp/spec.md](docs/specs/mvp/spec.md), written
   before any feature code, including the order to cut things in if time ran
   short.
2. **The tickets** — [docs/tickets/mvp/](docs/tickets/mvp/), one per vertical
   slice, each carrying a `Status:` line and a checklist of acceptance criteria.
   A criterion is ticked when it was verified, not when it looked satisfied; a
   ticket that could not meet one says so in a `## Notes on delivery` section
   naming the ticket that later closed it. Tickets 02 and 03 are the worked
   examples — both waited on tables a later ticket created.
3. **The ADRs** — [docs/adr/](docs/adr/), one per decision that would be
   expensive to revisit, each with its rejected alternatives and its
   consequences.
4. **The commit history** — conventional commits (`feat:` / `fix:` /
   `refactor:` / `docs:`), one per slice, named for the ticket it delivers, so
   `git log` reads as the plan executing.

**There is no verbatim log of AI prompts, deliberately.** The process record is
the four artefacts above: they show the reasoning, the alternatives that were
rejected, and the order it all happened in. A raw transcript contains the same
information diluted by every false start and every retry, and buries the
decisions rather than revealing them. The conventions the agent worked under
are themselves checked in, in [CLAUDE.md](CLAUDE.md) and
[docs/agents/](docs/agents/).

## Out of scope

Everything the assignment lists under "Not to implement for now": messenger and
chat, sharing Posts, full-text user search (discovery is a paginated list of
people), groups, pages, reels, stories, any image or video upload — avatars are
generated from initials — and administration.

And, by decision rather than by omission:

- **Unfriending.** There is no transition out of an accepted Friendship
  ([ADR-0003](docs/adr/0003-friendship-is-one-directional-row.md)). The
  assignment lists it nowhere, and adding it means deciding what happens to the
  Posts each person has already read.
- **A retained `declined` state.** Rejecting a Friend Request deletes the row,
  so nothing records that a request was refused and nothing throttles a
  re-request. The invariant bought in exchange is that a Friendship row always
  means something positive, which every "are these two connected?" query relies
  on.
- **Editing and deleting Comments** — excluded by the assignment. Comments are
  append-only; they go only when their Post or their Author does.
- **Infinite scroll.** The Feed and Find People use offset pagination with a
  linkable `page` parameter ([ADR-0005](docs/adr/0005-two-lists-page-and-pages-arrive-as-an-envelope.md)).
- **Optimistic updates on the Like button.** The Like count comes from
  `withCount` on the server; mirroring it client-side would reintroduce exactly
  the synchronisation risk that computing the counts was meant to avoid. The
  request is a partial reload with `preserveScroll`, so acting does not lose
  your place.
- Notifications, realtime updates, tagging, hashtags and bookmarks, all filed
  as future extensions.

## What is not tested, and why

**Registration and login have no tests of ours.** They are Fortify's code
reached through the starter kit's own screens, and the kit ships tests for
them — `tests/Feature/Auth/`. Writing our own would test the framework rather
than this application. The kit's tests are kept and run in the suite; the only
substantive change made to them was repointing the post-authentication redirect from the
removed `dashboard` route to `feed`. This is a judgment, and it is written here
rather than left as a silent gap.

What is tested is where the risk actually is: the visibility rule, which is one
scope with four enforcement points and easy to forget one of, and the Friendship
lifecycle, whose three rules were invented in design and are not visible in the
schema. The spec's [Testing Decisions](docs/specs/mvp/spec.md#testing-decisions)
section lists them.

**The front end is covered by browser verification rather than by unit tests.**
There is no front-end test runner (see [Known gaps](#known-gaps)), so every
front-end change was instead driven through a real Chrome via the
[Chrome DevTools MCP](https://github.com/ChromeDevTools/chrome-devtools-mcp)
before its ticket was closed: the actual flow clicked through — write a Post,
Like it, Comment on it, send and accept a Friend Request, page the Feed — with
the console checked for React and Inertia errors and the network panel checked
for the status each request returned. That is weaker than component tests at
catching a regression later, and stronger than them at proving a screen really
renders and the wiring really works. It is the reason no shipped screen was
closed on "the code looks right".

## Known gaps

Nothing on the spec's cut list was cut — Post CRUD, the Friendship lifecycle,
visibility, the Feed, Likes, Comments, Profiles, pagination and the seeder all
shipped. These are the gaps that remain, listed so none of them is a silent
omission:

- **No front-end test runner.** There is no Vitest, no Testing Library and no
  Pest browser test, so no component is asserted to render. What stands in for
  it: each page's controller has a Pest test pinning the Inertia component name
  and the props the page reads, so the contract a component depends on breaks a
  test rather than a browser; and every front-end change was driven through a
  real browser over the Chrome DevTools MCP before its ticket was closed, as
  described above. Closing this properly
  is `npm i -D vitest @testing-library/react @testing-library/jest-dom jsdom`,
  a `vitest.config.ts` sharing Vite's aliases, and a test apiece for the shared
  components that hold logic — `RelationshipAction`'s four states, `LikeButton`,
  `Paginator` and `PostCard`'s ownership affordances — roughly half a day.
  Adding a dependency was out of scope for the assignment's time budget.
- **CI is manual-only.** `.github/workflows/tests.yml` runs `composer setup` and
  `composer ci:check` on `workflow_dispatch`; the `push` and `pull_request`
  triggers are commented out to keep an interview repository from burning
  Actions minutes on every push. Re-enabling it is uncommenting them.
- **Starter-kit leftovers.** `tests/Unit/ExampleTest.php` is the skeleton's, and
  the unused starter-kit layout variants (`app-header-layout`,
  `auth-card-layout`, `auth-split-layout`) are reachable from nothing — they are
  kept as options rather than deleted. Neither affects the application; both are
  noted rather than quietly left.
