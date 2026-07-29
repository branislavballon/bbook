<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

/**
 * Comments are written from the post detail page and nowhere else, so storing
 * one is the only action there is: they are never edited, deleted, or nested.
 */
class CommentController extends Controller
{
    /**
     * Add a comment to a post the person is allowed to read.
     *
     * The route authorizes against the parent post's `view` ability, so the
     * visibility rule holds for this write exactly as it does for the read.
     */
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $comment = new Comment($request->validated());
        $comment->author()->associate($request->user());

        $post->comments()->save($comment);

        return back();
    }
}
