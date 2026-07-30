import { Heart } from 'lucide-react';
import { useEffect, useState } from 'react';
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
    /**
     * The pop has to fire on a transition, not on a state.
     */
    const [justLiked, setJustLiked] = useState(false);

    /**
     * The flag has to expire on its own rather than on `animationend`. The
     * animation is gated behind `motion-safe:`, so under reduced motion it
     * never runs and never ends — the flag would stay raised for the life of
     * the component, and the moment the viewer turned reduced motion back off
     * a heart nobody touched would pop. A timer just past the animation makes
     * "just pressed" mean the same thing in both motion modes.
     */
    useEffect(() => {
        if (!justLiked) {
            return;
        }

        const expiry = window.setTimeout(() => setJustLiked(false), 400);

        return () => window.clearTimeout(expiry);
    }, [justLiked]);

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
            onClick={() => setJustLiked(!liked)}
        >
            <Heart
                className={cn(
                    'size-4 fill-current motion-safe:transition-[fill-opacity] motion-safe:duration-200',
                    liked ? '[fill-opacity:1]' : '[fill-opacity:0]',
                    justLiked && 'motion-safe:animate-heart-pop',
                )}
                aria-hidden="true"
            />
            <span aria-hidden="true">{count}</span>
        </Button>
    );
}
