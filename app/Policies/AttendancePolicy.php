<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AttendancePolicy.
 */
class AttendancePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('attendance.manage');
    }
}
