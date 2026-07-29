<?php

namespace App\Enums;

use App\Models\Friendship;
use App\Models\User;

/**
 * Where one person stands with another, computed server-side so no screen has
 * to reason about the friendship graph. Every list row and every profile
 * receives one of these four values and switches on it.
 */
enum RelationshipState: string
{
    case None = 'none';
    case RequestSent = 'request_sent';
    case RequestReceived = 'request_received';
    case Friends = 'friends';

    /**
     * Read a friendship — or the absence of one — from the viewer's side.
     */
    public static function forViewer(?Friendship $friendship, User $viewer): self
    {
        if ($friendship === null) {
            return self::None;
        }

        if ($friendship->status === FriendshipStatus::Accepted) {
            return self::Friends;
        }

        return $friendship->requester_id === $viewer->id
            ? self::RequestSent
            : self::RequestReceived;
    }
}
