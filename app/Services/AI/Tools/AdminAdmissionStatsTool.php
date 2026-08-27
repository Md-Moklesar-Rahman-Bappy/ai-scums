<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Student;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * AdminAdmissionStatsTool.
 *
 * Admission statistics for the institution (total, by status, current year).
 * Read-only. Satisfies the "Admission statistics" admin query.
 */
class AdminAdmissionStatsTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::ADMIN_ADMISSION_STATS;
    }

    public function name(): string
    {
        return 'admin_admission_stats';
    }

    public function execute(User $user): array
    {
        $tenantId = $this->tenantId();

        if ($tenantId === null) {
            return ['summary' => 'No institution in scope.', 'data' => []];
        }

        $total = Student::where('institution_id', $tenantId)->count();
        $byStatus = Student::query()->where('institution_id', $tenantId)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')->pluck('c', 'status')->all();

        return [
            'summary' => "Total admissions: {$total}.",
            'data' => ['total' => $total, 'by_status' => $byStatus],
        ];
    }
}
