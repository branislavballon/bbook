<?php

namespace App\Http\Controllers;

use App\Enums\RelationshipState;
use App\Http\Resources\PersonResource;
use App\Http\Resources\PostResource;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Show a person's public profile: who they are, where the viewer stands
     * with them, and — if the visibility rule allows it — what they have
     * written. The viewer's own profile is this same page pointed at
     * themselves, so there is no separate screen for it.
     */
    public function show(Request $request, User $user): Response
    {
        $viewer = $request->user();
        $isSelf = $viewer->is($user);

        // Someone is never in a friendship with themselves, so the lookup is
        // skipped rather than relied upon to come back empty.
        $friendship = $isSelf
            ? null
            : Friendship::query()->between($viewer, $user->id)->first();

        $showsPosts = $isSelf
            || RelationshipState::forViewer($friendship, $viewer) === RelationshipState::Friends;

        return Inertia::render('users/show', [
            'person' => new PersonResource($user, $friendship, $viewer),
            // Withheld posts are null rather than an empty list, because the
            // two say different things: one that they are not for this viewer,
            // the other that there are none. `Post::scopeVisibleTo` cannot
            // tell them apart on its own — both come back empty — so the
            // friendship is asked about separately, and the scope still runs
            // to guarantee the rule rather than to discover it.
            'posts' => $showsPosts
                ? PostResource::collection(
                    Post::query()->whereBelongsTo($user, 'author')->readableBy($viewer)->get()
                )
                : null,
        ]);
    }
}
