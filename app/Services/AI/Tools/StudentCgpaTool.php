<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\ExamMark;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * StudentCgpaTool.
 *
 * Computes the acting student's cumulative grade point average (CGPA) from
 * their exam marks. Read-only. Satisfies the "My CGPA?" student query.
 */
class StudentCgpaTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::STUDENT_CGPA;
    }

    public function name(): string
    {
        return 'student_cgpa';
    }

    public function execute(User $user): array
    {
        $student = $this->resolveStudentFor($user);

        if (! $student) {
            return ['summary' => 'No linked student record found.', 'data' => []];
        }

        $marks = ExamMark::where('student_id', $student->id)->get();

        if ($marks->isEmpty()) {
            return ['summary' => 'No results available yet.', 'data' => []];
        }

        $totalPoints = 0.0;
        $totalCredits = 0;

        foreach ($marks as $mark) {
            $gradePoints = $this->gradeToPoints($mark->grade ?? ExamMark::deriveGrade($mark->marks_obtained, $mark->total_marks));
            $credits = $mark->exam->subject->credit_hours ?? 1;
            $totalPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        $cgpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;

        return [
            'summary' => "CGPA: {$cgpa} across {$marks->count()} exams.",
            'data' => [
                'cgpa' => $cgpa,
                'exams_count' => $marks->count(),
                'total_marks_obtained' => $marks->sum('marks_obtained'),
            ],
        ];
    }

    /**
     * Map a letter grade to a 4.0-scale grade point.
     */
    private function gradeToPoints(string $grade): float
    {
        return match ($grade) {
            'A+' => 4.0, 'A' => 3.7, 'B' => 3.0, 'C' => 2.0, 'D' => 1.0,
            default => 0.0,
        };
    }
}
