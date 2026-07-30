# 14 — Composer feedback and post actions

**Status:** ready-for-agent

**Blocked by:** nothing.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

Two things the composer currently leaves the person to find out by being
refused.

**The length limit becomes visible.** A body is capped at 1000 characters, and
until now the only way to learn that was to exceed it and be told. The composer
states how many characters remain, and the textarea stops accepting input at
the cap rather than letting text run past it and failing on submit.

**The submit button stops offering to post nothing.** It is disabled while the
field is empty, so the round-trip that only ever comes back with "Write
something before posting." cannot be started.

The number is not written twice. The cap lives as a constant on `BodyRequest`,
which is where the validation rule already reads it from, and travels to each
screen that renders a composer as a prop — so the counter and the validator can
only ever disagree by someone changing one line and breaking a test.

All three composers get this, because there is only one: `PostForm` is shared
by the Feed, the edit page, and the Comment box on a Post's detail page, and
all three validate through the same rule. A person who has learned the
composer already understands the Comment box.

**Post actions become icons.** Edit and Delete on a post card lose their text
labels and keep their glyphs, each with a tooltip and — because a tooltip is
neither announced reliably nor reachable by touch — a visually hidden label
inside the button, following the pattern the header already uses.

## Implementation note

`PostForm` is uncontrolled today: it hands the textarea a `defaultValue` and
lets Inertia's `resetOnSuccess` clear it after a successful write. A counter
needs the current length in React state, and that reset does not touch React
state — so after a Post is published the counter will read the length of the
text that is no longer there unless the reset is handled explicitly. Whatever
the mechanism, the browser check must include writing a Post and confirming the
counter returns to its full figure.

## Acceptance criteria

- [ ] The 1000-character cap is declared once, on `BodyRequest`, and the validation rule reads it from there.
- [ ] The Feed, the post edit page and the Post detail page each pass the cap to their composer as a prop; a feature test asserts the prop on all three.
- [ ] The composer states the number of characters remaining, and the figure is correct as text is typed and deleted.
- [ ] The remaining-characters text is associated with the textarea for assistive technology, alongside the validation error that may also describe it — and does not narrate a number on every keystroke.
- [ ] The counter reaches zero and stops there; the textarea accepts no further input at the cap.
- [ ] After a Post is successfully published and the composer clears, the counter reads the full figure again — not the length of the text that was submitted.
- [ ] The submit button is disabled while the field is empty and while a submission is in flight; whitespace-only text counts as empty.
- [ ] The rule is the same on the edit page: empty disables, unchanged does not.
- [ ] Edit and Delete on a post card are icon-only, each with a tooltip.
- [ ] Each carries a visually hidden label, so its name does not depend on the tooltip appearing.
- [ ] Keyboard focus reaches both, and the delete confirmation dialog still opens from its icon.
- [ ] The frontend guardrails pass: `npm run types:check` and `npm run lint:check`.
- [ ] Verified in a browser on all three composers: typing to the cap, an empty field's disabled submit, publishing and watching the counter reset, and both post actions driven by mouse and by keyboard — with the console clean of React and Inertia errors and the write requests answering as expected.

## Consequence to record

Hard-capping the textarea puts the `max` half of the body rule beyond reach of
the interface: it can no longer be violated from a browser. The rule stays,
as the defence for anything that does not come from this frontend, and its
test stays with it — but nobody will see its message again.

## Deliberately not in this ticket

The post card's Comment count is labelled "1 comments" for a screen reader
where the Like button beside it pluralises correctly. Found during ticket 11's
audit, deferred there to a ticket of its own, and deferred again here — this
ticket touches the card's actions, not its counts. No ticket owns it yet.
