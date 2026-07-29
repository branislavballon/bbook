import { Form, Link } from '@inertiajs/react';
import { MessageCircle, Pencil } from 'lucide-react';
import LikeController from '@/actions/App/Http/Controllers/LikeController';
import { DeletePostDialog } from '@/components/delete-post-dialog';
import { LikeButton } from '@/components/like-button';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { UserAvatar } from '@/components/user-avatar';
import { edit, show } from '@/routes/posts';
import type { Post } from '@/types/posts';

type Props = {
    post: Post;
    /**
     * The feed links each card through to the post; the detail page, already
     * being that destination, renders the same card without the link.
     */
    linked?: boolean;
    /**
     * Which prop liking refreshes. The feed holds its list under `posts`, the
     * detail page a single `post`, and each should reload only its own.
     */
    reloadProp?: string;
};

export function PostCard({ post, linked = true, reloadProp = 'posts' }: Props) {
    /** The same count either way; only what wraps it differs. */
    const commentCount = (
        <>
            <MessageCircle className="size-4" aria-hidden="true" />
            {post.comments_count}
            <span className="sr-only">comments</span>
        </>
    );

    return (
        <Card data-test="post-card">
            <CardContent className="flex gap-3">
                <UserAvatar name={post.author.name} />

                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-baseline gap-x-2">
                        <span className="font-medium">{post.author.name}</span>
                        <time
                            dateTime={post.created_at}
                            className="text-xs text-muted-foreground"
                        >
                            {post.created_at_diff}
                        </time>
                    </div>

                    {linked ? (
                        <Link
                            href={show(post.id)}
                            className="mt-2 block break-words whitespace-pre-line hover:underline"
                            data-test="open-post"
                        >
                            {post.body}
                        </Link>
                    ) : (
                        <p className="mt-2 break-words whitespace-pre-line">
                            {post.body}
                        </p>
                    )}

                    <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                        {/*
                         * Liking and unliking are separate endpoints, so
                         * which one this form posts to is read off the
                         * server's answer rather than off a toggle. The count
                         * that comes back is the server's too — nothing here
                         * mirrors it, so only the post's own prop reloads.
                         */}
                        <Form
                            {...(post.liked
                                ? LikeController.destroy.form(post.id)
                                : LikeController.store.form(post.id))}
                            options={{
                                preserveScroll: true,
                                only: [reloadProp],
                            }}
                        >
                            {({ processing }) => (
                                <LikeButton
                                    count={post.likes_count}
                                    liked={post.liked}
                                    disabled={processing}
                                />
                            )}
                        </Form>

                        {/*
                         * Comments are only ever written on the detail page,
                         * so the feed's count is the way through to them
                         * rather than a composer of its own.
                         */}
                        {linked ? (
                            <Link
                                href={show(post.id)}
                                className="flex items-center gap-1.5 hover:underline"
                                data-test="comments-link"
                            >
                                {commentCount}
                            </Link>
                        ) : (
                            <span className="flex items-center gap-1.5">
                                {commentCount}
                            </span>
                        )}

                        {(post.can.update || post.can.delete) && (
                            <div className="ml-auto flex items-center gap-1">
                                {post.can.update && (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        asChild
                                        data-test="edit-post-link"
                                    >
                                        <Link href={edit(post.id)}>
                                            <Pencil
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                            Edit
                                        </Link>
                                    </Button>
                                )}

                                {post.can.delete && (
                                    <DeletePostDialog postId={post.id} />
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
