<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TeacherRequest.
 */
class TeacherRequest extends FormRequest
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
            'employee_id' => ['required', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ];
    }
}
