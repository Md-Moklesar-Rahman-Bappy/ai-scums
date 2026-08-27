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
        return $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->can('students.view')) {
            return true;
        }

        // Students/parents may view their own record.
        if ($user->student && $user->student->id === $student->id) {
            return true;
        }

        if ($user->parent && $user->parent->students->contains($student)) {
            return true;
        }

        return false;
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
