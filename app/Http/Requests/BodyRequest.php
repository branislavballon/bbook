<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The rules a piece of written text has to satisfy — required, trimmed and
 * capped — whether it is a post being written or edited, or a comment being
 * added. The spec states that as one rule for both, so it is written once and
 * the subclasses supply only the wording of the refusal.
 *
 * Authorization is not asked here: every route that reaches one of these has
 * already run its policy as route middleware, so someone who may not act on
 * the post is refused rather than told their text is wrong.
 */
abstract class BodyRequest extends FormRequest
{
    public const int MAX_LENGTH = 1000;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:'.self::MAX_LENGTH],
        ];
    }

    /**
     * Trim the body before validation, so whitespace-only text fails the
     * "required" rule instead of being stored as empty.
     */
    protected function prepareForValidation(): void
    {
        $body = $this->input('body');

        if (is_string($body)) {
            $this->merge(['body' => trim($body)]);
        }
    }
}
