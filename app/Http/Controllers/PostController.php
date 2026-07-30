<?php

namespace App\Http\Controllers;

use App\Http\Requests\BodyRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Show the feed: the posts visible to the current person, newest first,
     * a page at a time.
     *
     * Offset pagination, per ADR-0005: a post written between page views can
     * shift the boundary and repeat an item, which is accepted at this scale
     * over cursor pagination's need for a unique sort key, given the identical
     * timestamps the factories and the seeder produce.
     */
    public function index(Request $request): Response
    {
        $posts = Post::query()->readableBy($request->user())->paginate(self::PER_PAGE);

        return Inertia::render('feed', [
            'posts' => PostResource::collection($posts),
            'bodyMaxLength' => BodyRequest::MAX_LENGTH,
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
        $post->load('author')->loadCount('comments')->loadLikeState($request->user());

        // Oldest-first, so the thread reads as the conversation happened. The
        // id breaks the tie: two comments written in the same second — which
        // the seeder and the factories both produce — would otherwise come
        // back in whatever order SQLite felt like.
        $comments = $post->comments()
            ->with('author')
            ->oldest()
            ->oldest('id')
            ->get();

        return Inertia::render('posts/show', [
            'post' => PostResource::make($post),
            'comments' => $comments->map($this->commentPayload(...)),
            'bodyMaxLength' => BodyRequest::MAX_LENGTH,
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
            'bodyMaxLength' => BodyRequest::MAX_LENGTH,
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
     * The shape the detail page reads a comment in. Comments are never edited
     * or deleted, so there are no abilities to report alongside them.
     *
     * @return array<string, mixed>
     */
    private function commentPayload(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'created_at' => $comment->created_at->toIso8601String(),
            'created_at_diff' => $comment->created_at->diffForHumans(),
            'author' => [
                'id' => $comment->author->id,
                'name' => $comment->author->name,
            ],
        ];
    }
}
