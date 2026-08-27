<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * AttendanceMarkRequest.
 *
 * Validates a bulk attendance marking payload.
 */
class AttendanceMarkRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late,half_day'],
        ];
    }
}
