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

test('the friends and requests sections are empty until they are built', function () {
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
            ->has('people', 2)
            ->where('people.0.id', $other->id)
            ->where('people.0.name', 'Ada Lovelace')
            ->where('people.0.relationship_state', 'none')
        );
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

    Friendship::factory()->pending()->create([
        'requester_id' => $asker->id,
        'addressee_id' => $user->id,
    ]);

    Friendship::factory()->accepted()->create([
        'requester_id' => $friend->id,
        'addressee_id' => $user->id,
    ]);

    $this->actingAs($user)->get(route('friends.find'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('people', 3)
            ->where('people.0.relationship_state', 'request_sent')
            ->where('people.1.relationship_state', 'request_received')
            ->where('people.2.relationship_state', 'friends')
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
            ->where('people.0.relationship_state', 'request_sent')
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
