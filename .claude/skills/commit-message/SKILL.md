---
name: commit-message
description: Generate a commit message from the currently staged files. Use when the user asks to write a commit message, commit staged changes, or "commit". Inspects only what is staged (git diff --cached) and proposes a Conventional Commits message.
---

# commit-message

Write a commit message for the **staged** changes only.

## Steps

1. Check what is staged:
   ```bash
   git diff --cached --stat
   ```
   If nothing is staged, stop and tell the user to `git add` their changes first. Do **not** stage files yourself.

2. Read the staged diff to understand the change:
   ```bash
   git diff --cached
   ```

3. Compose a Conventional Commits message:
   - Subject line: `type(scope): summary`
     - `type`: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `ci`, `style`, `perf`, `build`
     - `scope` is optional — use the component or area touched (e.g. `Table`, `ci`).
     - Keep the summary in the imperative mood, lower-case, no trailing period, ≤ 72 chars.
   - Body (optional): wrap at ~72 chars, explain *why* when it isn't obvious from the diff.
   - Base the message strictly on what is staged — ignore unstaged and untracked files.

4. Show the proposed message to the user. Only run `git commit` if the user explicitly asks you to commit; otherwise just present the message.

## Notes

- One logical change per commit — if the staged diff spans unrelated concerns, say so and suggest splitting.
- Match the style of recent history:
  ```bash
  git log --oneline -10
  ```