<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Repositories\FeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * FeeService.
 *
 * Fee assignment, payment tracking and due reporting. Recording a payment
 * updates paid_amount and recomputes status (see {@see Fee::recalcStatus}).
 */
class FeeService
{
    public function __construct(private readonly FeeRepository $repository) {}

    /**
     * @return LengthAwarePaginator<int, Fee>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Assign a fee to a student.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Fee
    {
        $fee = $this->repository->create($data);
        $fee->recalcStatus();
        $fee->save();

        return $fee;
    }

    /**
     * Record a payment and recompute the fee status.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Fee $fee, array $data): FeePayment
    {
        /** @var FeePayment $payment */
        $payment = $fee->payments()->create(array_merge($data, [
            'institution_id' => $fee->institution_id,
            'student_id' => $fee->student_id,
            'collected_by' => auth()->id(),
        ]));

        $fee->paid_amount += $payment->amount;
        $fee->paid_date = $fee->paid_date ?? now();
        $fee->recalcStatus();
        $fee->save();

        return $payment;
    }

    /**
     * Outstanding (unpaid) fee summary.
     *
     * @return array{total: float, count: int}
     */
    public function dueReport(): array
    {
        $due = Fee::whereIn('status', ['pending', 'partial', 'overdue'])->get();
        $total = $due->sum(fn ($f) => $f->amount - $f->paid_amount);

        return ['total' => (float) $total, 'count' => $due->count()];
    }
}
