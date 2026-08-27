<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * NoticePolicy.
 */
class NoticePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('notices.view');
    }

    public function view(User $user, Notice $notice): bool
    {
        return $user->can('notices.view');
    }

    public function create(User $user): bool
    {
        return $user->can('notices.manage');
    }

    public function update(User $user, Notice $notice): bool
    {
        return $user->can('notices.manage');
    }

    public function delete(User $user, Notice $notice): bool
    {
        return $user->can('notices.manage');
    }
}
