<?php

namespace App\Http\Requests;

use App\Enums\RelationshipState;
use App\Models\Friendship;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The four ways a friend request can be wrong: sent to yourself, sent twice,
 * sent to someone whose own request is still waiting for you, and sent to
 * someone you are already friends with. Only the second is caught by the
 * unique index, so all four are stated here.
 */
class StoreFriendshipRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'addressee_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn([$this->user()->id]),
            ],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'addressee_id.exists' => __('That person is not on this network.'),
            'addressee_id.not_in' => __('You cannot send a friend request to yourself.'),
        ];
    }

    /**
     * Refuse a request the friendship graph already answers.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $refusal = $this->refusalForExistingFriendship();

                if ($refusal !== null) {
                    $validator->errors()->add('addressee_id', __($refusal));
                }
            },
        ];
    }

    /**
     * Why the existing friendship — if there is one — forbids a new request.
     */
    private function refusalForExistingFriendship(): ?string
    {
        $existing = Friendship::query()
            ->between($this->user(), (int) $this->input('addressee_id'))
            ->first();

        if ($existing === null) {
            return null;
        }

        return match (RelationshipState::forViewer($existing, $this->user())) {
            RelationshipState::Friends => 'You are already friends with this person.',
            RelationshipState::RequestSent => 'You have already sent this person a friend request.',
            RelationshipState::RequestReceived => 'This person has already sent you a friend request. Respond to it instead.',
            RelationshipState::None => null,
        };
    }
}
