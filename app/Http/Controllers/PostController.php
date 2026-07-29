<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Show the feed: the posts visible to the current person, newest first.
     */
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->with('author')
            ->withLikeState($request->user())
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return Inertia::render('feed', [
            'posts' => $posts->map(fn (Post $post): array => $this->postPayload($post, $request->user())),
        ]);
    }

    /**
     * Store a new post written by the current person.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $request->user()->posts()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post published.')]);

        return to_route('feed');
    }

    /**
     * Show a single post in full.
     */
    public function show(Request $request, Post $post): Response
    {
        $post->load('author')->loadLikeState($request->user());

        return Inertia::render('posts/show', [
            'post' => $this->postPayload($post, $request->user()),
        ]);
    }

    /**
     * Show the form for editing a post, pre-filled with its current text.
     */
    public function edit(Post $post): Response
    {
        return Inertia::render('posts/edit', [
            'post' => [
                'id' => $post->id,
                'body' => $post->body,
            ],
        ]);
    }

    /**
     * Save an edited post.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post updated.')]);

        return to_route('posts.show', $post);
    }

    /**
     * Delete a post, and with it — by database cascade — everything attached.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post deleted.')]);

        return to_route('feed');
    }

    /**
     * The shape every screen reads a post in.
     *
     * @return array<string, mixed>
     */
    private function postPayload(Post $post, User $viewer): array
    {
        // TODO: This should be resource in the future.
        return [
            'id' => $post->id,
            'body' => $post->body,
            'created_at' => $post->created_at->toIso8601String(),
            'created_at_diff' => $post->created_at->diffForHumans(),
            'author' => [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ],
            'likes_count' => $post->likes_count,
            // Resolved in the query that loaded the post, not asked per card.
            'liked' => (bool) $post->liked,
            // Comments do not exist yet; this becomes a withCount() aggregate
            // when that relation lands.
            'comments_count' => 0,
            'can' => [
                'update' => $viewer->can('update', $post),
                'delete' => $viewer->can('delete', $post),
            ],
        ];
    }
}
