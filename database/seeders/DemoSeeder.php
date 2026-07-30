<?php

namespace Database\Seeders;

use App\Enums\FriendshipStatus;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The demo graph: a deliberately composed network, so that someone who clones
 * this repository and seeds it is inside a populated application with every
 * state of it visible without creating anything themselves.
 *
 * Sign in as `demo@example.com` / `password` for the populated experience, or
 * as `newcomer@example.com` / `password` for the empty one. Everybody in the
 * graph has the password `password`, which is the whole point of a demo seed.
 *
 * What the demo account can see: its own posts and the posts of four friends —
 * two friendships it asked for, two it was asked for — with likes and comments
 * spread unevenly across them. Waiting for it: two incoming friend requests,
 * and one outgoing request nobody has answered. Invisible to it, and the
 * reason the visibility rule can be confirmed by observation rather than taken
 * on trust: five strangers who write to each other.
 *
 * The structure here is written out; only the prose inside a post or comment
 * comes from a factory.
 */
class DemoSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@example.com';

    public const NEWCOMER_EMAIL = 'newcomer@example.com';

    public const PASSWORD = 'password';

    /**
     * The people the demo account is friends with. Every one of them writes, so
     * the feed has something in it.
     *
     * @var list<string>
     */
    public const FRIEND_NAMES = ['Marta Kovács', 'Julian Reyes', 'Priya Anand', 'Tomasz Nowak'];

    /**
     * The people who have asked the demo account to be friends and are waiting
     * for an answer.
     *
     * @var list<string>
     */
    public const INCOMING_NAMES = ['Elena Sokolova', 'Hiro Tanaka'];

    /**
     * The people the demo account has asked, who have not answered.
     *
     * @var list<string>
     */
    public const OUTGOING_NAMES = ['Sofia Marchetti'];

    /**
     * People with no relationship to the demo account at all. They write, and
     * none of it may reach the demo account.
     *
     * @var list<string>
     */
    public const STRANGER_NAMES = [
        'Noah Bergström',
        'Amara Okonkwo',
        'Lucas Ferreira',
        'Mei Chen',
        'Oliver Grant',
    ];

    /**
     * Enough that the demo account's feed runs past the first page: itself plus
     * four friends, three posts each, against ten to a page.
     */
    public const POSTS_PER_PERSON = 3;

    /**
     * Strangers write less, because nothing they write is on display — their
     * posts exist to be absent from the demo account's feed.
     */
    public const STRANGER_POSTS = 2;

    /**
     * How far apart consecutive posts are written, so "3 hours ago" and
     * "2 days ago" both appear and the relative timestamps mean something.
     */
    private const HOURS_BETWEEN_POSTS = 3;

    /**
     * The likes and the comments on a post are counted out by where the post
     * falls in the writing order, modulo these. Two different numbers, both
     * small, so the counts vary from post to post, land on zero often enough
     * for the empty states to be reachable, and never agree with each other for
     * long. Reproducible by construction: nothing here is random.
     */
    private const LIKES_CYCLE = 4;

    private const COMMENTS_CYCLE = 3;

    /**
     * Build the demo graph, unless it is already here.
     */
    public function run(): void
    {
        // Re-running the seeder over a graph that is already here would collide
        // on the demo account's address, so it stops instead: `migrate:fresh
        // --seed` is how the graph is rebuilt.
        if (User::query()->where('email', self::DEMO_EMAIL)->exists()) {
            return;
        }

        // All of it or none of it. The check above keys on the demo account, so
        // a half-written graph would be indistinguishable from a finished one
        // and every later seed would skip it.
        DB::transaction(function (): void {
            $demo = $this->person('Demo Person', self::DEMO_EMAIL);
            $this->person('New Person', self::NEWCOMER_EMAIL);

            $friends = $this->people(self::FRIEND_NAMES);
            $strangers = $this->people(self::STRANGER_NAMES);

            $this->connect($demo, $friends, $strangers);
            $this->publish($this->writingOrder((new Collection([$demo]))->concat($friends), $strangers));
        });
    }

    /**
     * Wire up the friendship graph: the demo account's friends, the requests
     * waiting on either side of it, and the strangers' own network.
     *
     * @param  Collection<int, User>  $friends
     * @param  Collection<int, User>  $strangers
     */
    private function connect(User $demo, Collection $friends, Collection $strangers): void
    {
        // Alternating sides, because the friends list has to read the same
        // whether the demo account asked or was asked.
        $friends->each(fn (User $friend, int $index): Friendship => $index % 2 === 0
            ? $this->friends($demo, $friend)
            : $this->friends($friend, $demo));

        // The friends know each other in pairs, so a post in the demo feed has
        // an audience wider than the demo account itself and its like and
        // comment counts can climb above one.
        $this->friends($friends[0], $friends[1]);
        $this->friends($friends[2], $friends[3]);

        $this->people(self::INCOMING_NAMES)
            ->each(fn (User $requester): Friendship => $this->pendingRequest($requester, $demo));

        $this->people(self::OUTGOING_NAMES)
            ->each(fn (User $addressee): Friendship => $this->pendingRequest($demo, $addressee));

        // A chain rather than a clique: enough for the strangers' posts to
        // collect likes and comments from each other without any of it ever
        // reaching the demo account.
        $strangers->sliding(2)
            ->each(fn (Collection $pair): Friendship => $this->friends($pair->first(), $pair->last()));
    }

    /**
     * The order the network wrote in: round by round, so consecutive posts have
     * different authors and the feed reads as several people talking rather
     * than as one person's output followed by another's.
     *
     * Everyone writes in the early rounds and only the demo account's circle
     * writes in the last one, which puts strangers' posts in the middle of the
     * feed's timespan — a leak in the visibility rule would show up between
     * visible posts rather than safely below them.
     *
     * @param  Collection<int, User>  $circle  The demo account and its friends.
     * @param  Collection<int, User>  $strangers
     * @return Collection<int, User> One entry per post to be written, oldest first.
     */
    private function writingOrder(Collection $circle, Collection $strangers): Collection
    {
        $order = new Collection;

        for ($round = 1; $round <= self::POSTS_PER_PERSON; $round++) {
            $order = $order->concat($round <= self::STRANGER_POSTS
                ? $circle->concat($strangers)
                : $circle);
        }

        return $order;
    }

    /**
     * Write the posts, oldest first, ending at the present moment so the most
     * recent post reads as hours old and the oldest as days.
     *
     * @param  Collection<int, User>  $authors  One entry per post, oldest first.
     */
    private function publish(Collection $authors): void
    {
        $audiences = $this->audiences();

        $writtenAt = Carbon::now()
            ->subHours($authors->count() * self::HOURS_BETWEEN_POSTS)
            ->startOfHour();

        foreach ($authors->values() as $ordinal => $author) {
            $post = Post::factory()
                ->for($author, 'author')
                ->create([
                    'created_at' => $writtenAt,
                    'updated_at' => $writtenAt,
                ]);

            $this->reactTo($post, $audiences[$author->id] ?? [], $ordinal);

            $writtenAt = $writtenAt->copy()->addHours(self::HOURS_BETWEEN_POSTS);
        }
    }

    /**
     * Who can read each person's posts, keyed by author: everyone they are in
     * an accepted friendship with, from either side of the row.
     *
     * Nobody likes or comments on their own post, so the author is not in
     * their own audience — the reactions on a post are all from other people,
     * which is what makes them worth showing.
     *
     * @return array<int, list<int>> Reader ids, keyed by author id.
     */
    private function audiences(): array
    {
        $audiences = [];

        foreach (Friendship::query()->where('status', FriendshipStatus::Accepted)->get() as $friendship) {
            $audiences[$friendship->requester_id][] = $friendship->addressee_id;
            $audiences[$friendship->addressee_id][] = $friendship->requester_id;
        }

        return $audiences;
    }

    /**
     * Give a post its likes and comments, from people who can actually read it.
     *
     * Commenters are drawn from the far end of the audience so that the people
     * who liked a post and the people who wrote on it are not the same list.
     *
     * @param  list<int>  $audience  The ids of everyone who can read this post.
     * @param  int  $ordinal  Where this post falls in the writing order, which
     *                        is what decides how many reactions it collects.
     */
    private function reactTo(Post $post, array $audience, int $ordinal): void
    {
        $reactedAt = $post->created_at->copy()->addMinutes(20);

        foreach (array_slice($audience, 0, $ordinal % self::LIKES_CYCLE) as $reader) {
            Like::factory()->create([
                'user_id' => $reader,
                'post_id' => $post->id,
                'created_at' => $reactedAt,
                'updated_at' => $reactedAt,
            ]);
        }

        foreach (array_slice(array_reverse($audience), 0, $ordinal % self::COMMENTS_CYCLE) as $reader) {
            Comment::factory()->create([
                'user_id' => $reader,
                'post_id' => $post->id,
                'created_at' => $reactedAt,
                'updated_at' => $reactedAt,
            ]);
        }
    }

    /**
     * Create one person with the documented password.
     */
    private function person(string $name, ?string $email = null): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email ?? $this->emailFor($name),
            'password' => self::PASSWORD,
        ]);
    }

    /**
     * Create a group of people, in the order they are named.
     *
     * @param  list<string>  $names
     * @return Collection<int, User>
     */
    private function people(array $names): Collection
    {
        return (new Collection($names))->map(fn (string $name): User => $this->person($name));
    }

    /**
     * A predictable address, so anyone reviewing the seed can sign in as
     * anybody in it and see the same graph from their side.
     */
    private function emailFor(string $name): string
    {
        return str($name)->ascii()->slug('.')->append('@example.com')->toString();
    }

    /**
     * Two people who are friends, recorded in the direction they asked in —
     * which the accepted friendship keeps even though nothing reads it.
     */
    private function friends(User $requester, User $addressee): Friendship
    {
        return Friendship::factory()->accepted()->create($this->sides($requester, $addressee));
    }

    /**
     * A friend request nobody has answered yet.
     */
    private function pendingRequest(User $requester, User $addressee): Friendship
    {
        return Friendship::factory()->pending()->create($this->sides($requester, $addressee));
    }

    /**
     * The two sides of a friendship, in the direction it was asked in. The
     * status comes from the factory state rather than from here, so the seeder
     * composes friendships out of the same pieces the tests do.
     *
     * @return array<string, int>
     */
    private function sides(User $requester, User $addressee): array
    {
        return [
            'requester_id' => $requester->id,
            'addressee_id' => $addressee->id,
        ];
    }
}
