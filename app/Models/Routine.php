<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Routine.
 *
 * A scheduled class or exam slot. Rendered on a FullCalendar view. Weekly
 * routines use {@see self::$dayOfWeek} (1=Mon .. 7=Sun); exam routines use the
 * same structure with type 'exam'.
 *
 * @property int $id
 * @property int $institution_id
 * @property string $type class|exam
 * @property int|null $subject_id
 * @property int|null $teacher_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 */
class Routine extends BaseModel
{
    protected $fillable = [
        'institution_id', 'type', 'subject_id', 'teacher_id', 'section_id',
        'day_of_week', 'start_time', 'end_time', 'room',
        'effective_from', 'effective_to',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * @return BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
