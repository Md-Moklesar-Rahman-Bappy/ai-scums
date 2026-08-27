<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Intent.
 *
 * Enumerates the assistant's supported intents. Each intent maps to a single
 * read-only data tool and a set of roles permitted to invoke it. The "Step 2:
 * Intent Detection" stage of the assistant resolves a user query to one of
 * these constants.
 */
final class Intent
{
    public const STUDENT_ATTENDANCE = 'student_attendance';

    public const STUDENT_NEXT_EXAM = 'student_next_exam';

    public const STUDENT_CGPA = 'student_cgpa';

    public const STUDENT_SCHEDULE = 'student_schedule';

    public const TEACHER_LOW_ATTENDANCE = 'teacher_low_attendance';

    public const TEACHER_COURSE_PERFORMANCE = 'teacher_course_performance';

    public const TEACHER_PENDING_EVALUATIONS = 'teacher_pending_evaluations';

    public const ADMIN_ADMISSION_STATS = 'admin_admission_stats';

    public const ADMIN_ENROLLMENT_REPORT = 'admin_enrollment_report';

    public const ADMIN_OUTSTANDING_FEES = 'admin_outstanding_fees';

    public const GENERAL = 'general';

    /**
     * All intent constants.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::STUDENT_ATTENDANCE,
            self::STUDENT_NEXT_EXAM,
            self::STUDENT_CGPA,
            self::STUDENT_SCHEDULE,
            self::TEACHER_LOW_ATTENDANCE,
            self::TEACHER_COURSE_PERFORMANCE,
            self::TEACHER_PENDING_EVALUATIONS,
            self::ADMIN_ADMISSION_STATS,
            self::ADMIN_ENROLLMENT_REPORT,
            self::ADMIN_OUTSTANDING_FEES,
            self::GENERAL,
        ];
    }
}
