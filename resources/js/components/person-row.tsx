import { Form } from '@inertiajs/react';
import { Check, Clock, UserCheck, UserPlus, X } from 'lucide-react';
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
 * One person in a friends list, showing whatever their current relationship
 * state allows: a single action, a statement of fact, or — for a request
 * waiting on this person — the two answers to it.
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
            // The one state with a choice in it. Both answers are offered
            // wherever the request appears — the Requests section and the row
            // for that person in Find People — so neither screen is a dead end.
            return (
                <div className="flex items-center gap-2">
                    <Form
                        {...FriendshipController.update.form(
                            person.friendship_id,
                        )}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                size="sm"
                                disabled={processing}
                                data-test="accept-friend-request"
                            >
                                <Check className="size-4" aria-hidden="true" />
                                Accept
                            </Button>
                        )}
                    </Form>

                    <Form
                        {...FriendshipController.destroy.form(
                            person.friendship_id,
                        )}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={processing}
                                data-test="reject-friend-request"
                            >
                                <X className="size-4" aria-hidden="true" />
                                Reject
                            </Button>
                        )}
                    </Form>
                </div>
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
