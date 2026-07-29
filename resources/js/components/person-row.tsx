import { Form } from '@inertiajs/react';
import { Clock, UserCheck, UserPlus } from 'lucide-react';
import FriendshipController from '@/actions/App/Http/Controllers/FriendshipController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { UserAvatar } from '@/components/user-avatar';
import type { Person } from '@/types/friends';

type Props = {
    person: Person;
};

/**
 * One person in a friends list, with whatever action their current
 * relationship state allows — which is exactly one thing, never a choice.
 */
export function PersonRow({ person }: Props) {
    return (
        <Card data-test="person-row">
            <CardContent className="flex items-center gap-3">
                <UserAvatar name={person.name} />

                <span className="min-w-0 flex-1 truncate font-medium">
                    {person.name}
                </span>

                <RelationshipAction person={person} />
            </CardContent>
        </Card>
    );
}

function RelationshipAction({ person }: Props) {
    switch (person.relationship_state) {
        case 'none':
            return (
                <Form
                    {...FriendshipController.store.form()}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <>
                            <input
                                type="hidden"
                                name="addressee_id"
                                value={person.id}
                            />

                            <Button
                                size="sm"
                                disabled={processing}
                                data-test="send-friend-request"
                            >
                                <UserPlus
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                Add friend
                            </Button>
                        </>
                    )}
                </Form>
            );

        case 'request_sent':
            return (
                <Badge variant="secondary" data-test="request-pending">
                    <Clock className="size-3" aria-hidden="true" />
                    Request sent
                </Badge>
            );

        case 'request_received':
            // Accepting and rejecting arrive with the requests section; until
            // then the row states the fact without offering an action.
            return (
                <Badge variant="secondary" data-test="request-received">
                    <Clock className="size-3" aria-hidden="true" />
                    Wants to be your friend
                </Badge>
            );

        case 'friends':
            return (
                <Badge variant="outline" data-test="already-friends">
                    <UserCheck className="size-3" aria-hidden="true" />
                    Friends
                </Badge>
            );
    }
}
