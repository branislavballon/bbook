# 16 — Avatars and likes get colour and motion

**Status:** resolved

**Blocked by:** nothing.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

Two pieces of presentation that the application has been doing correctly and
joylessly.

**The Like heart responds to the press, not to the server.** Liking is a
round-trip: the heart fills only once the server has answered, so the one
gesture in this network that is meant to feel immediate feels like a form
submission. The heart now pops the moment it is pressed — an overshoot that
settles, on a spring curve, around a third of a second — while the count goes
on waiting for the server, because the count is the truth and the animation is
not.

The motion belongs to liking, not to unliking. Withdrawing approval eases the
fill away and nothing more; a celebration for taking something back reads as
wrong, and reserving the pop is what makes it mean anything. It respects
`prefers-reduced-motion`, where it degrades to the plain fill it does today.

**Avatars take a colour from the person.** Every avatar in the application is
the same grey, so a friends list is a column of identical circles distinguished
only by two letters. Each person's avatar now draws a colour from a small fixed
palette, chosen by their name, so the same person is the same colour on every
screen and two people rarely look alike.

The colour is taken from the **whole name**, not from the initials it displays.
Initials collide constantly — Jan Novák and Jana Nemcová are both `JN` — and a
colour that collides with them distinguishes letter-pairs rather than people,
which is the opposite of what it is for. The initials remain what is shown; the
name is what is read to choose the colour.

**The header stops being the exception.** The user menu builds its own avatar
by hand instead of using the shared component, so it would have stayed grey
while every other avatar in the application took a colour — including the
viewer's own face, on their own Profile, beside a grey copy of itself in the
chrome above. It uses the shared component like everything else. Its hand-built
markup also carries an image element that can never resolve, because nothing in
this application is uploaded; that goes with it, and the shared component's
claim that there are no uploaded images anywhere finally holds across the whole
interface.

## Implementation note

**The pop must fire on a transition, not on a state.** A class applied whenever
the post is liked replays every time the component renders with that value
true — returning to the Feed, paging, another post's partial reload — so hearts
would pop unbidden all over a list nobody touched. The button therefore holds
its own short-lived "just pressed" flag, set on the press and cleared when the
animation ends. Everything else it knows still comes from the server.

**This is not an optimistic update, and must not become one.** `README.md`
records the decision that the Like count is not mirrored client-side, because
mirroring it reintroduces exactly the synchronisation risk that computing the
counts with `withCount` was meant to remove. That decision stands: the gesture
is local, the number is not. Inertia's `optimistic` is deliberately unused
here.

**Tailwind cannot build a class name at runtime.** The palette has to be full
literal class strings — a constructed `bg-${name}-100` compiles to nothing —
so it is a frozen list of complete entries, each carrying background and
foreground for light and dark, each checked against its own text for contrast
before it goes in the list. Around ten is enough that a list looks varied
without two entries being confusable.

## How this is verified

Neither half changes anything crossing the Inertia boundary — no new props, no
new routes, no controller touched — so no feature test can reach either of
them, and there is no frontend test runner in this project. Adding one is a
dependency decision that does not belong in this ticket. The browser check is
therefore not a formality here; it is the only evidence this ticket produces,
and it must actually be driven rather than assumed.

## Acceptance criteria

- [x] Pressing Like pops the heart immediately, before the server has answered.
- [x] The count still comes from the server and is never mirrored client-side.
- [x] Unliking does not pop; the fill eases away.
- [x] The pop does not replay on re-render — paging the Feed, opening a Post and returning, or liking one post leaves every other heart still.
- [x] Under `prefers-reduced-motion` the heart changes state without animating.
- [x] The Like button's accessible name and pressed state are unchanged.
- [x] Each person's avatar has a colour taken from their whole name, and the same person is the same colour on every screen they appear on.
- [x] Two people with the same initials get different colours.
- [x] Every palette entry is legible in both light and dark mode, against its own initials.
- [x] The user menu's avatar uses the shared component and takes a colour like every other avatar.
- [x] No avatar anywhere attempts to load an image.
- [x] The frontend guardrails pass: `npm run types:check` and `npm run lint:check`.
- [x] Verified in a browser: liking and unliking on both the Feed and a Post's detail page, a Feed and a friends list showing visibly varied avatar colours, and the same person's colour matching between a list, their Profile and the header — in both light and dark mode, with the console clean of React and Inertia errors and the Like requests answering as expected.
