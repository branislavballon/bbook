<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $author
 * @property-read int $likes_count
 * @property-read int $comments_count
 * @property-read bool $liked
 */
#[Fillable(['body'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * The person who wrote this post.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The likes this post has collected.
     *
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * The comments written on this post.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Resolve the like state a post payload reads — how many likes it has, and
     * whether this viewer is one of them — in the query that loads the posts,
     * so neither is a question asked once per card.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeWithLikeState(Builder $query, User $viewer): void
    {
        $query->withCount('likes')->withExists(self::likedByConstraint($viewer));
    }

    /**
     * The same, for a post already fetched by route model binding.
     */
    public function loadLikeState(User $viewer): static
    {
        return $this->loadCount('likes')->loadExists(self::likedByConstraint($viewer));
    }

    /**
     * A post list as a screen needs it: the author for the byline, the counts
     * and the viewer's own like resolved in this one query, limited to what
     * the viewer may read, newest first.
     *
     * The feed and the post list on a profile differ only in whether they
     * narrow it to one author, so the rest is written once.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeReadableBy(Builder $query, User $viewer): void
    {
        $query->with('author')
            ->withCount('comments')
            ->withLikeState($viewer)
            ->visibleTo($viewer)
            ->latest();
    }

    /**
     * Limit to the posts this person is allowed to read: their own, and those
     * written by someone they are in an accepted friendship with, matched on
     * either side.
     *
     * Per ADR-0001 visibility is a privacy rule rather than a feed filter, so
     * this scope is the single place the rule is written. Everything that
     * reads posts — the feed, the detail lookup, PostPolicy::view — goes
     * through it instead of re-deriving it.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeVisibleTo(Builder $query, User $viewer): void
    {
        $author = $query->qualifyColumn('user_id');

        // Grouped, so a caller's own orWhere cannot widen its way past the
        // rule: everything else the query asks is ANDed with visibility.
        $query->where(function (Builder $query) use ($author, $viewer): void {
            $query->where($author, $viewer->id)
                ->orWhereExists(
                    Friendship::query()->acceptedBetween($viewer, $author)->getQuery()
                );
        });
    }

    /**
     * The `likes` constraint naming the viewer, shared by the query-time and
     * load-time halves of the like state so the alias is written once.
     *
     * @return array<string, callable(Builder<Like>): Builder<Like>>
     */
    private static function likedByConstraint(User $viewer): array
    {
        return [
            /** @param Builder<Like> $likes */
            'likes as liked' => fn (Builder $likes): Builder => $likes->whereBelongsTo($viewer),
        ];
    }
}
