<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Teacher.
 *
 * Academic staff member. Links to an optional {@see User} account and a
 * {@see Department}. Allocates subjects via the `teacher_subject` pivot.
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $user_id
 * @property string $employee_id
 */
class Teacher extends BaseModel
{
    protected $fillable = [
        'institution_id', 'user_id', 'employee_id', 'department_id',
        'designation', 'qualification', 'joining_date', 'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Allocated subjects.
     *
     * @return BelongsToMany<Subject, $this>
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
            ->withTimestamps();
    }
}
