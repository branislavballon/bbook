<?php

namespace App\Http\Resources;

use App\Enums\RelationshipState;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The shape every screen reads another person in — the three friends sections
 * and the profile page.
 *
 * Relationship state is resolved here rather than on the client, so no screen
 * reasons about the friendship graph. The friendship's id rides along so
 * whatever offers an action always has something to act on.
 *
 * @mixin User
 */
class PersonResource extends JsonResource
{
    /**
     * @param  Friendship|null  $friendship  The relationship between this person and the viewer, if any.
     */
    public function __construct(
        User $person,
        private readonly ?Friendship $friendship,
        private readonly User $viewer,
    ) {
        parent::__construct($person);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'relationship_state' => RelationshipState::forViewer($this->friendship, $this->viewer)->value,
            'friendship_id' => $this->friendship?->id,
            // Nobody is in a friendship with themselves, so this is the one
            // thing the relationship state cannot express. Only a profile can
            // be pointed at the viewer; in a list it is always false.
            //
            // `is` compares keys rather than object identity: the viewer and
            // this person are separately hydrated, so `===` would never hold.
            'is_self' => $this->is($this->viewer),
        ];
    }
}
