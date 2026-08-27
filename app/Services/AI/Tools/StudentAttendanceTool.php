<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * StudentAttendanceTool.
 *
 * Returns the acting student's attendance summary (overall percentage and a
 * per-status breakdown). Read-only. Satisfies the "What is my attendance?"
 * student query.
 */
class StudentAttendanceTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::STUDENT_ATTENDANCE;
    }

    public function name(): string
    {
        return 'student_attendance';
    }

    public function execute(User $user): array
    {
        $student = $this->resolveStudentFor($user);

        if (! $student) {
            return ['summary' => 'No linked student record found.', 'data' => []];
        }

        $records = Attendance::where('student_id', $student->id)->get();

        $percentage = Attendance::percentageFor($records);
        $breakdown = [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
        ];

        return [
            'summary' => "Attendance for {$student->admission_no}: {$percentage}% overall.",
            'data' => [
                'student' => $student->admission_no,
                'percentage' => $percentage,
                'breakdown' => $breakdown,
                'total_records' => $records->count(),
            ],
        ];
    }
}
