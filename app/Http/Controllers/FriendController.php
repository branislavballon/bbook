<?php

namespace App\Http\Controllers;

use App\Enums\RelationshipState;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The three sections of the friends destination. They are three real routes
 * rendering one page component with a variant, so each is linkable,
 * back-navigable and testable on its own.
 *
 * @phpstan-type PersonPayload array{id: int, name: string, relationship_state: string}
 */
class FriendController extends Controller
{
    /**
     * The people this person is friends with.
     */
    public function index(): Response
    {
        // Accepting a request is ticket 05; until it exists nothing can reach
        // the accepted state through the interface, so this section is its
        // empty state and nothing else.
        return $this->section('friends', collect());
    }

    /**
     * The friend requests waiting for this person's answer.
     */
    public function requests(): Response
    {
        // Responding to a request is ticket 05, as above.
        return $this->section('requests', collect());
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
            ));

        return $this->section('find', $people);
    }

    /**
     * The shape every friends list reads a person in.
     *
     * @return PersonPayload
     */
    private function personPayload(User $person, ?Friendship $friendship, User $viewer): array
    {
        return [
            'id' => $person->id,
            'name' => $person->name,
            'relationship_state' => RelationshipState::forViewer($friendship, $viewer)->value,
        ];
    }

    /**
     * Render the friends page in one of its three variants.
     *
     * @param  Collection<int, PersonPayload>  $people
     */
    private function section(string $variant, Collection $people): Response
    {
        return Inertia::render('friends/index', [
            'variant' => $variant,
            'people' => $people->values(),
        ]);
    }
}
