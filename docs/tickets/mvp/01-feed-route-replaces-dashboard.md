# 01 — Feed route replaces dashboard

**Status:** ready-for-agent

**Blocked by:** None — can start immediately.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

After logging in, a person lands on the Feed rather than a placeholder dashboard. The Feed has nothing in it yet, so it shows an explanatory empty state pointing at where people are found. An unauthenticated visitor asking for the Feed is sent to login.

This is a prefactor: it clears the landing slot and the naming collision before any feature work depends on either. `profile.*` is already owned by the settings screens, and `dashboard` currently means "the page you land on", which stops being true the moment the Feed exists.

## Acceptance criteria

- [ ] A named `feed` route renders a Feed page for authenticated people.
- [ ] The `dashboard` route and its placeholder page no longer exist.
- [ ] The post-login redirect points at the Feed.
- [ ] Any remaining reference to the dashboard — navigation, redirects, generated route helpers — is gone.
- [ ] The Feed shows an empty state explaining that it is empty and linking to where people are found.
- [ ] Navigation shows the three MVP destinations, with the starter kit's user menu untouched for settings and logout.
- [ ] The existing dashboard test is replaced by an equivalent Feed test: a guest is redirected to login, an authenticated person gets the page.
- [ ] The full test suite passes; nothing still references the removed route.
