<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * TeacherPolicy.
 */
class TeacherPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('teachers.view');
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('teachers.create');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.edit');
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.delete');
    }
}
