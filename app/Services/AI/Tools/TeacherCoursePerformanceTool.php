<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\ExamMark;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * TeacherCoursePerformanceTool.
 *
 * Aggregates average marks per allocated subject for the teacher. Read-only.
 * Satisfies the "Course performance analysis" teacher query.
 */
class TeacherCoursePerformanceTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::TEACHER_COURSE_PERFORMANCE;
    }

    public function name(): string
    {
        return 'teacher_course_performance';
    }

    public function execute(User $user): array
    {
        $teacher = $user->teacher;

        if (! $teacher) {
            return ['summary' => 'No linked teacher record found.', 'data' => []];
        }

        $subjectIds = $teacher->subjects()->pluck('id')->unique();

        $performance = ExamMark::whereIn('subject_id', function ($query) use ($subjectIds) {
            $query->select('id')->from('exams')->whereIn('subject_id', $subjectIds);
        })
            ->with('exam.subject')
            ->get()
            ->groupBy(fn ($m) => $m->exam->subject?->name ?? 'Unknown')
            ->map(fn ($group) => [
            'average' => round($group->avg('marks_obtained'), 2),
            'students' => $group->pluck('student_id')->unique()->count(),
        ])
            ->all();

        return [
            'summary' => 'Performance computed for '.count($performance).' subjects.',
            'data' => $performance,
        ];
    }
}
