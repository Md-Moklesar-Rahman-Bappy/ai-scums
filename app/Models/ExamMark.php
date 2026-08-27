<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExamMark.
 *
 * A student's score in a given {@see Exam}. The grade is derived from the
 * obtained/total marks via {@see self::deriveGrade()}.
 *
 * @property int $id
 * @property int $institution_id
 * @property int $exam_id
 * @property int $student_id
 * @property float $marks_obtained
 * @property float $total_marks
 * @property string|null $grade
 */
class ExamMark extends BaseModel
{
    protected $fillable = [
        'institution_id', 'exam_id', 'student_id', 'marks_obtained',
        'total_marks', 'grade', 'remarks', 'entered_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Exam, $this>
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Derive a letter grade from obtained/total marks.
     */
    public static function deriveGrade(float $obtained, float $total): string
    {
        if ($total <= 0) {
            return 'N/A';
        }

        $percent = ($obtained / $total) * 100;

        return match (true) {
            $percent >= 90 => 'A+',
            $percent >= 80 => 'A',
            $percent >= 70 => 'B',
            $percent >= 60 => 'C',
            $percent >= 50 => 'D',
            default => 'F',
        };
    }
}
