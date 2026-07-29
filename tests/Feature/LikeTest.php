<?php

use App\Models\Friendship;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Inertia\Testing\AssertableInertia;

test('a person can like their own post and the count comes back from the server', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $response = $this->actingAs($user)->post(route('posts.likes.store', $post));

    $response->assertRedirect();
    expect(Like::count())->toBe(1);

    $this->actingAs($user)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->has('posts', 1)
            ->where('posts.0.likes_count', 1)
            ->where('posts.0.liked', true)
        );
});

test('liking twice does not error and does not produce two likes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $this->actingAs($user)->post(route('posts.likes.store', $post))->assertRedirect();
    $this->actingAs($user)->post(route('posts.likes.store', $post))->assertRedirect();

    expect(Like::count())->toBe(1);
});

test('a person can withdraw their like and the count goes back down', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();

    $response = $this->actingAs($user)->delete(route('posts.likes.destroy', $post));

    $response->assertRedirect();
    expect(Like::count())->toBe(0);

    $this->actingAs($user)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->has('posts', 1)
            ->where('posts.0.likes_count', 0)
            ->where('posts.0.liked', false)
        );
});

test('unliking twice does not error', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();

    $this->actingAs($user)->delete(route('posts.likes.destroy', $post))->assertRedirect();
    $this->actingAs($user)->delete(route('posts.likes.destroy', $post))->assertRedirect();

    expect(Like::count())->toBe(0);
});

test('unliking withdraws only the persons own like', function () {
    $author = User::factory()->create();
    $friend = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $author->id,
        'addressee_id' => $friend->id,
    ]);
    $post = Post::factory()->for($author, 'author')->create();
    $theirs = Like::factory()->for($author)->for($post)->create();
    Like::factory()->for($friend)->for($post)->create();

    $this->actingAs($friend)->delete(route('posts.likes.destroy', $post));

    expect(Like::pluck('id')->all())->toBe([$theirs->id]);
});

test('a person can like a friends post', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $viewer->id,
        'addressee_id' => $author->id,
    ]);
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($viewer)
        ->post(route('posts.likes.store', $post))
        ->assertRedirect();

    expect(Like::count())->toBe(1);
});

test('a person cannot like a post that is not visible to them', function () {
    $stranger = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->for($stranger, 'author')->create();

    $this->actingAs($viewer)
        ->post(route('posts.likes.store', $post))
        ->assertForbidden();

    expect(Like::count())->toBe(0);
});

test('a person cannot unlike a post that is not visible to them', function () {
    $stranger = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->for($stranger, 'author')->create();
    Like::factory()->for($viewer)->for($post)->create();

    $this->actingAs($viewer)
        ->delete(route('posts.likes.destroy', $post))
        ->assertForbidden();

    expect(Like::count())->toBe(1);
});

test('guests cannot like or unlike a post', function () {
    $post = Post::factory()->create();

    $this->post(route('posts.likes.store', $post))->assertRedirect(route('login'));
    $this->delete(route('posts.likes.destroy', $post))->assertRedirect(route('login'));

    expect(Like::count())->toBe(0);
});

test('a second like from the same person is impossible at the storage layer', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();

    expect(fn () => Like::factory()->for($user)->for($post)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

test('someone elses like on the same post is unaffected by the unique constraint', function () {
    $author = User::factory()->create();
    $friend = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    Like::factory()->for($author)->for($post)->create();
    Like::factory()->for($friend)->for($post)->create();

    expect(Like::count())->toBe(2);
});

test('the like state shown to one person is not the like state of another', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $author->id,
        'addressee_id' => $viewer->id,
    ]);
    $post = Post::factory()->for($author, 'author')->create();
    Like::factory()->for($author)->for($post)->create();

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('post.likes_count', 1)
            ->where('post.liked', false)
        );
});

test('the post detail page reports the viewers own like', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->where('post.likes_count', 1)
            ->where('post.liked', true)
        );
});

test('deleting a post disposes of its likes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();
    Like::factory()->for($user)->for($post)->create();

    $this->actingAs($user)->delete(route('posts.destroy', $post))->assertRedirect(route('feed'));

    expect(Like::count())->toBe(0);
});

test('deleting a person disposes of the likes they gave', function () {
    $author = User::factory()->create();
    $liker = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();
    Like::factory()->for($liker)->for($post)->create();

    $liker->delete();

    expect(Like::count())->toBe(0)
        ->and(Post::count())->toBe(1);
});
