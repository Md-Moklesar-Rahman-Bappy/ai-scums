<?php

declare(strict_types=1);

namespace App\Http\Requests\Routine;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * RoutineRequest.
 */
class RoutineRequest extends FormRequest
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
            'type' => ['required', 'in:class,exam'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ];
    }
}
