<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Fee;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * AdminOutstandingFeesTool.
 *
 * Outstanding (pending/partial/overdue) fee summary for the institution.
 * Read-only. Satisfies the "Outstanding fees" admin/accountant query.
 */
class AdminOutstandingFeesTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::ADMIN_OUTSTANDING_FEES;
    }

    public function name(): string
    {
        return 'admin_outstanding_fees';
    }

    public function execute(User $user): array
    {
        $tenantId = $this->tenantId();

        if ($tenantId === null) {
            return ['summary' => 'No institution in scope.', 'data' => []];
        }

        $fees = Fee::where('institution_id', $tenantId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get();

        $totalDue = $fees->sum(fn ($f) => $f->amount - $f->paid_amount);

        $byStatus = $fees->groupBy('status')->map->count()->all();

        return [
            'summary' => 'Outstanding fees total: '.number_format((float) $totalDue, 2),
            'data' => [
                'total_outstanding' => $totalDue,
                'count' => $fees->count(),
                'by_status' => $byStatus,
            ],
        ];
    }
}
