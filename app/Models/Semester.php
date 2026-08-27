<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Semester.
 *
 * A term within a {@see Program} (university). Carries an ordinal number.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $program_id
 * @property string $name
 * @property int $number
 */
class Semester extends BaseModel
{
    protected $fillable = [
        'institution_id', 'program_id', 'name', 'number',
    ];

    /**
     * @return BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
