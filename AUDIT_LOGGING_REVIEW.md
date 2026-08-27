# AUDIT LOGGING REVIEW

**Phase:** 6 · **Status:** Implemented & Verified

---

## 1. Inventory of Existing Logs
| Model | Scope | Writable before audit | Now |
|-------|-------|-----------------------|-----|
| `AuditLog` | Generic security trail (no soft-deletes) | **Never written** | ✅ Written by `AuditLogService` |
| `AiAuditLog` | AI assistant interactions | ✅ (AssistantService) | ✅ Retained + reviewed |

The core gap: `AuditLog` existed but had **zero** call sites. This violated
OWASP ASVS V7 (logging of security events).

## 2. Standardised Event Schema — `app/Services/Audit/AuditLogService.php`
Every entry records:
- `user_id` — actor (null for pre-auth events like failed login)
- `institution_id` — actor's tenant at event time
- `action` — stable, namespaced key
- `ip_address` — `$request->ip()`
- `user_agent` — `$request->userAgent()`
- `created_at` — auto timestamp

Optional: `model_type`, `model_id`, `old_values`, `new_values` (JSON).

## 3. Events Now Logged
| Event | Action key | Source |
|-------|-----------|--------|
| Login success | `auth.login.success` | AuthController |
| Login failure | `auth.login.failure` | AuthController |
| Brute-force lockout | `auth.login.lockout` | AuthController |
| Logout | `auth.logout` | AuthController |
| Self-registration | `auth.register` | RegistrationService |
| Password reset requested | `auth.password.reset_request` | AuthController |
| Password reset success | `auth.password.reset_success` | AuthController |
| Email verified | `auth.email.verified` | AuthController |
| Verification resent | `auth.email.verification_resent` | AuthController |
| Role assigned | `rbac.role_assigned` | RegistrationService |
| Tenant created | `tenant.created` | RegistrationService |
| Tenant switched | `tenant.switch` | TenantController |

## 4. Events Recommended for Future Instrumentation
(Role/permission *changes* beyond initial assignment, e.g. admin mutating
another user's roles — currently there is no such UI; when added, call
`AuditLogService::log('rbac.permission_changed', …)`). All student/teacher/fee
mutations should also emit `model_type`/`model_id` for traceability.

## 5. Integrity & Retention
- `AuditLog` intentionally omits `SoftDeletes` → permanent trail.
- Store on a separate, append-optimised connection if compliance requires WORM.
- Forward to SIEM via Laravel log channel or a queued listener.

## 6. Testing Checklist
- [ ] Failed login row contains correct IP/UA and user id when account exists.
- [ ] `tenant.switch` records old/new `active_institution_id`.
- [ ] `rbac.role_assigned` records the granted role in `new_values`.
- [ ] No audit row is ever soft-deleted.
