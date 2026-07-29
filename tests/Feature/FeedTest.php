<?php

use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('feed'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the feed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('feed'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('feed'));
});

test('the feed holds the persons own posts and their friends, and no strangers', function () {
    $viewer = User::factory()->create();
    $friendWhoAsked = User::factory()->create();
    $friendWhoWasAsked = User::factory()->create();
    $stranger = User::factory()->create();
    $pendingRequester = User::factory()->create();

    // Friendship is mutual whichever side sent the request, so both
    // directions must reach the feed.
    Friendship::factory()->accepted()->create([
        'requester_id' => $friendWhoAsked->id,
        'addressee_id' => $viewer->id,
    ]);
    Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $friendWhoWasAsked->id,
    ]);
    Friendship::factory()->pending()->create([
        'requester_id' => $pendingRequester->id,
        'addressee_id' => $viewer->id,
    ]);

    $own = Post::factory()->for($viewer, 'author')->create(['created_at' => now()->subMinute()]);
    $fromFriendWhoAsked = Post::factory()->for($friendWhoAsked, 'author')->create(['created_at' => now()->subMinutes(2)]);
    $fromFriendWhoWasAsked = Post::factory()->for($friendWhoWasAsked, 'author')->create(['created_at' => now()->subMinutes(3)]);
    Post::factory()->for($stranger, 'author')->create();
    Post::factory()->for($pendingRequester, 'author')->create();

    $this->actingAs($viewer)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->has('posts', 3)
            ->where('posts.0.id', $own->id)
            ->where('posts.1.id', $fromFriendWhoAsked->id)
            ->where('posts.2.id', $fromFriendWhoWasAsked->id)
        );
});
