<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Student;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * AdminEnrollmentReportTool.
 *
 * Enrollment counts by academic unit (class/program). Read-only. Satisfies the
 * "Enrollment report" admin query.
 */
class AdminEnrollmentReportTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::ADMIN_ENROLLMENT_REPORT;
    }

    public function name(): string
    {
        return 'admin_enrollment_report';
    }

    public function execute(User $user): array
    {
        $tenantId = $this->tenantId();

        if ($tenantId === null) {
            return ['summary' => 'No institution in scope.', 'data' => []];
        }

        $byClass = Student::query()->where('institution_id', $tenantId)->whereNotNull('class_id')
            ->selectRaw('class_id, count(*) as students_count')
            ->groupBy('class_id')->with('schoolClass')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->schoolClass->name ?? 'N/A' => $s->getAttribute('students_count')])
            ->all();

        $byProgram = Student::query()->where('institution_id', $tenantId)->whereNotNull('program_id')
            ->selectRaw('program_id, count(*) as students_count')
            ->groupBy('program_id')->with('program')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->program->name ?? 'N/A' => $s->getAttribute('students_count')])
            ->all();

        return [
            'summary' => 'Enrollment breakdown prepared.',
            'data' => ['by_class' => $byClass, 'by_program' => $byProgram],
        ];
    }
}
