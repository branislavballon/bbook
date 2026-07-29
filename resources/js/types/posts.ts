export type PostAuthor = {
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
    author: PostAuthor;
    likes_count: number;
    /** Whether the current viewer has liked this post. */
    liked: boolean;
    comments_count: number;
    can: PostAbilities;
};
