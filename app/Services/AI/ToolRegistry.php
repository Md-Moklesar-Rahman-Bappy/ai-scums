<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AIDataToolInterface;
use App\Services\AI\Tools\AdminAdmissionStatsTool;
use App\Services\AI\Tools\AdminEnrollmentReportTool;
use App\Services\AI\Tools\AdminOutstandingFeesTool;
use App\Services\AI\Tools\StudentAttendanceTool;
use App\Services\AI\Tools\StudentCgpaTool;
use App\Services\AI\Tools\StudentNextExamTool;
use App\Services\AI\Tools\StudentScheduleTool;
use App\Services\AI\Tools\TeacherCoursePerformanceTool;
use App\Services\AI\Tools\TeacherLowAttendanceTool;
use App\Services\AI\Tools\TeacherPendingEvaluationsTool;

/**
 * ToolRegistry.
 *
 * Maps each {@see Intent} to its read-only {@see AIDataToolInterface}
 * implementation. The assistant consults this registry during "Step 4: Data
 * Retrieval" after intent detection and authorization succeed.
 */
class ToolRegistry
{
    /**
     * Build the intent => tool map.
     *
     * @return array<string, AIDataToolInterface>
     */
    public function all(): array
    {
        return [
            Intent::STUDENT_ATTENDANCE => app(StudentAttendanceTool::class),
            Intent::STUDENT_NEXT_EXAM => app(StudentNextExamTool::class),
            Intent::STUDENT_CGPA => app(StudentCgpaTool::class),
            Intent::STUDENT_SCHEDULE => app(StudentScheduleTool::class),
            Intent::TEACHER_LOW_ATTENDANCE => app(TeacherLowAttendanceTool::class),
            Intent::TEACHER_COURSE_PERFORMANCE => app(TeacherCoursePerformanceTool::class),
            Intent::TEACHER_PENDING_EVALUATIONS => app(TeacherPendingEvaluationsTool::class),
            Intent::ADMIN_ADMISSION_STATS => app(AdminAdmissionStatsTool::class),
            Intent::ADMIN_ENROLLMENT_REPORT => app(AdminEnrollmentReportTool::class),
            Intent::ADMIN_OUTSTANDING_FEES => app(AdminOutstandingFeesTool::class),
        ];
    }

    /**
     * Resolve the tool for an intent, or null if none exists.
     */
    public function forIntent(string $intent): ?AIDataToolInterface
    {
        return $this->all()[$intent] ?? null;
    }
}
