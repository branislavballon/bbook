import { Link } from '@inertiajs/react';
import { RelationshipAction } from '@/components/relationship-action';
import { Card, CardContent } from '@/components/ui/card';
import { UserAvatar } from '@/components/user-avatar';
import { show } from '@/routes/users';
import type { Person } from '@/types/friends';

type Props = {
    person: Person;
};

/**
 * One person in a friends list. Their name and avatar are the way through to
 * their profile; the action beside them is whatever the relationship allows.
 */
export function PersonRow({ person }: Props) {
    return (
        <Card data-test="person-row">
            <CardContent className="flex items-center gap-3">
                <Link
                    href={show(person.id)}
                    className="flex min-w-0 flex-1 items-center gap-3 hover:underline"
                    data-test="person-profile-link"
                >
                    <UserAvatar name={person.name} />

                    <span className="min-w-0 flex-1 truncate font-medium">
                        {person.name}
                    </span>
                </Link>

                <RelationshipAction person={person} />
            </CardContent>
        </Card>
    );
}
