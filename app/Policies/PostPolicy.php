<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
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
