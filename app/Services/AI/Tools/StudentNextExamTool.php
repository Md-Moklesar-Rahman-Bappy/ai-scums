<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Exam;
use App\Models\User;
use App\Services\AI\Intent;
use Illuminate\Support\Carbon;

/**
 * StudentNextExamTool.
 *
 * Returns the next upcoming exam for the acting student. Read-only. Satisfies
 * the "My next exam?" student query.
 */
class StudentNextExamTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::STUDENT_NEXT_EXAM;
    }

    public function name(): string
    {
        return 'student_next_exam';
    }

    public function execute(User $user): array
    {
        $student = $this->resolveStudentFor($user);

        if (! $student) {
            return ['summary' => 'No linked student record found.', 'data' => []];
        }

        $query = Exam::query()
            ->where('exam_date', '>=', Carbon::today())
            ->orderBy('exam_date');

        if ($student->section_id) {
            $query->where('section_id', $student->section_id);
        }

        $exam = $query->first();

        if (! $exam) {
            return ['summary' => 'No upcoming exams scheduled.', 'data' => []];
        }

        return [
            'summary' => "Next exam: {$exam->name} on {$exam->exam_date}.",
            'data' => [
                'name' => $exam->name,
                'subject' => $exam->subject?->name,
                'date' => $exam->exam_date?->toDateString(),
                'total_marks' => $exam->total_marks,
            ],
        ];
    }
}
