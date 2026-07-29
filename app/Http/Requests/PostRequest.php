<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The rules a post body has to satisfy, whether it is being written or edited.
 * Authorization stays in the controller, through PostPolicy.
 */
abstract class PostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Trim the body before validation, so whitespace-only text fails the
     * "required" rule instead of being stored as an empty post.
     */
    protected function prepareForValidation(): void
    {
        $body = $this->input('body');

        if (is_string($body)) {
            $this->merge(['body' => trim($body)]);
        }
    }
}
