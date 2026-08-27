<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * RoutinePolicy.
 */
class RoutinePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('routines.view');
    }

    public function view(User $user, Routine $routine): bool
    {
        return $user->can('routines.view');
    }

    public function create(User $user): bool
    {
        return $user->can('routines.manage');
    }

    public function update(User $user, Routine $routine): bool
    {
        return $user->can('routines.manage');
    }

    public function delete(User $user, Routine $routine): bool
    {
        return $user->can('routines.manage');
    }
}
