# 01 — Feed route replaces dashboard

**Status:** ready-for-agent

**Blocked by:** None — can start immediately.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

After logging in, a person lands on the Feed rather than a placeholder dashboard. The Feed has nothing in it yet, so it shows an explanatory empty state pointing at where people are found. An unauthenticated visitor asking for the Feed is sent to login.

This is a prefactor: it clears the landing slot and the naming collision before any feature work depends on either. `profile.*` is already owned by the settings screens, and `dashboard` currently means "the page you land on", which stops being true the moment the Feed exists.

## Acceptance criteria

- [x] A named `feed` route renders a Feed page for authenticated people.
- [x] The `dashboard` route and its placeholder page no longer exist.
- [x] The post-login redirect points at the Feed.
- [x] Any remaining reference to the dashboard — navigation, redirects, generated route helpers — is gone.
- [x] The Feed shows an empty state explaining that it is empty. *Linking to where people are found is deferred — `friends.find` does not exist until ticket 04.*
- [x] Navigation shows the Feed destination, with the starter kit's user menu untouched for settings and logout. *Friends and My Profile are deferred to tickets 04 and 09, which own those routes.*
- [x] The existing dashboard test is replaced by an equivalent Feed test: a guest is redirected to login, an authenticated person gets the page.
- [x] The full test suite passes; nothing still references the removed route.

## Deferred out of this ticket

Two acceptance criteria assumed routes that later tickets own, so linking to them here would ship broken links:

- **Ticket 04** must add the `Friends` navigation item and wire the Feed empty state's link to `friends.find`.
- **Ticket 09** must add the `My Profile` navigation item pointing at `users.show` for the current person.
