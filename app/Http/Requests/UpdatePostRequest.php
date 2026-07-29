<?php

namespace App\Http\Requests;

class UpdatePostRequest extends BodyRequest
{
    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => __('A post cannot be left empty.'),
        ];
    }
}
