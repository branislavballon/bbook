import { Head } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';
import CommentController from '@/actions/App/Http/Controllers/CommentController';
import { CommentItem } from '@/components/comment-item';
import { EmptyState } from '@/components/empty-state';
import { PostCard } from '@/components/post-card';
import { PostForm } from '@/components/post-form';
import { Card, CardContent } from '@/components/ui/card';
import { feed } from '@/routes';
import { show } from '@/routes/posts';
import type { Comment, Post } from '@/types/posts';

type Props = {
    post: Post;
    comments: Comment[];
};

export default function ShowPost({ post, comments }: Props) {
    return (
        <>
            <Head title={`Post by ${post.author.name}`} />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <h1 className="sr-only">Post by {post.author.name}</h1>

                <PostCard post={post} linked={false} reloadProp="post" />

                <section
                    className="flex flex-col gap-4"
                    aria-labelledby="comments-heading"
                >
                    {/*
                     * The count is the server's `withCount` aggregate, not
                     * `comments.length`: mirroring it client-side is the
                     * synchronisation risk the like button already avoids.
                     */}
                    <h2 id="comments-heading" className="text-sm font-medium">
                        Comments
                        {post.comments_count > 0 && ` (${post.comments_count})`}
                    </h2>

                    {comments.length === 0 ? (
                        <EmptyState
                            icon={MessageCircle}
                            title="No comments yet"
                            description="Nobody has responded to this post. Be the first to say something."
                        />
                    ) : (
                        <div className="flex flex-col gap-4">
                            {comments.map((comment) => (
                                <CommentItem
                                    key={comment.id}
                                    comment={comment}
                                />
                            ))}
                        </div>
                    )}

                    {/*
                     * The composer sits below the thread because the thread
                     * reads oldest-first: a new comment appears where the
                     * person was already looking.
                     */}
                    <Card>
                        <CardContent>
                            <PostForm
                                {...CommentController.store.form(post.id)}
                                options={{
                                    preserveScroll: true,
                                    only: ['post', 'comments'],
                                }}
                                label="Write a comment"
                                placeholder="Write a comment…"
                                submitLabel="Comment"
                            />
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

/**
 * A callback rather than a static object, because the trail needs the post's
 * id. Dynamic props via setLayoutProps would outlive this page and follow the
 * person back to the feed.
 */
ShowPost.layout = ({ post }: Props) => ({
    breadcrumbs: [
        { title: 'Feed', href: feed() },
        { title: 'Post', href: show(post.id) },
    ],
});
