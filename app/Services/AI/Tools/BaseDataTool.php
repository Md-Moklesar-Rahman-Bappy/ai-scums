<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Models\Student;
use App\Models\User;

/**
 * BaseDataTool.
 *
 * Shared helper for student-scoped tools: resolves the {@see Student} record a
 * user is allowed to query (their own profile, or - for parents - their first
 * linked child). Centralises the tenant-scoped student resolution so every
 * tool respects isolation rules.
 */
abstract class BaseDataTool
{
    /**
     * Resolve the student a user is querying for.
     *
     * Students see their own record; parents see their first child. Teachers
     * and admins return null here and must scope queries themselves.
     */
    protected function resolveStudentFor(User $user): ?Student
    {
        if ($user->student) {
            return $user->student;
        }

        if ($user->parent) {
            return $user->parent->students()->first();
        }

        return null;
    }
}
