<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Concerns\TenantScoped;
use App\Models\User;
use App\Services\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveTenant.
 *
 * Establishes the active tenant (institution) for the current request so the
 * {@see TenantScoped} global scope can isolate data.
 *
 *  - Super admins: tenant taken from the "active_institution_id" session key
 *    (enables institution switching); null means "all institutions".
 *  - Regular users: tenant is the user's own institution (cannot be changed).
 */
class ResolveTenant
{
    public function __construct(private readonly TenantManager $tenantManager) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            if ($user->isSuperAdmin()) {
                $switch = $request->session()->get('active_institution_id');
                $this->tenantManager->setCurrentTenantId($switch ? (int) $switch : null);
            } else {
                $this->tenantManager->setCurrentTenantId($user->institution_id);
            }
        }

        return $next($request);
    }
}
