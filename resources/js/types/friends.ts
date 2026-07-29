/**
 * Where the viewer stands with another person. Computed server-side; no screen
 * reasons about the friendship graph itself.
 */
export type RelationshipState =
    'none' | 'request_sent' | 'request_received' | 'friends';

type Identity = {
    id: number;
    name: string;
};

/**
 * A person in a friends list. The friendship's id is there exactly when a
 * friendship exists, so a row offering an action always has something to act
 * on and the compiler knows it.
 */
export type Person =
    | (Identity & { relationship_state: 'none'; friendship_id: null })
    | (Identity & {
          relationship_state: Exclude<RelationshipState, 'none'>;
          friendship_id: number;
      });

export type FriendsVariant = 'friends' | 'requests' | 'find';
