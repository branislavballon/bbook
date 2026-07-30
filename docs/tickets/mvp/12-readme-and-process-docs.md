# 12 — README and process docs

**Status:** resolved

**Blocked by:** 11 — Demo seed data and empty states.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A reviewer arriving at the repository gets from clone to a running, populated application in under a minute, and can then find the thinking behind it without reading the code. The README is a launcher and a map — not a description of features they can see for themselves.

It also has to say what was deliberately left out and why. That section is doing real work: it is the difference between "did not finish" and "decided".

## Acceptance criteria

- [x] Setup is a short, copyable sequence that works from a fresh clone — `composer setup` now migrates *and* seeds, so a reviewer lands on a populated application; the prerequisites (PHP, Composer, Node) are named and linked ahead of it.
- [x] The seeded demo credentials are stated, as a table of the two accounts and what each one demonstrates.
- [x] How to run the tests is stated — `php artisan test`, `composer ci:check`, and the four individual checks.
- [x] A decisions section links to the glossary, the spec and each of the five ADRs, one line apiece.
- [x] The development process is described as spec, tickets with their `Status:` line, ADRs and conventional commit history — and states plainly that no verbatim AI prompt log is kept, and why.
- [x] An out-of-scope section lists the assignment's exclusions and, separately, the things excluded by decision: unfriending, a retained declined state, Comment editing, infinite scroll, optimistic Like updates.
- [x] It states that registration and login tests were declined as framework coverage, with the reason and the one change made to the kit's own tests.
- [x] Anything cut under time pressure is listed explicitly with what it would have taken — the missing front-end test runner, manual-only CI, and the starter-kit leftovers.
- [x] The glossary and ADRs are checked against what was actually built, and corrected where the code diverged — `CONTEXT.md` gained *Stranger* and *Relationship State*, and *Comment* and *Feed* were corrected to match the cascade and the pagination that shipped.
