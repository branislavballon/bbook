import { UserAvatar } from '@/components/user-avatar';
import type { Comment } from '@/types/posts';

type Props = {
    comment: Comment;
};

/**
 * One comment in a thread: who wrote it, when, and what they said. Nothing is
 * actionable here — comments are never edited or deleted.
 */
export function CommentItem({ comment }: Props) {
    return (
        <article className="flex gap-3" data-test="comment-item">
            <UserAvatar name={comment.author.name} className="size-8" />

            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-baseline gap-x-2">
                    <span className="text-sm font-medium">
                        {comment.author.name}
                    </span>
                    <time
                        dateTime={comment.created_at}
                        className="text-xs text-muted-foreground"
                    >
                        {comment.created_at_diff}
                    </time>
                </div>

                <p className="mt-1 text-sm break-words whitespace-pre-line">
                    {comment.body}
                </p>
            </div>
        </article>
    );
}
