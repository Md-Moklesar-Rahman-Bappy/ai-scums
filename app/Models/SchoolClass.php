<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SchoolClass.
 *
 * A class/grade within a school (e.g. "Grade 10"). Maps to an academic year.
 * The table is named `classes`; the model is `SchoolClass` to avoid the PHP
 * reserved keyword.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $academic_year_id
 * @property string $name
 */
class SchoolClass extends BaseModel
{
    protected $table = 'classes';

    protected $fillable = [
        'institution_id', 'academic_year_id', 'name',
    ];

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return HasMany<Section, $this>
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
