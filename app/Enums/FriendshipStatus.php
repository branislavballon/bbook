<?php

namespace App\Enums;

/**
 * Where a friendship stands. There is no `declined`: rejecting a request
 * deletes the row, so the existence of a friendship always means something
 * positive, per ADR-0003.
 */
enum FriendshipStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
}
