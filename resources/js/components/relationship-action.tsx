import { Form } from '@inertiajs/react';
import { Check, Clock, UserCheck, UserPlus, X } from 'lucide-react';
import FriendshipController from '@/actions/App/Http/Controllers/FriendshipController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Person } from '@/types/friends';

type Props = {
    person: Person;
};

/**
 * Whatever a person's current relationship state allows: a single action, a
 * statement of fact, or — for a request waiting on the viewer — the two
 * answers to it.
 *
 * Shared by the friends lists and the profile page, so accepting or rejecting
 * goes through the same operations wherever it is offered, and the switch on
 * the four server-computed states is written once.
 */
export function RelationshipAction({ person }: Props) {
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
            // wherever the request appears — the Requests section, the row for
            // that person in Find People, and their profile — so none of them
            // is a dead end.
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
