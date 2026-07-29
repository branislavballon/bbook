<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The shape every screen reads a post in — the feed, the post detail page and
 * the post list on a profile.
 *
 * The counts and the viewer's own like are resolved by the query that loaded
 * the post, per `Post::scopeWithLikeState`, so nothing here asks a question
 * once per card.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_diff' => $this->created_at->diffForHumans(),
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'likes_count' => $this->likes_count,
            'liked' => (bool) $this->liked,
            'comments_count' => $this->comments_count,
            'can' => [
                'update' => $viewer->can('update', $this->resource),
                'delete' => $viewer->can('delete', $this->resource),
            ],
        ];
    }
}
