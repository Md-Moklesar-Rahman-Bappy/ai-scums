<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User.
 *
 * Application user. Supports spatie role/permission based access control,
 * soft deletes and an optional tenant (institution) linkage. Super admins
 * (is_super_admin) are platform-wide and may switch institutions.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $institution_id
 * @property bool $is_super_admin
 * @property bool $is_active
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'institution_id', 'phone',
        'avatar', 'is_super_admin', 'is_active', 'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * The institution this user belongs to (null for super admin).
     *
     * @return BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Student profile, if any.
     *
     * @return HasOne
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Teacher profile, if any.
     *
     * @return HasOne
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Parent profile, if any.
     *
     * @return HasOne
     */
    public function parent()
    {
        return $this->hasOne(ParentModel::class);
    }

    /**
     * Determine whether the user is a platform super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }
}
