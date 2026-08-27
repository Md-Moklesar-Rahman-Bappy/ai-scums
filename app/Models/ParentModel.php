<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ParentModel.
 *
 * Guardian account. Linked 1:N to {@see Student} via the `student_parent`
 * pivot. Named ParentModel to avoid collision with the PHP reserved word.
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $user_id
 */
class ParentModel extends BaseModel
{
    protected $table = 'parents';

    protected $fillable = [
        'institution_id', 'user_id', 'occupation', 'phone', 'address',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Children of this guardian.
     *
     * @return BelongsToMany
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_parent')
            ->withPivot('relationship')
            ->withTimestamps();
    }
}
