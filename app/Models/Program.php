<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Program.
 *
 * Academic program/degree offered under a {@see Department}
 * (e.g. "BSc Computer Science").
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $department_id
 * @property string $name
 */
class Program extends BaseModel
{
    protected $fillable = [
        'institution_id', 'department_id', 'name', 'code', 'degree',
    ];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany
     */
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
