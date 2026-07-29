# Hard deletes with database-level cascades

Deleting a Post or a User has to dispose of dependent rows, and the starter kit already hard-deletes Users from the profile settings screen. We chose **hard deletes everywhere, with `cascadeOnDelete()` declared on every foreign key**, over anonymizing authors or soft-deleting Users.

The deciding factor is type honesty: a Post's Author and a Comment's Author stay non-nullable, so no PHP type, React prop, or initials-avatar helper needs a "deleted user" branch. Soft deletes were rejected outright — they would put a scope obligation on every author query and would keep a departed user's email locked against re-registration.

## Consequences

- Every foreign key must be declared inside the `Schema::create` call that makes the table. SQLite cannot add a foreign key to an existing table without rebuilding it, so a later "add the constraint" migration is not an option.
- `friendships` needs the cascade on **both** `requester_id` and `addressee_id`; declaring it on only one side leaves orphans when the user on the other side is deleted.
- Deleting an account mutates other people's Posts: their comment and like counts drop. Accepted — with no comment threading in the MVP the gap is invisible.
- Verified on this project's SQLite: `foreign_key_constraints` is enabled and cascades fire, in both the file database and the `:memory:` test database.
