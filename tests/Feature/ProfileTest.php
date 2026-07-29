<?php

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page from a profile', function () {
    $person = User::factory()->create();

    $this->get(route('users.show', $person))->assertRedirect(route('login'));
});

test('a profile renders the person it is bound to', function () {
    $viewer = User::factory()->create();
    $person = User::factory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($viewer)->get(route('users.show', $person))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('users/show')
            ->where('person.id', $person->id)
            ->where('person.name', 'Ada Lovelace')
            ->where('person.is_self', false)
            ->has('posts')
        );
});

test('the profile route does not collide with the settings screens', function () {
    $person = User::factory()->create();

    expect(route('users.show', $person))->not->toContain('settings')
        ->and(route('profile.edit'))->toContain('settings');
});

test('a stranger\'s posts are withheld rather than shown as empty', function () {
    $viewer = User::factory()->create();
    $stranger = User::factory()->create();
    Post::factory()->for($stranger, 'author')->create();

    $this->actingAs($viewer)->get(route('users.show', $stranger))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('posts', null)
            ->where('person.relationship_state', 'none')
        );
});

test('a friend\'s posts are listed', function () {
    $viewer = User::factory()->create();
    $friend = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $friend->id,
        'addressee_id' => $viewer->id,
    ]);

    $post = Post::factory()->for($friend, 'author')->create(['body' => 'Visible to friends.']);
    // The viewer's own posts must not leak onto someone else's profile.
    Post::factory()->for($viewer, 'author')->create();

    $this->actingAs($viewer)->get(route('users.show', $friend))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('person.relationship_state', 'friends')
            ->has('posts', 1)
            ->where('posts.0.id', $post->id)
            ->where('posts.0.body', 'Visible to friends.')
            ->has('posts.0.likes_count')
            ->has('posts.0.comments_count')
            ->has('posts.0.liked')
            ->has('posts.0.can')
        );
});

test('a friend with nothing written shows an empty list, not withheld posts', function () {
    $viewer = User::factory()->create();
    $friend = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $friend->id,
    ]);

    $this->actingAs($viewer)->get(route('users.show', $friend))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts', 0));
});

test('a pending request does not make posts visible', function () {
    $viewer = User::factory()->create();
    $person = User::factory()->create();
    Friendship::factory()->pending()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $person->id,
    ]);
    Post::factory()->for($person, 'author')->create();

    $this->actingAs($viewer)->get(route('users.show', $person))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('posts', null));
});

test('the viewer\'s own profile shows their posts and offers no relationship action', function () {
    $viewer = User::factory()->create();
    Post::factory()->for($viewer, 'author')->create();

    $this->actingAs($viewer)->get(route('users.show', $viewer))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('person.is_self', true)
            ->where('person.relationship_state', 'none')
            ->where('person.friendship_id', null)
            ->has('posts', 1)
        );
});

test('the relationship state names where the viewer stands with the person', function (string $state, Closure $arrange) {
    $viewer = User::factory()->create();
    $person = User::factory()->create();

    $friendship = $arrange($viewer, $person);

    $this->actingAs($viewer)->get(route('users.show', $person))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('person.relationship_state', $state)
            ->where('person.friendship_id', $friendship?->id)
        );
})->with([
    'stranger' => fn () => ['none', fn (): ?Friendship => null],
    'request sent' => fn () => ['request_sent', fn (User $viewer, User $person): Friendship => Friendship::factory()->pending()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $person->id,
    ])],
    'request received' => fn () => ['request_received', fn (User $viewer, User $person): Friendship => Friendship::factory()->pending()->create([
        'requester_id' => $person->id,
        'addressee_id' => $viewer->id,
    ])],
    'already friends' => fn () => ['friends', fn (User $viewer, User $person): Friendship => Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $person->id,
    ])],
]);

test('a request received on a profile is answered by the operations the requests section uses', function () {
    $viewer = User::factory()->create();
    $person = User::factory()->create();
    $friendship = Friendship::factory()->pending()->create([
        'requester_id' => $person->id,
        'addressee_id' => $viewer->id,
    ]);

    $this->actingAs($viewer)
        ->from(route('users.show', $person))
        ->patch(route('friendships.update', $friendship))
        ->assertRedirect(route('users.show', $person));

    expect($friendship->refresh()->status)->toBe(FriendshipStatus::Accepted);
});
