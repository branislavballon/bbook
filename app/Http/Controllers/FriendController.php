<?php

namespace App\Http\Controllers;

use App\Enums\FriendshipStatus;
use App\Enums\RelationshipState;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The three sections of the friends destination. They are three real routes
 * rendering one page component with a variant, so each is linkable,
 * back-navigable and testable on its own.
 *
 * @phpstan-type PersonPayload array{id: int, name: string, relationship_state: string, friendship_id: int|null}
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
            ->map(fn (Friendship $friendship): array => $this->personPayload(
                $friendship->counterpartFor($viewer),
                $friendship,
                $viewer,
            ))
            ->sortBy('name')
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
            ->map(fn (Friendship $friendship): array => $this->personPayload(
                $friendship->requester,
                $friendship,
                $viewer,
            ))
            ->values()
            ->all();

        return $this->section('requests', $people);
    }

    /**
     * Everyone on the network, and where the viewer stands with each of them.
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
            ->get()
            ->map(fn (User $person): array => $this->personPayload(
                $person,
                $friendships->get($person->id),
                $viewer,
            ))
            ->values()
            ->all();

        return $this->section('find', $people);
    }

    /**
     * The shape every friends list reads a person in. The friendship's id
     * rides along so a row can answer the request it is showing.
     *
     * @return PersonPayload
     */
    private function personPayload(User $person, ?Friendship $friendship, User $viewer): array
    {
        return [
            'id' => $person->id,
            'name' => $person->name,
            'relationship_state' => RelationshipState::forViewer($friendship, $viewer)->value,
            'friendship_id' => $friendship?->id,
        ];
    }

    /**
     * Render the friends page in one of its three variants.
     *
     * @param  array<int, PersonPayload>  $people
     */
    private function section(string $variant, array $people): Response
    {
        return Inertia::render('friends/index', [
            'variant' => $variant,
            'people' => $people,
        ]);
    }
}
