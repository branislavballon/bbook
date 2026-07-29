<?php

namespace App\Policies;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\User;

class FriendshipPolicy
{
    /**
     * Determine whether the person can answer this friend request.
     *
     * Only the addressee decides — the requester accepting their own request
     * would make friendship unilateral. The request must also still be
     * pending: rejection deletes the row, so responding to an accepted
     * friendship would be unfriending, which ADR-0003 excludes.
     */
    public function respond(User $user, Friendship $friendship): bool
    {
        return $friendship->addressee_id === $user->id
            && $friendship->status === FriendshipStatus::Pending;
    }
}
