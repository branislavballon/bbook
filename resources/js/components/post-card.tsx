import { Heart, MessageCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { UserAvatar } from '@/components/user-avatar';
import type { Post } from '@/types/posts';

type Props = {
    post: Post;
};

export function PostCard({ post }: Props) {
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

                    <p className="mt-2 break-words whitespace-pre-line">
                        {post.body}
                    </p>

                    <div className="mt-3 flex items-center gap-4 text-sm text-muted-foreground">
                        <span className="flex items-center gap-1.5">
                            <Heart className="size-4" aria-hidden="true" />
                            {post.likes_count}
                            <span className="sr-only">likes</span>
                        </span>

                        <span className="flex items-center gap-1.5">
                            <MessageCircle
                                className="size-4"
                                aria-hidden="true"
                            />
                            {post.comments_count}
                            <span className="sr-only">comments</span>
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
