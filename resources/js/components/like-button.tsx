import { Heart } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type Props = {
    count: number;
    liked: boolean;
    disabled?: boolean;
};

/**
 * The like control, and nothing else: it is told the count and whether the
 * viewer has liked, and it submits whatever form encloses it. Where that form
 * posts and what it refreshes belong to whatever renders it.
 */
export function LikeButton({ count, liked, disabled = false }: Props) {
    return (
        <Button
            type="submit"
            variant="ghost"
            size="sm"
            className={cn(
                '-ml-2.5 gap-1.5',
                liked && 'text-red-600 dark:text-red-500',
            )}
            aria-pressed={liked}
            aria-label={`${liked ? 'Unlike' : 'Like'} this post, ${count} ${count === 1 ? 'like' : 'likes'}`}
            disabled={disabled}
            data-test="like-button"
        >
            <Heart
                className={cn('size-4', liked && 'fill-current')}
                aria-hidden="true"
            />
            <span aria-hidden="true">{count}</span>
        </Button>
    );
}
