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
        // Only platform super admins may override the AI provider. Regular
        // users must use the institution-configured default so that student
        // data (even in aggregate) is never egressed to a provider of their
        // choosing. The "mock" provider (the configured default for demos and
        // tests) must be accepted when an override is permitted.
        $providerRule = $this->user()?->isSuperAdmin()
            ? ['nullable', 'in:openai,claude,gemini,local,mock']
            : ['prohibited'];

        return [
            'query' => ['required', 'string', 'max:1000'],
            'provider' => $providerRule,
        ];
    }
}
