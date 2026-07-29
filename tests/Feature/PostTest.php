<?php

use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot create a post', function () {
    $response = $this->post(route('posts.store'), ['body' => 'Hello there.']);

    $response->assertRedirect(route('login'));
    expect(Post::count())->toBe(0);
});

test('an authenticated person can create a post and it appears in their feed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), [
        'body' => "First line\nSecond line",
    ]);

    $response->assertRedirect(route('feed'));
    $response->assertSessionHas('inertia.flash_data.toast.message', 'Post published.');

    $post = Post::sole();
    expect($post->user_id)->toBe($user->id)
        ->and($post->body)->toBe("First line\nSecond line");

    $this->actingAs($user)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->has('posts', 1)
            ->where('posts.0.body', "First line\nSecond line")
            ->where('posts.0.author.name', $user->name)
            ->where('posts.0.likes_count', 0)
            ->where('posts.0.comments_count', 0)
        );
});

test('post body is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), ['body' => '']);

    $response->assertSessionHasErrors('body');
    expect(Post::count())->toBe(0);
});

test('a whitespace only post is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), ['body' => "   \n  "]);

    $response->assertSessionHasErrors('body');
    expect(Post::count())->toBe(0);
});

test('a post longer than 1000 characters is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), [
        'body' => str_repeat('a', 1001),
    ]);

    $response->assertSessionHasErrors('body');
    expect(Post::count())->toBe(0);
});

test('the post body is trimmed before it is stored', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), ['body' => '  Padded.  ']);

    expect(Post::sole()->body)->toBe('Padded.');
});

test('the feed lists the persons own posts newest first', function () {
    $user = User::factory()->create();

    $older = Post::factory()->for($user, 'author')->create(['created_at' => now()->subDay()]);
    $newer = Post::factory()->for($user, 'author')->create(['created_at' => now()]);

    $this->actingAs($user)
        ->get(route('feed'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('feed')
            ->has('posts', 2)
            ->where('posts.0.id', $newer->id)
            ->where('posts.1.id', $older->id)
        );
});

test('deleting a person deletes their posts', function () {
    $user = User::factory()->create();
    Post::factory()->for($user, 'author')->create();

    $user->delete();

    expect(Post::count())->toBe(0);
});
