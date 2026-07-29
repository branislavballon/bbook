<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Liking and unliking are two operations rather than one toggle: a toggle does
 * opposite things to identical requests, so a retry or a double-tap undoes the
 * act it was meant to repeat.
 */
class LikeController extends Controller
{
    /**
     * Like a post. Idempotent by construction — the person either already
     * likes it or now does, and the unique index makes a second row
     * impossible either way.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $post->likes()->firstOrCreate(['user_id' => $request->user()->id]);

        return back();
    }

    /**
     * Withdraw a like. Deleting nothing is not an error: repeating the request
     * leaves the same state it found.
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $post->likes()->whereBelongsTo($request->user())->delete();

        return back();
    }
}
