<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $author
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
}
