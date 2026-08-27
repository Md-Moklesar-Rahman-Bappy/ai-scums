<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuditLogService.
 *
 * Centralised, tamper-evident security audit logging. Every security-relevant
 * event (authentication, password reset, email verification, role/permission
 * mutation, tenant switch, etc.) is recorded with a consistent payload:
 *
 *  - user_id        the actor (null for pre-auth events such as a failed login)
 *  - institution_id the actor's tenant at the time of the event
 *  - action         a stable, namespaced event key (e.g. auth.login.success)
 *  - ip_address     request source IP
 *  - user_agent     request User-Agent
 *  - created_at     event timestamp
 *
 * The {@see AuditLog} model omits soft deletes so the trail is permanent.
 */
class AuditLogService
{
    /**
     * Record a security event.
     *
     * @param  string  $action  Stable event key, namespaced by area.
     * @param  array<string, mixed>  $context  Extra structured context (optional).
     */
    public function log(string $action, array $context = [], ?Request $request = null): void
    {
        $request ??= request();

        $user = Auth::user();
        $institutionId = $user?->institution_id
            ?? (method_exists($user, 'isSuperAdmin') && $user?->isSuperAdmin() ? null : null);

        AuditLog::create([
            'user_id' => $user?->id,
            'institution_id' => $institutionId,
            'action' => $action,
            'model_type' => $context['model_type'] ?? null,
            'model_id' => $context['model_id'] ?? null,
            'old_values' => $context['old_values'] ?? null,
            'new_values' => $context['new_values'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
