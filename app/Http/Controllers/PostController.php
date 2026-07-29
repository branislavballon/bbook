<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
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
            ->whereBelongsTo($request->user(), 'author')
            ->latest()
            ->get();

        return Inertia::render('feed', [
            'posts' => $posts->map(fn (Post $post): array => [
                'id' => $post->id,
                'body' => $post->body,
                'created_at' => $post->created_at->toIso8601String(),
                'created_at_diff' => $post->created_at->diffForHumans(),
                'author' => [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                ],
                // Likes and comments do not exist yet; these counts become
                // withCount() aggregates when those relations land.
                'likes_count' => 0,
                'comments_count' => 0,
            ]),
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
}
