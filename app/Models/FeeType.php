<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FeeType.
 *
 * A category of fee (e.g. tuition, exam, transport) with a default amount.
 *
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property float $default_amount
 */
class FeeType extends BaseModel
{
    protected $fillable = [
        'institution_id', 'name', 'description', 'default_amount',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
    ];

    /**
     * @return HasMany
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}
