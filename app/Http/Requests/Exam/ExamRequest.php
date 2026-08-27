<?php

declare(strict_types=1);

namespace App\Http\Requests\Exam;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ExamRequest.
 */
class ExamRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'exam_type' => ['nullable', 'string', 'max:50'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'exam_date' => ['nullable', 'date'],
            'total_marks' => ['nullable', 'integer', 'min:1'],
            'pass_marks' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
