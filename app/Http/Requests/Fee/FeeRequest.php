<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FeeRequest.
 */
class FeeRequest extends FormRequest
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
        return [
            'student_id' => ['required', 'exists:students,id'],
            'fee_type_id' => ['nullable', 'exists:fee_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:pending,partial,paid,overdue'],
        ];
    }
}
