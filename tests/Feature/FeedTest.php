<?php

use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

test('every page is given the application name the wordmark is derived from', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('name', config('app.name')));
});

test('the feed of someone with nothing written and nobody to read is empty rather than broken', function () {
    $newcomer = User::factory()->create();
    Post::factory(3)->create();
    $this->actingAs($newcomer);

    $this->get(route('feed'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts.data', 0));
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
            ->has('posts.data', 3)
            ->where('posts.data.0.id', $own->id)
            ->where('posts.data.1.id', $fromFriendWhoAsked->id)
            ->where('posts.data.2.id', $fromFriendWhoWasAsked->id)
        );
});

test('the feed returns ten posts per page and the eleventh on the second page', function () {
    $viewer = User::factory()->create();

    // Newest first, so the post written eleven minutes ago is the eleventh
    // item and therefore the only one on page two.
    $posts = collect(range(1, 11))->map(fn (int $minutes): Post => Post::factory()
        ->for($viewer, 'author')
        ->create(['created_at' => now()->subMinutes($minutes)]));

    $this->actingAs($viewer)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 10)
            ->where('posts.data.0.id', $posts->first()->id)
            ->where('posts.meta.current_page', 1)
            ->where('posts.meta.last_page', 2)
            ->where('posts.meta.total', 11)
        );

    $this->actingAs($viewer)
        ->get(route('feed', ['page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.id', $posts->last()->id)
            ->where('posts.meta.current_page', 2)
        );
});

test('posts written in the same second still read newest first', function () {
    $viewer = User::factory()->create();

    // The seeder and the factories both produce posts sharing a second. With
    // nothing to break the tie SQLite hands them back in insertion order —
    // oldest first, the reverse of what the feed promises.
    $written = now()->subMinute();
    $first = Post::factory()->for($viewer, 'author')->create(['created_at' => $written]);
    $second = Post::factory()->for($viewer, 'author')->create(['created_at' => $written]);
    $third = Post::factory()->for($viewer, 'author')->create(['created_at' => $written]);

    $this->actingAs($viewer)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('posts.data.0.id', $third->id)
            ->where('posts.data.1.id', $second->id)
            ->where('posts.data.2.id', $first->id)
        );
});

test('posts written in the same second do not repeat or vanish across the page boundary', function () {
    $viewer = User::factory()->create();

    // The seeder and the factories both produce posts sharing a second, and
    // offset pagination over a sort SQLite may break either way could hand the
    // same post back on both pages while dropping another entirely.
    $written = now()->subMinute();
    $posts = collect(range(1, 12))->map(fn (): Post => Post::factory()
        ->for($viewer, 'author')
        ->create(['created_at' => $written]));

    $ids = collect();

    foreach ([1, 2] as $page) {
        $this->actingAs($viewer)
            ->get(route('feed', ['page' => $page]))
            ->assertInertia(function (AssertableInertia $inertia) use ($ids): void {
                $ids->push(...collect($inertia->toArray()['props']['posts']['data'])->pluck('id'));
            });
    }

    expect($ids)->toHaveCount(12)
        ->and($ids->unique())->toHaveCount(12)
        ->and($ids->sort()->values()->all())->toEqual($posts->pluck('id')->sort()->values()->all());
});

test('paginating the feed still applies the visibility scope', function () {
    $viewer = User::factory()->create();
    $stranger = User::factory()->create();

    Post::factory()->count(4)->for($viewer, 'author')->create();
    Post::factory()->count(20)->for($stranger, 'author')->create();

    // A stranger's twenty posts must not fill the page, nor add a second one.
    $this->actingAs($viewer)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 4)
            ->where('posts.meta.total', 4)
            ->where('posts.meta.last_page', 1)
        );
});

test('the paginated feed resolves its counts and author without a query per post', function () {
    // What matters is not the absolute number of queries but that it does not
    // grow with the number of rows on the page, so the same page is loaded at
    // one post and at a full ten and the two counts are compared.
    $countQueriesForFeed = function (int $posts): int {
        $viewer = User::factory()->create();
        Post::factory()->count($posts)->for($viewer, 'author')->create();

        $queries = 0;
        $listener = function () use (&$queries): void {
            $queries++;
        };

        DB::listen($listener);
        $this->actingAs($viewer)->get(route('feed'))->assertOk();

        return $queries;
    };

    expect($countQueriesForFeed(10))->toBe($countQueriesForFeed(1));
});
