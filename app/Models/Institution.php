<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Institution.
 *
 * The tenant root entity. Supports three institution types (school, college,
 * university) that each drive a different academic hierarchy while sharing the
 * same security and storage model.
 *
 * @property int $id
 * @property string $name
 * @property string $type school|college|university
 * @property string $slug
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'type', 'slug', 'email', 'phone', 'address',
        'logo', 'website', 'settings', 'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Users that belong to this institution.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Students that belong to this institution.
     *
     * @return HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope a query to active institutions.
     *
     * @param  Builder  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
