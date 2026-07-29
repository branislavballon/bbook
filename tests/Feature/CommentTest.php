<?php

use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('a person can comment on their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $response = $this->actingAs($user)
        ->post(route('posts.comments.store', $post), ['body' => 'Well said, me.']);

    $response->assertRedirect();

    expect(Comment::count())->toBe(1);
    expect(Comment::first())
        ->body->toBe('Well said, me.')
        ->user_id->toBe($user->id)
        ->post_id->toBe($post->id);
});

test('a person can comment on a friends post', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $author->id,
    ]);
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($viewer)
        ->post(route('posts.comments.store', $post), ['body' => 'Good point.'])
        ->assertRedirect();

    expect(Comment::count())->toBe(1);
});

test('a person cannot comment on a post that is not visible to them', function () {
    $stranger = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->for($stranger, 'author')->create();

    $this->actingAs($viewer)
        ->post(route('posts.comments.store', $post), ['body' => 'Let me in.'])
        ->assertForbidden();

    expect(Comment::count())->toBe(0);
});

test('guests cannot comment on a post', function () {
    $post = Post::factory()->create();

    $this->post(route('posts.comments.store', $post), ['body' => 'Anonymous.'])
        ->assertRedirect(route('login'));

    expect(Comment::count())->toBe(0);
});

test('an empty comment is rejected', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.comments.store', $post), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect(Comment::count())->toBe(0);
});

test('a whitespace only comment is rejected', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.comments.store', $post), ['body' => "   \n\t  "])
        ->assertSessionHasErrors('body');

    expect(Comment::count())->toBe(0);
});

test('a comment longer than the cap is rejected', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.comments.store', $post), ['body' => str_repeat('a', 1001)])
        ->assertSessionHasErrors('body');

    expect(Comment::count())->toBe(0);
});

test('a comment is stored trimmed', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), ['body' => "  padded  \n"]);

    expect(Comment::first()->body)->toBe('padded');
});

test('the post detail page lists comments oldest first with their author', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $newer = Comment::factory()->for($post)->for($user, 'author')
        ->create(['body' => 'Second', 'created_at' => now()->subMinute()]);
    $older = Comment::factory()->for($post)->for($user, 'author')
        ->create(['body' => 'First', 'created_at' => now()->subHour()]);

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->has('comments', 2)
            ->where('comments.0.id', $older->id)
            ->where('comments.0.body', 'First')
            ->where('comments.0.author.name', $user->name)
            ->has('comments.0.created_at_diff')
            ->where('comments.1.id', $newer->id)
            ->where('post.comments_count', 2)
        );
});

test('comments written in the same second still read in the order they were written', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $written = now()->subMinute();
    $first = Comment::factory()->for($post)->for($user, 'author')
        ->create(['body' => 'First', 'created_at' => $written]);
    $second = Comment::factory()->for($post)->for($user, 'author')
        ->create(['body' => 'Second', 'created_at' => $written]);

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('comments.0.id', $first->id)
            ->where('comments.1.id', $second->id)
        );
});

test('the post detail page reports no comments when there are none', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->has('comments', 0)
            ->where('post.comments_count', 0)
        );
});

test('the feed carries the comment count of each post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Comment::factory()->count(3)->for($post)->create();

    $this->actingAs($user)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->where('posts.0.comments_count', 3)
        );
});

test('deleting a post removes its likes and comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();
    Comment::factory()->for($post)->for($user, 'author')->create();

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('feed'));

    expect(Like::count())->toBe(0)
        ->and(Comment::count())->toBe(0);
});

test('deleting a person disposes of the comments they wrote', function () {
    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();
    Comment::factory()->for($post)->for($commenter, 'author')->create();

    $commenter->delete();

    expect(Comment::count())->toBe(0)
        ->and(Post::count())->toBe(1);
});
