<?php

namespace App\Models;

use App\Enums\FriendshipStatus;
use Database\Factories\FriendshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One row for the whole life of a relationship, per ADR-0003: direction is
 * preserved because a pending request needs to know who asked, and rejection
 * deletes the row rather than recording a status.
 *
 * @property int $id
 * @property int $requester_id
 * @property int $addressee_id
 * @property FriendshipStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $requester
 * @property-read User $addressee
 */
#[Fillable(['requester_id', 'addressee_id', 'status'])]
class Friendship extends Model
{
    /** @use HasFactory<FriendshipFactory> */
    use HasFactory;

    /**
     * The person who sent the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * The person the request was sent to.
     *
     * @return BelongsTo<User, $this>
     */
    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /**
     * Limit to friendships this person appears in, on either side. No query
     * about the graph may assume the person is the requester.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeInvolving(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('requester_id', $user->id)
                ->orWhere('addressee_id', $user->id);
        });
    }

    /**
     * Limit to the single friendship between two people, whoever asked.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeBetween(Builder $query, User $viewer, int $otherId): void
    {
        $query->whereIn('requester_id', [$viewer->id, $otherId])
            ->whereIn('addressee_id', [$viewer->id, $otherId]);
    }

    /**
     * The other person in this friendship, seen from one side of it.
     */
    public function counterpartIdFor(User $user): int
    {
        return $this->isRequester($user) ? $this->addressee_id : $this->requester_id;
    }

    /**
     * The other person themselves. Friendship reads as mutual, so no list
     * built from this asks which side the viewer is on.
     *
     * Takes the id-only variant's place wherever the person is wanted rather
     * than their key; both relations must be loaded to avoid a query per row.
     */
    public function counterpartFor(User $user): User
    {
        return $this->isRequester($user) ? $this->addressee : $this->requester;
    }

    /**
     * Which side of the friendship this person is on.
     */
    private function isRequester(User $user): bool
    {
        return $this->requester_id === $user->id;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FriendshipStatus::class,
        ];
    }
}
