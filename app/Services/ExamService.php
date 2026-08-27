<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamMark;
use App\Repositories\ExamRepository;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * ExamService.
 *
 * Examination management and result processing. Marks are derived into grades
 * via {@see ExamMark::deriveGrade()}. The AI assistant reads this
 * data but never writes it (see AuthorizationGate).
 */
class ExamService
{
    public function __construct(private readonly ExamRepository $repository) {}

    /**
     * List exams paginated.
     */
    public function list(int $perPage = 15): Paginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Create an exam.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Exam
    {
        return $this->repository->create($data);
    }

    /**
     * Enter/update bulk marks, deriving grades.
     *
     * @param  array<int, array{student_id: int, marks_obtained: float}>  $marks
     */
    public function enterMarks(Exam $exam, array $marks): void
    {
        foreach ($marks as $row) {
            $grade = ExamMark::deriveGrade((float) $row['marks_obtained'], $exam->total_marks);

            ExamMark::updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $row['student_id']],
                [
                    'institution_id' => $exam->institution_id,
                    'marks_obtained' => $row['marks_obtained'],
                    'total_marks' => $exam->total_marks,
                    'grade' => $grade,
                    'entered_by' => auth()->id(),
                ]
            );
        }
    }

    /**
     * Result summary for an exam.
     *
     * @return array{average: float, highest: float, pass_rate: float}
     */
    public function resultSummary(Exam $exam): array
    {
        $marks = $exam->marks;
        $count = $marks->count();

        return [
            'average' => $count ? round($marks->avg('marks_obtained'), 2) : 0,
            'highest' => $count ? $marks->max('marks_obtained') : 0,
            'pass_rate' => $count
                ? round($marks->where('marks_obtained', '>=', $exam->pass_marks)->count() / $count * 100, 2)
                : 0,
        ];
    }
}
