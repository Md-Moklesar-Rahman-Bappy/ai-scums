<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeePayment.
 *
 * A single payment transaction against a {@see Fee}.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $fee_id
 * @property float $amount
 * @property string $payment_method
 */
class FeePayment extends BaseModel
{
    protected $fillable = [
        'institution_id', 'fee_id', 'student_id', 'amount',
        'payment_method', 'transaction_ref', 'collected_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * @return BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
