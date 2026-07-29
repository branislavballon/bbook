<?php

namespace App\Http\Requests;

class StoreCommentRequest extends BodyRequest
{
    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => __('Write something before commenting.'),
        ];
    }
}
