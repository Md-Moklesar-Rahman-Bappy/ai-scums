<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * TenantController.
 *
 * Allows the platform super admin to switch the "active institution" context
 * stored in the session, which the ResolveTenant middleware then applies to
 * all tenant-scoped queries.
 */
class TenantController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    /**
     * Switch the active tenant (super admin only).
     */
    public function switch(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            abort(403);
        }

        $request->validate(['institution_id' => ['nullable', 'exists:institutions,id']]);

        $previous = $request->session()->get('active_institution_id');
        $next = $request->input('institution_id') ? (int) $request->input('institution_id') : null;

        $request->session()->put('active_institution_id', $next);

        $this->audit->log('tenant.switch', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => ['active_institution_id' => $previous],
            'new_values' => ['active_institution_id' => $next],
        ]);

        return redirect()->back();
    }
}
