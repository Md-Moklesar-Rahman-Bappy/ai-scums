<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Subject.
 *
 * Unified subject/course entity. A single row may represent a school subject,
 * a college subject or a university course depending on which nullable
 * academic foreign keys are populated. The `type` column disambiguates
 * 'subject' vs 'course'.
 *
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property string $type subject|course
 * @property int|null $class_id
 * @property int|null $section_id
 * @property int|null $department_id
 * @property int|null $program_id
 * @property int|null $semester_id
 * @property int|null $credit_hours
 */
class Subject extends BaseModel
{
    protected $fillable = [
        'institution_id', 'name', 'code', 'type',
        'academic_year_id', 'class_id', 'section_id', 'department_id',
        'program_id', 'semester_id', 'faculty_id', 'credit_hours', 'description',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
    ];

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Teachers allocated to this subject.
     *
     * @return BelongsToMany<Teacher, $this>
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject')
            ->withTimestamps();
    }
}
