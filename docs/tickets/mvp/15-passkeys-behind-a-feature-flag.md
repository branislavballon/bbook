# 15 — Passkeys behind a feature flag

**Status:** ready-for-agent

**Blocked by:** nothing.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

The starter kit ships passkey sign-in. Nothing in the assignment asks for it,
nothing in the spec describes it, and it costs the login screen, the password
confirmation screen and the security settings page real surface — three screens
a reviewer reads before they reach anything this application was built to do.
It goes.

**The feature becomes a flag.** `FORTIFY_PASSKEYS_ENABLED` defaults to `false`,
in `config/fortify.php` and in `.env.example` both, and `Features::passkeys()`
is registered only when it is true. The default is off, so a fresh clone is a
passwords-only application without anyone having to configure it that way.

**The interface goes entirely.** All four passkey components —
`passkey-verify`, `passkey-register`, `passkey-item`, `manage-passkeys` — are
deleted, along with their use on the login page, the password confirmation page
and the security settings page. `SecurityController` stops sending
`canManagePasskeys` and `passkeys`, because with `ManagePasskeys` gone nothing
reads them, and a page that receives props no component consumes reads as an
oversight rather than a decision.

**The discovery endpoint goes with it.** `.well-known/passkey-endpoints` is
ours, not Fortify's, and it tells browsers and password managers to enrol
passkeys at `/settings/security` — a page that will no longer enrol anything.
An endpoint that lies to a user agent is worse than no endpoint.

**The schema stays.** The `passkeys` table and its migration, the `PasskeyUser`
interface and the `PasskeyAuthenticatable` trait on `User`, and the `passkeys`
rate limiter are all left where they are. They are inert with the feature off
and they are precisely what the flag turns back on.

## Implementation note

`resources/js/actions` and `resources/js/routes` are gitignored and generated
by Wayfinder from the registered routes. Turning `Features::passkeys()` off
unregisters the passkey routes, so those generated modules stop existing — and
two files import them today: `manage-passkeys.tsx` (from
`PasskeyRegistrationController`) and `confirm-password.tsx` (from
`PasskeyConfirmationController`). This is why deleting the interface is not
optional cleanup but the thing that keeps a default checkout compiling. Verify
by running `npm run types:check` on a tree where the flag is off; a stale
`resources/js/actions` directory left over from an earlier build will hide the
failure, so clear it first.

`SecurityController` already guards its passkey props with
`Features::canManagePasskeys()`, so the backend degrades on its own — the props
are removed because nothing consumes them, not because they would break.

## What the flag is honestly worth

With no interface left, flipping `FORTIFY_PASSKEYS_ENABLED` back on restores
working endpoints and a live table, not a working feature — someone would have
to build the screens again. The flag is kept anyway because it is the smallest
thing that keeps the removal a configuration change rather than an amputation,
and because it is the only marker in the codebase saying the `passkeys` table
is dormant by choice.

Recorded here rather than as an ADR, by decision: this is the removal of
something the assignment never asked for, not an architectural choice about
what this application is. A reader who finds the `passkeys` table and wonders
why it is empty is meant to find their way here.

## Acceptance criteria

- [ ] `FORTIFY_PASSKEYS_ENABLED` exists, defaults to `false`, and appears in `.env.example`.
- [ ] `Features::passkeys()` is registered only when the flag is true.
- [ ] The four passkey components are deleted, and no file imports them.
- [ ] The login page and the password confirmation page offer no passkey path.
- [ ] The security settings page manages passwords only, and `SecurityController` sends neither `canManagePasskeys` nor `passkeys`.
- [ ] The `.well-known/passkey-endpoints` route is gone.
- [ ] The `passkeys` migration, the `PasskeyUser` interface and `PasskeyAuthenticatable` trait on `User`, and the `passkeys` rate limiter are all still present.
- [ ] `SecurityTest` keeps both affected tests, stripped of their passkey assertions and `Features::passkeys()` setup; the second is renamed, because "without two factor" no longer describes anything.
- [ ] `php artisan test --compact --filter=Security` is green, as is the wider auth suite.
- [ ] `vendor/bin/pint --dirty --format agent` reports clean.
- [ ] The frontend guardrails pass on a tree with the flag off and a cleared `resources/js/actions`: `npm run types:check` and `npm run lint:check`.
- [ ] Verified in a browser: signing in with a password, confirming a password on the way to security settings, and the security page itself — with the console clean of React and Inertia errors and every request answering as expected.
