<?php

declare(strict_types=1);

namespace App\Http\Requests\Institution;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * InstitutionRequest.
 *
 * Validation for institution create/update.
 */
class InstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route('institution');

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:school,college,university'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
