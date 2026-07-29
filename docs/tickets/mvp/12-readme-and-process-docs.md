# 12 — README and process docs

**Status:** ready-for-agent

**Blocked by:** 11 — Demo seed data and empty states.

**Spec:** [MVP](../../specs/mvp/spec.md)

## What to build

A reviewer arriving at the repository gets from clone to a running, populated application in under a minute, and can then find the thinking behind it without reading the code. The README is a launcher and a map — not a description of features they can see for themselves.

It also has to say what was deliberately left out and why. That section is doing real work: it is the difference between "did not finish" and "decided".

## Acceptance criteria

- [ ] Setup is a short, copyable sequence that works from a fresh clone.
- [ ] The seeded demo credentials are stated.
- [ ] How to run the tests is stated.
- [ ] A decisions section links to the glossary, the spec and each ADR, summarising each in a line rather than restating it.
- [ ] The development process is described as spec, tickets with their status, ADRs and conventional commit history — and states plainly that no verbatim AI prompt log is kept, and why.
- [ ] An out-of-scope section lists the assignment's exclusions and, separately, the things excluded by decision: unfriending, a retained declined state, Comment editing, infinite scroll, optimistic Like updates.
- [ ] It states that registration and login tests were declined as framework coverage, with the reason.
- [ ] Anything cut under time pressure is listed explicitly with what it would have taken — no silent omissions.
- [ ] The glossary and ADRs are checked against what was actually built, and corrected where the code diverged.
