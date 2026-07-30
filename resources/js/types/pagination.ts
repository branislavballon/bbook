/**
 * A page of a list, in the envelope a Laravel resource collection produces for
 * a paginator. Only the two lists that grow without bound arrive this way —
 * the feed and find people.
 *
 * The server sends more than this under `links` and `meta`; declared here is
 * what the paginator actually reads.
 */
export type Paginated<T> = {
    data: T[];
    links: {
        /** Null on the first page, which is how the control knows to stop. */
        prev: string | null;
        /** Null on the last page, likewise. */
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
    };
};
