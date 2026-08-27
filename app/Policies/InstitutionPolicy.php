<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * InstitutionPolicy.
 *
 * Authorizes institution management. Only the platform super admin may manage
 * tenants; institution admins operate inside their own tenant and cannot
 * manage the tenant record itself.
 */
class InstitutionPolicy
{
    use HandlesAuthorization;

    /**
     * Super admin may do anything to institutions.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Institution $institution): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Institution $institution): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Institution $institution): bool
    {
        return $user->isSuperAdmin();
    }
}
