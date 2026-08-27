<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AcademicYear.
 *
 * Represents a school year or college/university "session". Only one may be
 * flagged as current per institution.
 *
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property bool $is_current
 */
class AcademicYear extends BaseModel
{
    protected $fillable = [
        'institution_id', 'name', 'start_date', 'end_date', 'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * @return HasMany
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * @return HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
