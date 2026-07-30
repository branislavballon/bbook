<?php

namespace App\Http\Controllers;

use App\Enums\FriendshipStatus;
use App\Http\Resources\PersonResource;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The three sections of the friends destination. They are three real routes
 * rendering one page component with a variant, so each is linkable,
 * back-navigable and testable on its own.
 */
class FriendController extends Controller
{
    /**
     * The people this person is friends with.
     */
    public function index(Request $request): Response
    {
        $viewer = $request->user();

        // Matched on either side: someone who accepted this person's request
        // and someone whose request this person accepted are the same thing.
        $people = Friendship::query()
            ->involving($viewer)
            ->where('status', FriendshipStatus::Accepted)
            ->with(['requester', 'addressee'])
            ->get()
            ->map(fn (Friendship $friendship): PersonResource => new PersonResource(
                $friendship->counterpartFor($viewer),
                $friendship,
                $viewer,
            ))
            ->sortBy(fn (PersonResource $person): string => $person->name)
            ->values()
            ->all();

        return $this->section('friends', $people);
    }

    /**
     * The friend requests waiting for this person's answer. Requests this
     * person sent are not here — those show as pending in Find People.
     */
    public function requests(Request $request): Response
    {
        $viewer = $request->user();

        $people = Friendship::query()
            ->where('addressee_id', $viewer->id)
            ->where('status', FriendshipStatus::Pending)
            ->with('requester')
            ->latest()
            ->get()
            ->map(fn (Friendship $friendship): PersonResource => new PersonResource(
                $friendship->requester,
                $friendship,
                $viewer,
            ))
            ->values()
            ->all();

        return $this->section('requests', $people);
    }

    /**
     * Everyone on the network, and where the viewer stands with each of them,
     * a page at a time. This is the other list that grows without bound, so
     * it pages the way the feed does.
     */
    public function find(Request $request): Response
    {
        $viewer = $request->user();

        $friendships = Friendship::query()
            ->involving($viewer)
            ->get()
            ->keyBy(fn (Friendship $friendship): int => $friendship->counterpartIdFor($viewer));

        $people = User::query()
            ->whereKeyNot($viewer->id)
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->through(fn (User $person): PersonResource => new PersonResource(
                $person,
                $friendships->get($person->id),
                $viewer,
            ));

        return $this->section('find', PersonResource::collection($people));
    }

    /**
     * Render the friends page in one of its three variants.
     *
     * Find People arrives as a paginator envelope — `data`, `links`, `meta` —
     * while the two naturally small sections arrive as a plain list. The page
     * component reads the shape its variant implies.
     *
     * @param  array<int, PersonResource>|AnonymousResourceCollection  $people
     */
    private function section(string $variant, array|AnonymousResourceCollection $people): Response
    {
        return Inertia::render('friends/index', [
            'variant' => $variant,
            'people' => $people,
        ]);
    }
}
