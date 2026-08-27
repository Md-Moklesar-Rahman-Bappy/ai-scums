<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Institution;

/**
 * InstitutionRepository.
 *
 * Data access for {@see Institution} (the tenant root). Institutions are NOT
 * tenant-scoped: the global TenantScoped scope is intentionally absent so the
 * platform may list and manage all tenants.
 */
class InstitutionRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Institution::class;
    }
}
