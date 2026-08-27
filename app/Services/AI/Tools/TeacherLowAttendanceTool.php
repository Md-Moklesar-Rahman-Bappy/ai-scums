<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * TeacherLowAttendanceTool.
 *
 * Lists students below the 75% attendance threshold within the teacher's
 * allocated sections/subjects. Read-only. Satisfies the "Students below 75%
 * attendance" teacher query.
 */
class TeacherLowAttendanceTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::TEACHER_LOW_ATTENDANCE;
    }

    public function name(): string
    {
        return 'teacher_low_attendance';
    }

    public function execute(User $user): array
    {
        $teacher = $user->teacher;

        if (! $teacher) {
            return ['summary' => 'No linked teacher record found.', 'data' => []];
        }

        $sectionIds = $teacher->subjects()->pluck('section_id')->filter()->unique();
        $subjectIds = $teacher->subjects()->pluck('id')->unique();

        $students = Student::query()
            ->when($sectionIds->isNotEmpty(), fn ($q) => $q->whereIn('section_id', $sectionIds))
            ->get();

        $atRisk = [];

        foreach ($students as $student) {
            $records = Attendance::where('student_id', $student->id)
                ->when($subjectIds->isNotEmpty(), fn ($q) => $q->whereIn('subject_id', $subjectIds))
                ->get();

            $percentage = Attendance::percentageFor($records);
            if ($percentage < 75) {
                $atRisk[] = [
                    'student' => $student->admission_no,
                    'attendance' => $percentage,
                ];
            }
        }

        return [
            'summary' => count($atRisk).' students below 75% attendance.',
            'data' => $atRisk,
        ];
    }
}
