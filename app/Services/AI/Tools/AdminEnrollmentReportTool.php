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
        $byClass = Student::query()->whereNotNull('class_id')
            ->selectRaw('class_id, count(*) as c')
            ->groupBy('class_id')->with('schoolClass')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->schoolClass?->name ?? 'N/A' => $s->c])
            ->all();

        $byProgram = Student::query()->whereNotNull('program_id')
            ->selectRaw('program_id, count(*) as c')
            ->groupBy('program_id')->with('program')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->program?->name ?? 'N/A' => $s->c])
            ->all();

        return [
            'summary' => 'Enrollment breakdown prepared.',
            'data' => ['by_class' => $byClass, 'by_program' => $byProgram],
        ];
    }
}
