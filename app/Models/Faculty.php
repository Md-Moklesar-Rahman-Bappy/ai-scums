<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Faculty.
 *
 * Top-level academic division of a university (e.g. "Faculty of Engineering").
 *
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property string|null $code
 */
class Faculty extends BaseModel
{
    protected $fillable = [
        'institution_id', 'name', 'code', 'description',
    ];

    /**
     * @return HasMany
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
