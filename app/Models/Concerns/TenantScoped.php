<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Institution;
use App\Services\Tenant\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TenantScoped trait.
 *
 * Automatically scopes Eloquent queries to the currently resolved
 * institution (tenant) and assigns the tenant id on creation. This is the
 * core of the shared-database, tenant-column multi-tenancy strategy used
 * across the platform to guarantee tenant isolation.
 */
trait TenantScoped
{
    /**
     * Boot the tenant scope for the model.
     */
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(TenantManager::class)->getCurrentTenantId();

            if ($tenantId !== null) {
                $builder->where($builder->getModel()->getTable().'.institution_id', $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            $tenantId = app(TenantManager::class)->getCurrentTenantId();

            if ($tenantId !== null && empty($model->getAttribute('institution_id'))) {
                $model->setAttribute('institution_id', $tenantId);
            }
        });
    }

    /**
     * Relationship to the owning institution.
     *
     * @return BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
