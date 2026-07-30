<?php

use App\Models\Friendship;
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
            ->has('posts.data', 1)
            ->where('posts.data.0.body', "First line\nSecond line")
            ->where('posts.data.0.author.name', $user->name)
            ->where('posts.data.0.likes_count', 0)
            ->where('posts.data.0.comments_count', 0)
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
            ->has('posts.data', 2)
            ->where('posts.data.0.id', $newer->id)
            ->where('posts.data.1.id', $older->id)
        );
});

test('deleting a person deletes their posts', function () {
    $user = User::factory()->create();
    Post::factory()->for($user, 'author')->create();

    $user->delete();

    expect(Post::count())->toBe(0);
});

test('a person can open the detail page of their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create(['body' => 'The whole story.']);

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->where('post.id', $post->id)
            ->where('post.body', 'The whole story.')
            ->where('post.author.name', $user->name)
            ->where('post.likes_count', 0)
            ->where('post.comments_count', 0)
            ->where('post.can.update', true)
            ->where('post.can.delete', true)
        );
});

test('the edit page is pre-filled with the posts current text', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create(['body' => 'Needs a fix.']);

    $this->actingAs($user)
        ->get(route('posts.edit', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/edit')
            ->where('post.id', $post->id)
            ->where('post.body', 'Needs a fix.')
        );
});

test('a person can edit their own post and lands back on its detail page', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create(['body' => 'Before.']);

    $response = $this->actingAs($user)->patch(route('posts.update', $post), [
        'body' => 'After.',
    ]);

    $response->assertRedirect(route('posts.show', $post));
    $response->assertSessionHas('inertia.flash_data.toast.message', 'Post updated.');
    expect($post->refresh()->body)->toBe('After.');
});

test('an edited post is validated with the same rules as a new one', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create(['body' => 'Before.']);

    $this->actingAs($user)
        ->patch(route('posts.update', $post), ['body' => '   '])
        ->assertSessionHasErrors('body');

    $this->actingAs($user)
        ->patch(route('posts.update', $post), ['body' => str_repeat('a', 1001)])
        ->assertSessionHasErrors('body');

    expect($post->refresh()->body)->toBe('Before.');
});

test('a person can delete their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('feed'));
    $response->assertSessionHas('inertia.flash_data.toast.message', 'Post deleted.');
    expect(Post::count())->toBe(0);
});

test('a person cannot open the edit page for a post someone else wrote', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($stranger)
        ->get(route('posts.edit', $post))
        ->assertForbidden();
});

test('a person cannot update a post authored by someone else', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create(['body' => 'Mine.']);

    $this->actingAs($stranger)
        ->patch(route('posts.update', $post), ['body' => 'Hijacked.'])
        ->assertForbidden();

    expect($post->refresh()->body)->toBe('Mine.');
});

test('a person cannot delete a post authored by someone else', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($stranger)
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    expect(Post::count())->toBe(1);
});

test('a friends post offers no edit or delete affordance', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $author->id,
        'addressee_id' => $viewer->id,
    ]);
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('post.can.update', false)
            ->where('post.can.delete', false)
        );
});

test('a person can open the detail page of a friends post', function () {
    $friend = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->accepted()->create([
        'requester_id' => $friend->id,
        'addressee_id' => $viewer->id,
    ]);
    $post = Post::factory()->for($friend, 'author')->create(['body' => 'Told to friends.']);

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->where('post.id', $post->id)
            ->where('post.body', 'Told to friends.')
            ->where('post.author.name', $friend->name)
        );
});

test('a person is refused the detail page of a strangers post', function () {
    $stranger = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->for($stranger, 'author')->create();

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertForbidden();
});

test('a pending request does not open a persons posts', function () {
    $requester = User::factory()->create();
    $viewer = User::factory()->create();
    Friendship::factory()->pending()->create([
        'requester_id' => $requester->id,
        'addressee_id' => $viewer->id,
    ]);
    $post = Post::factory()->for($requester, 'author')->create();

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertForbidden();
});

test('guests cannot reach a post detail page', function () {
    $post = Post::factory()->create();

    $this->get(route('posts.show', $post))->assertRedirect(route('login'));
});

test('someone elses post is refused before its text is even validated', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create(['body' => 'Mine.']);

    $this->actingAs($stranger)
        ->patch(route('posts.update', $post), ['body' => ''])
        ->assertForbidden();

    expect($post->refresh()->body)->toBe('Mine.');
});

test('guests cannot edit or delete a post', function () {
    $post = Post::factory()->create(['body' => 'Untouched.']);

    $this->get(route('posts.edit', $post))->assertRedirect(route('login'));
    $this->patch(route('posts.update', $post), ['body' => 'Changed.'])
        ->assertRedirect(route('login'));
    $this->delete(route('posts.destroy', $post))->assertRedirect(route('login'));

    expect($post->refresh()->body)->toBe('Untouched.')
        ->and(Post::count())->toBe(1);
});
