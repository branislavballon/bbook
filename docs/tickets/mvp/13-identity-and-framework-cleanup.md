# 13 — Identity and framework cleanup

**Status:** resolved

**Blocked by:** nothing.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

The application stops looking like a starter kit and starts looking like a
small social network called **Bbook**. Nothing a visitor or a logged-in person
sees names the framework it is built on: not the page they land on, not the tab
title, not the icon in that tab, not the navigation.

The root URL is the first thing to go. It renders the kit's marketing page —
framework wordmark, documentation links, "Deploy now" — and under
[ADR-0006](../../adr/0006-the-root-url-is-an-auth-gate.md) it becomes a
redirect instead: a guest to login, an authenticated person to the Feed. The
login screen therefore becomes the front door and is given the application's
mark and name.

The identity itself is a book with spectacles resting on it, drawn as one
solid monochrome path so it inherits colour from wherever it is placed, and a
wordmark whose first letter is accented — derived from the name rather than
written out, so it survives the name changing.

The framework's own references in the navigation — a Repository link and a
Documentation link, present twice — go. So does the group label "Platform",
which describes nothing in a social network.

This ticket is presentation only. No route gains or loses a policy, no query
changes, and the only new test is the one the redirect makes necessary.

## Scope note

Framework references in **prose** stay: `README.md`, `docs/`, the ADRs and this
spec all discuss the stack deliberately, because the assignment asks for
documented technology and decisions. The rule applies to the running interface.

## Acceptance criteria

- [x] `/` no longer renders a page: a guest is redirected to login, an authenticated person to the Feed.
- [x] The `home` route name survives, so `home()` still resolves for Wayfinder and the layouts that use it.
- [x] `resources/js/pages/welcome.tsx` no longer exists.
- [x] The application is named `Bbook` wherever the name can be read: `.env.example`, the `config/app.php` default, and the frontend's build-time fallback — a clone with no `.env` never displays the framework's name.
- [x] `AppLogoIcon` draws a book with spectacles as a solid monochrome path, and every existing consumer of it renders correctly without being changed.
- [x] The mark is legible at the sidebar's size, not only at the auth screen's size.
- [x] `public/favicon.svg` is the same mark; no stale framework icon is served or referenced from the document head.
- [x] The logo is mark plus wordmark, the wordmark's accent derived from the first character of the application's name rather than hardcoded.
- [x] The auth screens show mark and wordmark, and the logo is not a link — for a guest, `home` is login, so linking it would land on itself.
- [x] Neither the sidebar nor the header carries a Repository or Documentation link, and neither file contains the URLs any more.
- [x] The main navigation group is not labelled "Platform".
- [x] `README.md` names the application `Bbook`.
- [x] A feature test covers the redirect from both sides — guest to login, authenticated person to the Feed — replacing the kit's test that asserted `/` answered 200.
- [x] The frontend guardrails pass: `npm run types:check` and `npm run lint:check`.
- [x] Verified in a browser: the redirect from `/` as both a guest and a signed-in person, the logo in the sidebar and on the login screen, the tab title and favicon, and a sidebar with no framework links — with the console clean of React and Inertia errors.

## Deliberately not in this ticket

- **Open Graph and meta description tags.** The document head has neither, so a
  pasted link renders as a bare URL. Considered and left out; no ticket owns it
  yet.
- **The unused starter-kit layout variants.** `app-header-layout`,
  `app-header`, `nav-footer`, `auth-card-layout` and `auth-split-layout` are
  reachable from nothing — `app-layout` uses the sidebar variant. They stay,
  kept as options; the two link arrays inside them are emptied so the framework
  URLs do not linger in a file nobody reads.
- **A copy pass** over the auth screens and empty states in the application's
  own voice.

## Notes on delivery

**The wordmark's accent is weight, not colour.** The theme is deliberately
monochrome — `--primary` is near-black in light and near-white in dark, which
is the surrounding foreground in both — so `text-primary` on the first
character would have been invisible. The accent is `font-extrabold` on the
first character against `font-medium` for the rest. Introducing a brand hue is
a design decision this ticket did not authorise; if one is wanted, it changes
one line in `app-wordmark.tsx`.

**`favicon.ico` and `apple-touch-icon.png` were deleted, not redrawn.** Both
were the framework's mark, and both were referenced from the document head.
The head now carries `favicon.svg` alone and nothing requests the other two,
which no longer exist. A raster apple-touch icon would need a rasteriser and
is not in scope; no ticket owns it yet.

**The favicon carries a dark plate the component's mark does not.** A favicon
has no `currentColor` to inherit, and a bare monochrome mark disappears
against a tab strip of the same shade, so `public/favicon.svg` sets the mark
in white on a rounded `#171717` square — the same treatment the sidebar gives
it. The consequence is that the path is now maintained in two files; there is
no build step emitting one from the other, so a change to the mark has to be
made in both.

**`app-header`'s `rightNavItems` is an empty array rather than removed.** The
header shell is one of the unused layout variants this ticket keeps as an
option, so the mapping code stays and only the framework URLs go.

**The review pass changed three things beyond the criteria.** All small, all
consequences of what the ticket asked for:

- `/` is `HomeController` rather than a closure. A closure route cannot be
  serialised, so `/` would have thrown on `php artisan route:cache` — verified
  by running it.
- `auth-card-layout` and `auth-split-layout` had the same `home()` logo link
  the criteria removed from `auth-simple-layout`, which ADR-0006 calls a loop.
  Unlinked, and both now show mark plus wordmark. Only the simple variant is
  reachable, so this is unverifiable in a browser and rests on the type check.
- `FeedTest` now asserts the shared `name` prop, which `AppWordmark` reads to
  derive its accent and would throw on if it were ever dropped.
