<?php

declare(strict_types=1);

namespace App\Http\Requests\Assistant;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * AssistantAskRequest.
 *
 * Validates an assistant query and optional provider override.
 */
class AssistantAskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('assistant.use');
    }

    /**
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:1000'],
            'provider' => ['nullable', 'in:openai,claude,gemini,local'],
        ];
    }
}
