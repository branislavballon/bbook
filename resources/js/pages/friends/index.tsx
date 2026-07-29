import { Head, Link, usePage } from '@inertiajs/react';
import { Inbox, UserPlus, Users } from 'lucide-react';
import AlertError from '@/components/alert-error';
import { EmptyState } from '@/components/empty-state';
import { PersonRow } from '@/components/person-row';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { find, index, requests } from '@/routes/friends';
import type { FriendsVariant, Person } from '@/types/friends';

type Props = {
    variant: FriendsVariant;
    people: Person[];
};

const sections = [
    { variant: 'friends', title: 'Friends', href: index },
    { variant: 'requests', title: 'Requests', href: requests },
    { variant: 'find', title: 'Find People', href: find },
] as const;

/**
 * The one place a section's title and route live, so the tabs, the page title
 * and the breadcrumb all read the same entry.
 */
function sectionFor(variant: FriendsVariant) {
    return sections.find((section) => section.variant === variant)!;
}

const emptyStates = {
    friends: {
        icon: Users,
        title: 'No friends yet',
        description:
            'People whose friend request you accepted, and who accepted yours, appear here. Find people to send the first request.',
    },
    requests: {
        icon: Inbox,
        title: 'No friend requests',
        description:
            'When someone sends you a friend request, it waits here for your answer.',
    },
    find: {
        icon: UserPlus,
        title: 'Nobody else is here yet',
        description:
            'Everyone else on the network shows up here, so you can send them a friend request.',
    },
} satisfies Record<FriendsVariant, Parameters<typeof EmptyState>[0]>;

export default function Friends({ variant, people }: Props) {
    const { errors } = usePage().props;
    const { title } = sectionFor(variant);
    const emptyState = emptyStates[variant];

    return (
        <>
            <Head title={title} />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Friends</h1>

                <nav
                    aria-label="Friends sections"
                    className="flex gap-1 rounded-lg bg-muted p-1"
                >
                    {sections.map((section) => (
                        <Button
                            key={section.variant}
                            variant="ghost"
                            size="sm"
                            asChild
                            className={cn(
                                // The ghost variant hovers to `accent`, which
                                // this theme defines as the same colour as the
                                // `muted` track behind the tabs — invisible.
                                // Inactive tabs rise towards the active tab's
                                // background instead; the active one holds.
                                'flex-1 transition-colors',
                                section.variant === variant
                                    ? 'bg-background shadow-xs hover:bg-background'
                                    : 'text-muted-foreground hover:bg-background/60 hover:text-foreground',
                            )}
                        >
                            <Link
                                href={section.href()}
                                aria-current={
                                    section.variant === variant
                                        ? 'page'
                                        : undefined
                                }
                                data-test={`friends-tab-${section.variant}`}
                            >
                                {section.title}
                            </Link>
                        </Button>
                    ))}
                </nav>

                {/*
                 * A refused friend request is a fact about the graph, not about
                 * a field the person typed in, so it is stated once for the
                 * page rather than under the row that was clicked.
                 */}
                {errors.addressee_id && (
                    <AlertError
                        errors={[errors.addressee_id]}
                        title="That friend request was not sent."
                    />
                )}

                {people.length === 0 ? (
                    <EmptyState {...emptyState} />
                ) : (
                    <div className="flex flex-col gap-3">
                        {people.map((person) => (
                            <PersonRow key={person.id} person={person} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Friends.layout = ({ variant }: Props) => {
    const section = sectionFor(variant);

    return {
        breadcrumbs: [
            { title: 'Friends', href: index() },
            // The friends section is the destination itself, so it is already
            // the first crumb; the other two hang off it.
            ...(variant === 'friends'
                ? []
                : [{ title: section.title, href: section.href() }]),
        ],
    };
};
