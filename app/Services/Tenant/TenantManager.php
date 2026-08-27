<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Concerns\TenantScoped;
use App\Models\User;

/**
 * TenantManager.
 *
 * Resolves and stores the "active institution" (tenant) for the current
 * request. Super admins may switch between institutions; regular users are
 * bound to a single institution. This is the single source of truth used by
 * the {@see TenantScoped} global scope.
 */
class TenantManager
{
    /**
     * The currently resolved tenant id (in-memory for the request).
     */
    private ?int $currentTenantId = null;

    /**
     * Resolve the active tenant id.
     *
     * Resolution order:
     *   1. Explicitly set value (switch action)
     *   2. Authenticated user's institution (non super-admin)
     *
     * Returns null when no tenant can be resolved (e.g. login screen).
     */
    public function getCurrentTenantId(): ?int
    {
        if ($this->currentTenantId !== null) {
            return $this->currentTenantId;
        }

        /** @var User|null $user */
        $user = auth()->user();

        if ($user && $user->institution_id) {
            return $user->institution_id;
        }

        return null;
    }

    /**
     * Explicitly set the active tenant for the current request.
     */
    public function setCurrentTenantId(?int $tenantId): void
    {
        $this->currentTenantId = $tenantId;
    }

    /**
     * Determine whether a tenant is currently active.
     */
    public function hasTenant(): bool
    {
        return $this->getCurrentTenantId() !== null;
    }
}
