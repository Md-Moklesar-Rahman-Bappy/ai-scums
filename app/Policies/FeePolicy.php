<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Fee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * FeePolicy.
 */
class FeePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('fees.view');
    }

    public function view(User $user, Fee $fee): bool
    {
        return $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fees.manage');
    }

    public function update(User $user, Fee $fee): bool
    {
        return $user->can('fees.manage');
    }

    public function delete(User $user, Fee $fee): bool
    {
        return $user->can('fees.manage');
    }

    public function pay(User $user, Fee $fee): bool
    {
        return $user->can('fees.manage');
    }
}
