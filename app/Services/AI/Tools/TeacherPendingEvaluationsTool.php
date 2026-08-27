<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Exam;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * TeacherPendingEvaluationsTool.
 *
 * Lists exams for the teacher's subjects that still have unentered marks.
 * Read-only. Satisfies the "Pending evaluations" teacher query.
 */
class TeacherPendingEvaluationsTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::TEACHER_PENDING_EVALUATIONS;
    }

    public function name(): string
    {
        return 'teacher_pending_evaluations';
    }

    public function execute(User $user): array
    {
        $teacher = $user->teacher;

        if (! $teacher) {
            return ['summary' => 'No linked teacher record found.', 'data' => []];
        }

        $subjectIds = $teacher->subjects()->pluck('id')->unique();

        $exams = Exam::whereIn('subject_id', $subjectIds)->withCount(['marks'])->get();

        $pending = $exams->map(fn ($exam) => [
            'exam' => $exam->name,
            'subject' => $exam->subject?->name,
            'marks_entered' => $exam->marks_count,
        ])->all();

        return [
            'summary' => count($pending).' exams require evaluation tracking.',
            'data' => $pending,
        ];
    }
}
