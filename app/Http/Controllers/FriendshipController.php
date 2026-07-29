<?php

namespace App\Http\Controllers;

use App\Enums\FriendshipStatus;
use App\Http\Requests\StoreFriendshipRequest;
use App\Models\Friendship;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class FriendshipController extends Controller
{
    /**
     * Send a friend request. StoreFriendshipRequest has already refused the
     * four cases the graph forbids, so this only has to write the row.
     */
    public function store(StoreFriendshipRequest $request): RedirectResponse
    {
        Friendship::create([
            'requester_id' => $request->user()->id,
            'addressee_id' => $request->validated('addressee_id'),
            'status' => FriendshipStatus::Pending,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Friend request sent.')]);

        return back();
    }
}
