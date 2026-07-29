/**
 * Where the viewer stands with another person. Computed server-side; no screen
 * reasons about the friendship graph itself.
 */
export type RelationshipState =
    'none' | 'request_sent' | 'request_received' | 'friends';

export type Person = {
    id: number;
    name: string;
    relationship_state: RelationshipState;
};

export type FriendsVariant = 'friends' | 'requests' | 'find';
