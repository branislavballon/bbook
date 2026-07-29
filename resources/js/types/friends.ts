/**
 * Where the viewer stands with another person. Computed server-side; no screen
 * reasons about the friendship graph itself.
 */
export type RelationshipState =
    'none' | 'request_sent' | 'request_received' | 'friends';

type Identity = {
    id: number;
    name: string;
    /**
     * The one thing the relationship state cannot express, because nobody is
     * in a friendship with themselves. Only a profile can be pointed at the
     * viewer; in a list it is always false.
     */
    is_self: boolean;
};

/**
 * A person as every screen reads them — the friends lists and the profile
 * page. The friendship's id is there exactly when a friendship exists, so
 * whatever offers an action always has something to act on and the compiler
 * knows it.
 */
export type Person =
    | (Identity & { relationship_state: 'none'; friendship_id: null })
    | (Identity & {
          relationship_state: Exclude<RelationshipState, 'none'>;
          friendship_id: number;
      });

export type FriendsVariant = 'friends' | 'requests' | 'find';
