import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';

type Props = {
    /**
     * The page envelope of whatever list is being paged. Only `links` and
     * `meta` are read, so any `Paginated` will do.
     */
    page: Paginated<unknown>;
    /** Names the list for assistive technology, e.g. "Feed". */
    label: string;
};

/**
 * Previous/next paging for the two lists that grow without bound. Both steps
 * are real links to the URL the server built, so the page lives in the address
 * bar: a page is shareable and the back button walks back through it.
 */
export function Paginator({ page, label }: Props) {
    const { links, meta } = page;

    // A single page is not something to page through.
    if (meta.last_page <= 1) {
        return null;
    }

    return (
        <nav
            aria-label={`${label} pages`}
            className="flex items-center justify-between gap-3"
            data-test="paginator"
        >
            <Step href={links.prev} rel="prev" testId="paginator-previous">
                <ChevronLeft className="size-4" aria-hidden="true" />
                Previous
            </Step>

            <p
                className="text-sm text-muted-foreground"
                data-test="paginator-position"
            >
                Page {meta.current_page} of {meta.last_page}
            </p>

            <Step href={links.next} rel="next" testId="paginator-next">
                Next
                <ChevronRight className="size-4" aria-hidden="true" />
            </Step>
        </nav>
    );
}

type StepProps = {
    /** Null at the end of the run, where the step is shown but unavailable. */
    href: string | null;
    rel: 'prev' | 'next';
    testId: string;
    children: ReactNode;
};

/**
 * One step of the run. The first and last pages keep their step in place as a
 * disabled button rather than dropping it, so the control does not change
 * width or shift the other step as the pages turn.
 */
function Step({ href, rel, testId, children }: StepProps) {
    if (href === null) {
        return (
            <Button variant="outline" size="sm" disabled data-test={testId}>
                {children}
            </Button>
        );
    }

    return (
        <Button variant="outline" size="sm" asChild>
            <Link href={href} rel={rel} data-test={testId}>
                {children}
            </Link>
        </Button>
    );
}
