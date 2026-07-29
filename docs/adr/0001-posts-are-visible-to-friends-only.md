# Posts are visible to friends only

The assignment constrains the feed to "own posts + friends' posts" but never says whether that is a privacy rule or merely a relevance filter. We decided it is a **privacy** rule: a Post is readable only by its Author and the Author's Friends. The alternative — public posts with a curated feed — was cheaper (one scope on the feed query instead of a visibility check threaded through post detail, profiles, likes and comments) but it would have made "friends" a mere follow relationship, and a Facebook clone whose posts are world-readable misrepresents the domain.

## Consequences

- Visibility is a single reusable Post scope, not an inline check, and it is applied by the feed, post detail, the profile post list, and every write that targets a Post (like, unlike, comment).
- `PostPolicy` gains a `view` ability alongside `update`/`delete`. Likes and comments authorize against the *parent Post's* visibility — otherwise a stranger could like a post they cannot read.
- The user list is weakened as a discovery mechanism: a stranger's profile shows a name and no posts, so the decision to send a Friend Request is made on identity alone. Accepted for the MVP.
