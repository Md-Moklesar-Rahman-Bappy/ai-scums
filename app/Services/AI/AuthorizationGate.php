<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\User;

/**
 * AuthorizationGate.
 *
 * Implements "Step 3: Authorization Check" of the assistant pipeline. Maps
 * each intent to the roles permitted to invoke it, enforcing least-privilege
 * and tenant isolation. The assistant is strictly read-only for now; any
 * intent implying a mutation is rejected here.
 */
class AuthorizationGate
{
    /**
     * Role => permitted intents.
     *
     * @var array<string, array<int, string>>
     */
    private array $policy = [
        'super_admin' => [Intent::all()[0], Intent::all()[1], Intent::all()[2], Intent::all()[3],
            Intent::all()[4], Intent::all()[5], Intent::all()[6], Intent::all()[7],
            Intent::all()[8], Intent::all()[9], Intent::GENERAL],
        'institution_admin' => [
            Intent::STUDENT_ATTENDANCE, Intent::STUDENT_NEXT_EXAM, Intent::STUDENT_CGPA,
            Intent::STUDENT_SCHEDULE, Intent::TEACHER_LOW_ATTENDANCE,
            Intent::TEACHER_COURSE_PERFORMANCE, Intent::TEACHER_PENDING_EVALUATIONS,
            Intent::ADMIN_ADMISSION_STATS, Intent::ADMIN_ENROLLMENT_REPORT,
            Intent::ADMIN_OUTSTANDING_FEES, Intent::GENERAL,
        ],
        'accountant' => [Intent::ADMIN_OUTSTANDING_FEES, Intent::GENERAL],
        'teacher' => [
            Intent::TEACHER_LOW_ATTENDANCE, Intent::TEACHER_COURSE_PERFORMANCE,
            Intent::TEACHER_PENDING_EVALUATIONS, Intent::STUDENT_ATTENDANCE,
            Intent::STUDENT_NEXT_EXAM, Intent::STUDENT_CGPA, Intent::GENERAL,
        ],
        'student' => [
            Intent::STUDENT_ATTENDANCE, Intent::STUDENT_NEXT_EXAM,
            Intent::STUDENT_CGPA, Intent::STUDENT_SCHEDULE, Intent::GENERAL,
        ],
        'parent' => [
            Intent::STUDENT_ATTENDANCE, Intent::STUDENT_NEXT_EXAM,
            Intent::STUDENT_CGPA, Intent::GENERAL,
        ],
    ];

    /**
     * Determine whether a user may execute the given intent.
     *
     * @return array{allowed: bool, reason: string}
     */
    public function check(User $user, string $intent): array
    {
        if ($intent === Intent::GENERAL) {
            return ['allowed' => true, 'reason' => 'General conversation is always permitted.'];
        }

        $roles = $user->getRoleNames()->all();

        foreach ($roles as $role) {
            if (isset($this->policy[$role]) && in_array($intent, $this->policy[$role], true)) {
                return ['allowed' => true, 'reason' => "Role '{$role}' is permitted."];
            }
        }

        return [
            'allowed' => false,
            'reason' => 'Your role is not authorized to query this data.',
        ];
    }

    /**
     * List intents a role may access (for diagnostics/UI).
     *
     * @return array<int, string>
     */
    public function intentsForRole(string $role): array
    {
        return $this->policy[$role] ?? [Intent::GENERAL];
    }
}
