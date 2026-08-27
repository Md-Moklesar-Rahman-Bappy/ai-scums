<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\Tenant\TenantManager;

/**
 * AttendanceService.
 *
 * Records daily attendance (upsert per student/subject/date) and computes
 * analytics. Used by the Attendance module and the AI assistant.
 */
class AttendanceService
{
    /**
     * Mark attendance for a set of students on a date.
     *
     * @param  array<int, array{student_id: int, status: string}>  $records
     */
    public function mark(string $date, array $records, ?int $subjectId = null, ?int $sectionId = null): void
    {
        foreach ($records as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'subject_id' => $subjectId,
                    'date' => $date,
                ],
                [
                    'institution_id' => \app(TenantManager::class)->getCurrentTenantId(),
                    'section_id' => $sectionId,
                    'status' => $record['status'],
                    'marked_by' => auth()->id(),
                ]
            );
        }
    }

    /**
     * Attendance percentage for a single student.
     */
    public function studentPercentage(Student $student): float
    {
        return Attendance::percentageFor($student->attendances);
    }

    /**
     * Section-wide summary for a date.
     *
     * @return array{present: int, absent: int, late: int, total: int}
     */
    public function sectionSummary(?int $sectionId, string $date): array
    {
        $query = Attendance::whereDate('date', $date);
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        $rows = $query->get();

        return [
            'present' => $rows->where('status', 'present')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'late' => $rows->where('status', 'late')->count(),
            'total' => $rows->count(),
        ];
    }
}
