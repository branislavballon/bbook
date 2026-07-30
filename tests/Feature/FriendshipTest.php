<?php

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page from every friends section', function () {
    $this->get(route('friends.index'))->assertRedirect(route('login'));
    $this->get(route('friends.requests'))->assertRedirect(route('login'));
    $this->get(route('friends.find'))->assertRedirect(route('login'));
});

test('the three friends sections render one page with their own variant', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $variants = [
        'friends.index' => 'friends',
        'friends.requests' => 'requests',
        'friends.find' => 'find',
    ];

    foreach ($variants as $route => $variant) {
        $this->get(route($route))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('friends/index')
                ->where('variant', $variant)
                ->has('people')
            );
    }
});

test('the friends and requests sections are empty for someone with no relationships', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('friends.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 0));

    $this->actingAs($user)->get(route('friends.requests'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 0));
});

test('find people lists everyone on the network except the viewer', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => 'Ada Lovelace']);
    User::factory()->create(['name' => 'Zoe Zeta']);

    $this->actingAs($user)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('variant', 'find')
            ->has('people.data', 2)
            ->where('people.data.0.id', $other->id)
            ->where('people.data.0.name', 'Ada Lovelace')
            ->where('people.data.0.relationship_state', 'none')
            // The viewer is never in their own list, so the profile page's
            // flag is always false on a row.
            ->where('people.data.0.is_self', false)
        );
});

test('find people returns ten people per page and the eleventh on the second page', function () {
    $viewer = User::factory()->create();

    // Ordered by name, so the sequence is the page order and the eleventh
    // person is the only one on page two.
    $people = collect(range(1, 11))->map(fn (int $position): User => User::factory()
        ->create(['name' => sprintf('Person %02d', $position)]));

    $this->actingAs($viewer)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('people.data', 10)
            ->where('people.data.0.id', $people->first()->id)
            ->where('people.meta.current_page', 1)
            ->where('people.meta.last_page', 2)
            ->where('people.meta.total', 11)
        );

    $this->actingAs($viewer)->get(route('friends.find', ['page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('people.data', 1)
            ->where('people.data.0.id', $people->last()->id)
            ->where('people.meta.current_page', 2)
        );
});

test('the friends and requests sections stay unpaginated', function () {
    $viewer = User::factory()->create();

    User::factory()->count(12)->create()->each(function (User $person) use ($viewer): void {
        Friendship::factory()->accepted()->create([
            'requester_id' => $person->id,
            'addressee_id' => $viewer->id,
        ]);
    });

    User::factory()->count(12)->create()->each(function (User $person) use ($viewer): void {
        Friendship::factory()->pending()->create([
            'requester_id' => $person->id,
            'addressee_id' => $viewer->id,
        ]);
    });

    // A plain list rather than a paginator envelope, and all of it.
    $this->actingAs($viewer)->get(route('friends.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 12));

    $this->actingAs($viewer)->get(route('friends.requests'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 12));
});

test('find people shows the current relationship state per row', function () {
    $user = User::factory()->create();
    $asked = User::factory()->create(['name' => 'A Asked']);
    $asker = User::factory()->create(['name' => 'B Asker']);
    $friend = User::factory()->create(['name' => 'C Friend']);

    Friendship::factory()->pending()->create([
        'requester_id' => $user->id,
        'addressee_id' => $asked->id,
    ]);

    $incoming = Friendship::factory()->pending()->create([
        'requester_id' => $asker->id,
        'addressee_id' => $user->id,
    ]);

    Friendship::factory()->accepted()->create([
        'requester_id' => $friend->id,
        'addressee_id' => $user->id,
    ]);

    $this->actingAs($user)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('people.data', 3)
            ->where('people.data.0.relationship_state', 'request_sent')
            ->where('people.data.1.relationship_state', 'request_received')
            ->where('people.data.2.relationship_state', 'friends')
            // The row carries what it needs to answer the request, so the
            // incoming request is actionable here and not only in Requests.
            ->where('people.data.1.friendship_id', $incoming->id)
        );
});

test('a person can send a friend request and the row turns pending', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $response = $this->actingAs($user)->post(route('friendships.store'), [
        'addressee_id' => $other->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.toast.message', 'Friend request sent.');

    $friendship = Friendship::sole();
    expect($friendship->requester_id)->toBe($user->id)
        ->and($friendship->addressee_id)->toBe($other->id)
        ->and($friendship->status)->toBe(FriendshipStatus::Pending);

    $this->actingAs($user)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('people.data.0.relationship_state', 'request_sent')
        );
});

test('a friend request to yourself is refused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('friendships.store'), ['addressee_id' => $user->id])
        ->assertSessionHasErrors('addressee_id');

    expect(Friendship::count())->toBe(0);
});

test('a second friend request to the same person is refused', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Friendship::factory()->pending()->create([
        'requester_id' => $user->id,
        'addressee_id' => $other->id,
    ]);

    $this->actingAs($user)
        ->post(route('friendships.store'), ['addressee_id' => $other->id])
        ->assertSessionHasErrors('addressee_id');

    expect(Friendship::count())->toBe(1);
});

test('a friend request to someone who already has a pending request out to you is refused', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Friendship::factory()->pending()->create([
        'requester_id' => $other->id,
        'addressee_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->post(route('friendships.store'), ['addressee_id' => $other->id]);

    $response->assertSessionHasErrors([
        'addressee_id' => 'This person has already sent you a friend request. Respond to it instead.',
    ]);

    expect(Friendship::count())->toBe(1);
});

test('a friend request to an existing friend is refused', function () {
    $user = User::factory()->create();
    $friend = User::factory()->create();

    Friendship::factory()->accepted()->create([
        'requester_id' => $friend->id,
        'addressee_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('friendships.store'), ['addressee_id' => $friend->id])
        ->assertSessionHasErrors('addressee_id');

    expect(Friendship::count())->toBe(1);
});

test('a friend request to someone who does not exist is refused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('friendships.store'), ['addressee_id' => 999])
        ->assertSessionHasErrors('addressee_id');

    expect(Friendship::count())->toBe(0);
});

test('guests cannot send a friend request', function () {
    $other = User::factory()->create();

    $this->post(route('friendships.store'), ['addressee_id' => $other->id])
        ->assertRedirect(route('login'));

    expect(Friendship::count())->toBe(0);
});

test('the identical friend request cannot be stored twice', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Friendship::factory()->pending()->create([
        'requester_id' => $user->id,
        'addressee_id' => $other->id,
    ]);

    expect(fn () => Friendship::factory()->pending()->create([
        'requester_id' => $user->id,
        'addressee_id' => $other->id,
    ]))->toThrow(QueryException::class);
});

test('deleting a person deletes every friendship they appear in on either side', function () {
    $user = User::factory()->create();

    Friendship::factory()->pending()->create([
        'requester_id' => $user->id,
        'addressee_id' => User::factory(),
    ]);

    Friendship::factory()->accepted()->create([
        'requester_id' => User::factory(),
        'addressee_id' => $user->id,
    ]);

    expect(Friendship::count())->toBe(2);

    $user->delete();

    expect(Friendship::count())->toBe(0);
});

test('only the addressee can accept a friendship; the requester is refused', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
    ]);

    $this->actingAs($requester)
        ->patch(route('friendships.update', $friendship))
        ->assertForbidden();

    expect($friendship->fresh()->status)->toBe(FriendshipStatus::Pending);

    $this->actingAs($addressee)
        ->patch(route('friendships.update', $friendship))
        ->assertRedirect();

    expect($friendship->fresh()->status)->toBe(FriendshipStatus::Accepted);
});

test('accepting moves the existing row to accepted rather than making a new one', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
    ]);

    $response = $this->actingAs($addressee)->patch(route('friendships.update', $friendship));

    $response->assertSessionHas('inertia.flash_data.toast.message', 'Friend request accepted.');

    expect(Friendship::count())->toBe(1)
        ->and(Friendship::sole()->id)->toBe($friendship->id)
        ->and(Friendship::sole()->requester_id)->toBe($requester->id);
});

test('rejecting a friend request removes the row entirely', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
    ]);

    $response = $this->actingAs($addressee)->delete(route('friendships.destroy', $friendship));

    $response->assertSessionHas('inertia.flash_data.toast.message', 'Friend request rejected.');

    expect(Friendship::count())->toBe(0);
});

test('only the addressee can reject a friendship', function () {
    $requester = User::factory()->create();
    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $requester->id,
        'addressee_id' => User::factory(),
    ]);

    $this->actingAs($requester)
        ->delete(route('friendships.destroy', $friendship))
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->delete(route('friendships.destroy', $friendship))
        ->assertForbidden();

    expect(Friendship::count())->toBe(1);
});

test('an accepted friendship can no longer be responded to, so rejection is not unfriending', function () {
    $addressee = User::factory()->create();

    $friendship = Friendship::factory()->accepted()->create([
        'requester_id' => User::factory(),
        'addressee_id' => $addressee->id,
    ]);

    $this->actingAs($addressee)
        ->delete(route('friendships.destroy', $friendship))
        ->assertForbidden();

    $this->actingAs($addressee)
        ->patch(route('friendships.update', $friendship))
        ->assertForbidden();

    expect(Friendship::count())->toBe(1);
});

test('guests cannot respond to a friend request', function () {
    $friendship = Friendship::factory()->pending()->create();

    $this->patch(route('friendships.update', $friendship))->assertRedirect(route('login'));
    $this->delete(route('friendships.destroy', $friendship))->assertRedirect(route('login'));

    expect($friendship->fresh()->status)->toBe(FriendshipStatus::Pending);
});

test('the requests section lists incoming pending requests with who sent them', function () {
    $viewer = User::factory()->create();
    $asker = User::factory()->create(['name' => 'Ada Lovelace']);

    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $asker->id,
        'addressee_id' => $viewer->id,
    ]);

    // A request the viewer sent, and one between two other people: neither is
    // waiting for this person's answer.
    Friendship::factory()->pending()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => User::factory(),
    ]);
    Friendship::factory()->pending()->create();

    $this->actingAs($viewer)->get(route('friends.requests'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('variant', 'requests')
            ->has('people', 1)
            ->where('people.0.id', $asker->id)
            ->where('people.0.name', 'Ada Lovelace')
            ->where('people.0.relationship_state', 'request_received')
            ->where('people.0.friendship_id', $friendship->id)
        );
});

test('an accepted request leaves the requests section and joins the friends list', function () {
    $viewer = User::factory()->create();
    $asker = User::factory()->create(['name' => 'Ada Lovelace']);

    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $asker->id,
        'addressee_id' => $viewer->id,
    ]);

    $this->actingAs($viewer)->patch(route('friendships.update', $friendship));

    $this->actingAs($viewer)->get(route('friends.requests'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 0));

    $this->actingAs($viewer)->get(route('friends.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('people', 1)
            ->where('people.0.id', $asker->id)
            ->where('people.0.relationship_state', 'friends')
        );
});

test('the friends list matches on either side of the relationship', function () {
    $viewer = User::factory()->create();
    $acceptedMine = User::factory()->create(['name' => 'A Accepted Mine']);
    $acceptedTheirs = User::factory()->create(['name' => 'B Accepted Theirs']);
    $stillPending = User::factory()->create(['name' => 'C Pending']);

    Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $acceptedMine->id,
    ]);

    Friendship::factory()->accepted()->create([
        'requester_id' => $acceptedTheirs->id,
        'addressee_id' => $viewer->id,
    ]);

    Friendship::factory()->pending()->create([
        'requester_id' => $stillPending->id,
        'addressee_id' => $viewer->id,
    ]);

    // Somebody else's friendship must not leak into this person's list.
    Friendship::factory()->accepted()->create();

    $this->actingAs($viewer)->get(route('friends.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('variant', 'friends')
            ->has('people', 2)
            ->where('people.0.name', 'A Accepted Mine')
            ->where('people.1.name', 'B Accepted Theirs')
            ->where('people.0.relationship_state', 'friends')
        );
});

test('a request the viewer sent stays visibly pending in find people', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($viewer)->post(route('friendships.store'), ['addressee_id' => $other->id]);

    $this->actingAs($viewer)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('people.data.0.relationship_state', 'request_sent')
            ->where('people.data.0.friendship_id', Friendship::sole()->id)
        );
});
