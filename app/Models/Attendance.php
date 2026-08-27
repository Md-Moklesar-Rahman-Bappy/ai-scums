<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance.
 *
 * Daily attendance record per student (optionally per subject/section). The
 * unique composite key prevents duplicate entries for the same student,
 * subject and date.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $student_id
 * @property int|null $subject_id
 * @property string $date
 * @property string $status present|absent|late|half_day
 */
class Attendance extends BaseModel
{
    protected $fillable = [
        'institution_id', 'student_id', 'subject_id', 'section_id',
        'date', 'status', 'marked_by', 'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Percentage of present records for a set of attendances.
     *
     * @param  Collection<int, Attendance>  $records
     */
    public static function percentageFor(Collection $records): float
    {
        if ($records->isEmpty()) {
            return 0.0;
        }

        $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();

        return round(($present / $records->count()) * 100, 2);
    }
}
