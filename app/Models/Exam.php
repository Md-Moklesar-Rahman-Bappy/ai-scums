<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Exam.
 *
 * An examination event for a subject/section within an academic year.
 *
 * @property int $id
 * @property int $institution_id
 * @property int|null $subject_id
 * @property string $name
 * @property int $total_marks
 * @property int $pass_marks
 */
class Exam extends BaseModel
{
    protected $fillable = [
        'institution_id', 'academic_year_id', 'subject_id', 'section_id',
        'name', 'exam_type', 'exam_date', 'total_marks', 'pass_marks',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'total_marks' => 'integer',
        'pass_marks' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return HasMany
     */
    public function marks()
    {
        return $this->hasMany(ExamMark::class);
    }
}
