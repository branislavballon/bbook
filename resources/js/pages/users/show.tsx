import { Head, usePage } from '@inertiajs/react';
import { Lock, Newspaper } from 'lucide-react';
import AlertError from '@/components/alert-error';
import { EmptyState } from '@/components/empty-state';
import { PostCard } from '@/components/post-card';
import { RelationshipAction } from '@/components/relationship-action';
import { Card, CardContent } from '@/components/ui/card';
import { UserAvatar } from '@/components/user-avatar';
import { feed } from '@/routes';
import { show } from '@/routes/users';
import type { Person } from '@/types/friends';
import type { Post } from '@/types/posts';

type Props = {
    person: Person;
    /**
     * Null when the visibility rule withholds them, which is a different fact
     * from an empty list: one means "not for you", the other "nothing yet".
     */
    posts: Post[] | null;
};

export default function ShowUser({ person, posts }: Props) {
    const { errors } = usePage().props;

    return (
        <>
            <Head title={person.name} />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardContent className="flex flex-wrap items-center gap-4">
                        <UserAvatar name={person.name} className="size-16" />

                        <h1 className="min-w-0 flex-1 truncate text-xl font-semibold">
                            {person.name}
                        </h1>

                        {/*
                         * A profile pointed at the viewer is still the same
                         * page; there is simply nobody to befriend.
                         */}
                        {!person.is_self && (
                            <RelationshipAction person={person} />
                        )}
                    </CardContent>
                </Card>

                {/*
                 * A refused friend request is a fact about the graph rather
                 * than about a field, so it is stated for the page, the way
                 * the friends sections state it.
                 */}
                {errors.addressee_id && (
                    <AlertError
                        errors={[errors.addressee_id]}
                        title="That friend request was not sent."
                    />
                )}

                <section className="flex flex-col gap-4" aria-label="Posts">
                    {posts === null ? (
                        <EmptyState
                            icon={Lock}
                            title={`${person.name}'s posts are visible to friends`}
                            description="Posts on this network are shared with friends only. Send a friend request, and once it is accepted their posts appear here."
                        />
                    ) : posts.length === 0 ? (
                        <EmptyState
                            icon={Newspaper}
                            title={
                                person.is_self
                                    ? 'You have not posted yet'
                                    : `${person.name} has not posted yet`
                            }
                            description={
                                person.is_self
                                    ? 'Posts you write show up here, and on the feeds of your friends.'
                                    : 'When they write something, it will show up here.'
                            }
                        />
                    ) : (
                        posts.map((post) => (
                            <PostCard key={post.id} post={post} />
                        ))
                    )}
                </section>
            </div>
        </>
    );
}

ShowUser.layout = ({ person }: Props) => ({
    breadcrumbs: [
        { title: 'Feed', href: feed() },
        { title: person.name, href: show(person.id) },
    ],
});
