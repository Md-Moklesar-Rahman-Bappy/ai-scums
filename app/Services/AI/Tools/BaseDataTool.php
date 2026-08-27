<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Models\Student;
use App\Models\User;
use App\Services\Tenant\TenantManager;

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
     * Resolve the active tenant (institution) for the current request.
     *
     * Returns the switched tenant for super admins, or the user's institution
     * otherwise. Admin tools MUST scope every query by this id to guarantee
     * tenant isolation; a null result means "no tenant in scope" and the tool
     * must return a safe empty result rather than leaking cross-tenant data.
     */
    protected function tenantId(): ?int
    {
        return app(TenantManager::class)->getCurrentTenantId();
    }

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
