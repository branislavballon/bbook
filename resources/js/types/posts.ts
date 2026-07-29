/** Whoever wrote the thing being rendered — a post or a comment. */
export type Author = {
    id: number;
    name: string;
};

export type PostAbilities = {
    update: boolean;
    delete: boolean;
};

export type Post = {
    id: number;
    body: string;
    created_at: string;
    created_at_diff: string;
    author: Author;
    likes_count: number;
    /** Whether the current viewer has liked this post. */
    liked: boolean;
    comments_count: number;
    can: PostAbilities;
};

/**
 * A response written on a post. Comments are never edited or deleted in this
 * application, so there are no abilities to carry alongside one.
 */
export type Comment = {
    id: number;
    body: string;
    created_at: string;
    created_at_diff: string;
    author: Author;
};
