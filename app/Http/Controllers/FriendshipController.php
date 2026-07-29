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

    /**
     * Accept a friend request. The pending row becomes the accepted one — the
     * relationship is a single row for its whole life, per ADR-0003, so there
     * is nothing to create and nothing to delete.
     */
    public function update(Friendship $friendship): RedirectResponse
    {
        $friendship->update(['status' => FriendshipStatus::Accepted]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Friend request accepted.')]);

        return back();
    }

    /**
     * Reject a friend request. The row goes, leaving no record that it was
     * refused: nothing lingers on either person's screen, and the requester
     * is free to ask again.
     */
    public function destroy(Friendship $friendship): RedirectResponse
    {
        $friendship->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Friend request rejected.')]);

        return back();
    }
}
