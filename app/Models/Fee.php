<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fee.
 *
 * A fee assigned to a student. Tracks amount, paid amount and status. Payments
 * are recorded in {@see FeePayment}.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $student_id
 * @property float $amount
 * @property float $paid_amount
 * @property string $status pending|partial|paid|overdue
 */
class Fee extends BaseModel
{
    protected $fillable = [
        'institution_id', 'student_id', 'fee_type_id', 'amount',
        'paid_amount', 'due_date', 'paid_date', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<FeeType, $this>
     */
    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * @return HasMany<FeePayment, $this>
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Recompute status from paid vs total amount.
     */
    public function recalcStatus(): void
    {
        $this->status = match (true) {
            $this->paid_amount >= $this->amount => 'paid',
            $this->paid_amount > 0 => 'partial',
            $this->due_date && $this->due_date->isPast() => 'overdue',
            default => 'pending',
        };
    }
}
