<?php

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

/**
 * The graph is asserted the way a reviewer meets it — by signing in and reading
 * the screens — rather than by asking the visibility scope what it thinks. What
 * is checked against the database directly is the shape of the seed itself:
 * rows that exist, and who is on which side of them.
 */
beforeEach(function () {
    $this->seed(DemoSeeder::class);

    $this->demo = User::query()->where('email', DemoSeeder::DEMO_EMAIL)->sole();
    $this->newcomer = User::query()->where('email', DemoSeeder::NEWCOMER_EMAIL)->sole();
});

/**
 * One prop of one page, read as the given person sees it.
 */
function propOf(User $viewer, string $url, string $prop): mixed
{
    return test()->actingAs($viewer)->get($url)->inertiaProps($prop);
}

/**
 * Every post the feed shows this person, across all of its pages.
 *
 * @return Collection<int, array<string, mixed>>
 */
function wholeFeed(User $viewer): Collection
{
    $posts = new Collection;
    $page = 1;

    do {
        $paginator = propOf($viewer, route('feed', ['page' => $page]), 'posts');
        $posts = $posts->concat($paginator['data']);
        $page++;
    } while ($page <= $paginator['meta']['last_page']);

    return $posts;
}

test('the demo account exists and its documented password logs in', function () {
    $response = $this->post(route('login.store'), [
        'email' => DemoSeeder::DEMO_EMAIL,
        'password' => DemoSeeder::PASSWORD,
    ]);

    $this->assertAuthenticatedAs($this->demo);
    $response->assertRedirect(route('feed', absolute: false));
});

test('the newcomer account exists and its documented password logs in', function () {
    $this->post(route('login.store'), [
        'email' => DemoSeeder::NEWCOMER_EMAIL,
        'password' => DemoSeeder::PASSWORD,
    ]);

    $this->assertAuthenticatedAs($this->newcomer);
});

test('the demo friends list holds several people, all of whom have written', function () {
    $friends = propOf($this->demo, route('friends.index'), 'people');

    expect($friends)->toHaveCount(count(DemoSeeder::FRIEND_NAMES))
        ->and(count(DemoSeeder::FRIEND_NAMES))->toBeGreaterThanOrEqual(3);

    foreach ($friends as $friend) {
        // A friend's profile shows their posts, so an empty list here would mean
        // a friend who has written nothing.
        expect(propOf($this->demo, route('users.show', $friend['id']), 'posts'))
            ->not->toBeEmpty();
    }
});

test('friendship is seeded from both sides, so the friends list cannot be one-directional', function () {
    $accepted = Friendship::query()
        ->where('status', FriendshipStatus::Accepted)
        ->where(fn ($query) => $query
            ->where('requester_id', $this->demo->id)
            ->orWhere('addressee_id', $this->demo->id))
        ->get();

    expect($accepted->where('requester_id', $this->demo->id))->not->toBeEmpty()
        ->and($accepted->where('addressee_id', $this->demo->id))->not->toBeEmpty();
});

test('the demo requests section holds incoming requests waiting for an answer', function () {
    $requests = propOf($this->demo, route('friends.requests'), 'people');

    expect($requests)->toHaveCount(count(DemoSeeder::INCOMING_NAMES))
        ->and($requests)->not->toBeEmpty();

    foreach ($requests as $request) {
        expect($request['relationship_state'])->toBe('request_received');
    }
});

test('the demo account has an outgoing friend request still pending', function () {
    $pending = new Collection(propOf($this->demo, route('friends.find', ['page' => 2]), 'people')['data']);

    $sent = $pending->where('relationship_state', 'request_sent');

    expect($sent)->toHaveCount(count(DemoSeeder::OUTGOING_NAMES))
        ->and($sent)->not->toBeEmpty();

    // The same state, on the profile of the person who was asked: sent, and no
    // posts, because a pending request is not yet friendship.
    $this->get(route('users.show', $sent->first()['id']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('person.relationship_state', 'request_sent')
            ->where('posts', null));
});

test('strangers have written posts the demo feed does not show', function () {
    $feed = wholeFeed($this->demo);

    expect($feed)->not->toBeEmpty()
        ->and(Post::query()->count())->toBeGreaterThan($feed->count());
});

test('a stranger post the feed withheld is refused when asked for directly', function () {
    $shown = wholeFeed($this->demo)->pluck('id');

    $withheld = Post::query()->whereKeyNot($shown)->first();

    expect($withheld)->not->toBeNull();

    $this->actingAs($this->demo)->get(route('posts.show', $withheld))->assertForbidden();
});

test('the demo feed runs to a second page, so pagination is visible', function () {
    $posts = propOf($this->demo, route('feed'), 'posts');

    expect($posts['data'])->toHaveCount(10)
        ->and($posts['meta']['last_page'])->toBeGreaterThan(1);
});

test('find people runs to a second page, so pagination is visible there too', function () {
    $people = propOf($this->demo, route('friends.find'), 'people');

    expect($people['data'])->toHaveCount(10)
        ->and($people['meta']['last_page'])->toBeGreaterThan(1);
});

test('likes and comments are spread across the demo feed rather than absent or uniform', function () {
    $feed = wholeFeed($this->demo);

    expect($feed->sum('likes_count'))->toBeGreaterThan(0)
        ->and($feed->sum('comments_count'))->toBeGreaterThan(0)
        // Varied, not one post carrying everything: the counts have to differ
        // from each other for the feed to demonstrate anything.
        ->and($feed->pluck('likes_count')->unique()->count())->toBeGreaterThan(1)
        ->and($feed->pluck('comments_count')->unique()->count())->toBeGreaterThan(1);
});

test('the demo account has liked some of what it can see and not the rest', function () {
    $feed = wholeFeed($this->demo);

    expect($feed->where('liked', true))->not->toBeEmpty()
        ->and($feed->where('liked', false))->not->toBeEmpty();
});

test('every like and every comment was written by a friend of the author', function () {
    $friendsOf = Friendship::query()
        ->where('status', FriendshipStatus::Accepted)
        ->get()
        ->flatMap(fn (Friendship $friendship): array => [
            [$friendship->requester_id, $friendship->addressee_id],
            [$friendship->addressee_id, $friendship->requester_id],
        ]);

    $reactions = Post::query()->with(['likes', 'comments'])->get()
        ->flatMap(fn (Post $post): Collection => $post->likes
            ->pluck('user_id')
            ->concat($post->comments->pluck('user_id'))
            ->map(fn (int $reader): array => [$post->user_id, $reader]));

    expect($reactions)->not->toBeEmpty()
        ->and($reactions->reject(fn (array $pair): bool => $friendsOf->contains($pair))->all())
        ->toBe([]);
});

test('the newcomer has nothing at all', function () {
    expect($this->newcomer->posts()->exists())->toBeFalse()
        ->and(Friendship::query()
            ->where('requester_id', $this->newcomer->id)
            ->orWhere('addressee_id', $this->newcomer->id)
            ->exists())->toBeFalse();
});

test('every empty state the newcomer meets is reachable', function () {
    $this->actingAs($this->newcomer);

    $this->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts.data', 0));

    $this->get(route('friends.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 0));

    $this->get(route('friends.requests'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('people', 0));

    // Their own profile: an empty post list rather than a withheld one.
    $this->get(route('users.show', $this->newcomer))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts', 0));

    // A stranger's profile: withheld, which is the other thing emptiness can
    // mean, and the reason the two are told apart.
    $this->get(route('users.show', $this->demo))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('posts', null));
});

test('a post with no comments is reachable from the demo feed', function () {
    $bare = wholeFeed($this->demo)->firstWhere('comments_count', 0);

    expect($bare)->not->toBeNull();

    $this->actingAs($this->demo)
        ->get(route('posts.show', $bare['id']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('comments', 0));
});

test('seeding a database that is already seeded changes nothing', function () {
    $before = [User::query()->count(), Post::query()->count(), Friendship::query()->count()];

    $this->seed(DemoSeeder::class);

    expect([User::query()->count(), Post::query()->count(), Friendship::query()->count()])
        ->toBe($before);
});

test('the default database seeder builds the demo graph', function () {
    $this->seed(DatabaseSeeder::class);

    expect($this->demo->exists)->toBeTrue()
        ->and($this->newcomer->exists)->toBeTrue();
});
