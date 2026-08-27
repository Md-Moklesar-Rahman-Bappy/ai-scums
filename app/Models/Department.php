<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Department.
 *
 * Academic department, used by colleges and universities. Optionally nested
 * under a {@see Faculty}.
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $faculty_id
 * @property string $name
 */
class Department extends BaseModel
{
    protected $fillable = [
        'institution_id', 'faculty_id', 'name', 'code', 'description',
    ];

    /**
     * @return BelongsTo
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * @return HasMany
     */
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    /**
     * @return HasMany
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}
