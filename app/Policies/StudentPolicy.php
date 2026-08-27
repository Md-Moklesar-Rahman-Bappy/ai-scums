<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * StudentPolicy.
 *
 * Authorizes student record access. Institution admins and teachers manage
 * students within their tenant; students/parents see only their own record.
 */
class StudentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        // Students and parents must never obtain a listing of all students; they
        // may only view their own/linked record via the `view` ability. Listing
        // is restricted to staff roles that legitimately manage cohorts.
        if ($user->hasRole(['student', 'parent'])) {
            return false;
        }

        return $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        // Students and parents may only view their own / linked record. The
        // generic `students.view` permission is intentionally NOT sufficient for
        // these roles, otherwise a parent could read every student in the
        // tenant via the show route.
        if ($user->hasRole(['student', 'parent'])) {
            if ($user->student && $user->student->id === $student->id) {
                return true;
            }

            return $user->parent && $user->parent->students->contains($student);
        }

        return $user->can('students.view');
    }

    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->can('students.edit');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('students.delete');
    }

    /**
     * Promotion is an enrollment change available to admins.
     */
    public function promote(User $user, Student $student): bool
    {
        return $user->can('students.edit');
    }
}
