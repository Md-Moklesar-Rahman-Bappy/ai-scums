<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Student.
 *
 * Represents an enrolled student. Links to an optional {@see User} account,
 * an academic placement (class/section or department/program/semester) and
 * guardian contact details.
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $user_id
 * @property string $admission_no
 * @property string $status active|inactive|graduated|transferred
 */
class Student extends BaseModel
{
    protected $fillable = [
        'institution_id', 'user_id', 'admission_no', 'roll_no',
        'academic_year_id', 'class_id', 'section_id', 'department_id',
        'program_id', 'semester_id', 'gender', 'date_of_birth', 'blood_group',
        'guardian_name', 'guardian_phone', 'address', 'admission_date', 'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return BelongsTo
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Parents/guardians of this student.
     *
     * @return BelongsToMany
     */
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'student_parent')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * Attendance records.
     *
     * @return HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Exam marks.
     *
     * @return HasMany
     */
    public function examMarks()
    {
        return $this->hasMany(ExamMark::class);
    }

    /**
     * Fees assigned to this student.
     *
     * @return HasMany
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}
