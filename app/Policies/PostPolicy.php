<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the person can read the post.
     *
     * The rule itself lives in Post::scopeVisibleTo, per ADR-0001; this asks
     * the scope about one post rather than restating it, so a change to
     * visibility never has to be made twice.
     */
    public function view(User $user, Post $post): bool
    {
        return Post::query()
            ->whereKey($post->getKey())
            ->visibleTo($user)
            ->exists();
    }

    /**
     * Determine whether the person can edit the post.
     *
     * Only its author can, which is what makes opening the edit page for
     * someone else's post a refusal rather than a form that fails on save.
     */
    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the person can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }
}
