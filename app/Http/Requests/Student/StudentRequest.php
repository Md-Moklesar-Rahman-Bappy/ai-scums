<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * StudentRequest.
 *
 * Validation for student admission/update.
 */
class StudentRequest extends FormRequest
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
            'admission_no' => ['nullable', 'string', 'max:50'],
            'roll_no' => ['nullable', 'string', 'max:30'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'gender' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive,graduated,transferred'],
        ];
    }
}
