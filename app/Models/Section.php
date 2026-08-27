<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Section.
 *
 * A division of a {@see SchoolClass} (e.g. "Section A").
 *
 * @property int $id
 * @property int $institution_id
 * @property int $class_id
 * @property string $name
 */
class Section extends BaseModel
{
    protected $fillable = [
        'institution_id', 'class_id', 'name',
    ];

    /**
     * @return BelongsTo
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
