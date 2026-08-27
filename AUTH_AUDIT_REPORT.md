# AUTH AUDIT REPORT

**System:** AI-Powered Integrated Educational Management System (IEMS)
**Stack:** Laravel 11 · PHP 8.2 · MySQL · Spatie Laravel Permission · Multi-Tenant
**Auditor Role:** Principal Laravel Security Engineer / Senior Authentication Architect
**Date:** 2026-08-27
**Standard:** OWASP ASVS v4.0.3, Laravel Security Best Practices, Multi-Tenant SaaS Hardening

---

## 1. Executive Summary

The authentication and RBAC foundation is structurally sound: a thin `AuthController`,
dedicated `LoginRequest`/`RegisterRequest`, a transactional `RegistrationService`,
Spatie role/permission seeding, and a tenant-column global scope (`TenantScoped`).
However, the implementation is **not yet production-ready** because several
defence-in-depth controls expected of an enterprise SaaS are missing or weak.

| Severity | Count | Headline issues |
|----------|-------|-----------------|
| Critical | 2 | No email verification; Parent role can enumerate/list all students |
| High | 5 | No login rate limiting; no auth audit trail; demo seeders in prod; `verified` middleware absent on modules; password policy too weak |
| Medium | 4 | Session not encrypted; no secure-cookie/httponly enforcement; reset/register not throttled; super-admin null-tenant write gap |
| Low | 3 | `remember_token` not rotated on login; `APP_DEBUG=true` in `.env`; no `last_login_ip` capture |

**Overall verdict:** Remediate all Critical and High findings before any production exposure.

---

## 2. Scope Audited

| Component | File | Status |
|-----------|------|--------|
| Auth Controller | `app/Http/Controllers/Auth/AuthController.php` | Reviewed + Hardened |
| Login Request | `app/Http/Requests/Auth/LoginRequest.php` | Reviewed (throttle moved to routes/controller) |
| Register Request | `app/Http/Requests/Auth/RegisterRequest.php` | Reviewed (password policy upgraded) |
| Registration Service | `app/Services/Auth/RegistrationService.php` | Reviewed + Hardened (audit) |
| User Model | `app/Models/User.php` | Reviewed + Hardened (`MustVerifyEmail`) |
| Seeders | `database/seeders/*` | Reviewed + Hardened (env-gated) |
| Middleware | `app/Http/Middleware/ResolveTenant.php`, `bootstrap/app.php` | Reviewed + Hardened (rate limiters) |
| Session | `config/session.php`, `config/security.php` | Reviewed + Hardened (config) |
| RBAC | `app/Policies/*`, `database/seeders/RolesAndPermissionsSeeder.php` | Reviewed + Hardened (StudentPolicy) |
| Tenant | `app/Models/Concerns/TenantScoped.php`, `app/Services/Tenant/TenantManager.php` | Reviewed |

---

## 3. Findings

### CRITICAL

#### C-1 — No Email Verification (Phases 2 / 11)
`User.php` imports `MustVerifyEmail` are commented out; the `verified` middleware is
**not applied to any protected route**. A freshly registered (or attacker-registered)
account can immediately reach every ERP module. This violates OWASP ASVS V2.1 and
enables abusive/throwaway accounts, data-pollution, and unverified billing contacts.

**Fix applied:** `User` now implements `MustVerifyEmail`; `verification.verify`,
`verification.notice`, `verification.send` routes added; `verified` middleware wraps
the entire authenticated module group. See `EMAIL_VERIFICATION_IMPLEMENTATION.md`.

#### C-2 — Parent/Student RBAC Escalation via `students.view` (Phases 7 / 8)
`StudentPolicy::viewAny()` returned `$user->can('students.view')`. The `parent` role
is **granted `students.view`**, therefore any parent could open `/students` and obtain
a listing of *every* student in the tenant (names, admission numbers, guardians,
contact details). `StudentPolicy::view()` had the same defect — a blanket
`students.view` check returned `true` for any parent, allowing `show` on any tenant
student.

**Fix applied:** `viewAny()` returns `false` for `student`/`parent` roles; `view()`
only permits own/linked records for those roles and no longer trusts `students.view`
for them. See `PARENT_ACCESS_REVIEW.md` and `RBAC_SECURITY_REPORT.md`.

### HIGH

#### H-1 — No Brute-Force / Login Rate Limiting (Phase 3)
The login route had **no `throttle` and no attempt counter**. Attackers could perform
unbounded credential stuffing. `config/auth.php` `throttle => 60` only governs the
password-reset token generator, not login.

**Fix applied:** IP+email `RateLimiter` in `AuthController::login` (5 attempts / 60s,
configurable in `config/security.php`); named `login` and `verification` limiters in
`bootstrap/app.php`; `throttle:login` on the public auth surface. See
`RATE_LIMITING_DOCUMENTATION.md`.

#### H-2 — No Authentication Audit Trail (Phase 6)
The `AuditLog` model existed but was **never written to**. No record of login success,
login failure, logout, password reset, email verification, role/permission changes,
or tenant switches existed — a direct failure of OWASP ASVS V7 (Logging).

**Fix applied:** `AuditLogService` created; wired into `AuthController`,
`RegistrationService`, and `TenantController`; every event records `user_id`,
`institution_id`, `action`, `ip_address`, `user_agent`, `created_at`. See
`AUDIT_LOGGING_REVIEW.md`.

#### H-3 — Production-Unsafe Seeders (Phase 5)
`DatabaseSeeder` unconditionally created `superadmin@iems.test` and `admin@demo.test`
with the literal password `password`. Shipping this to production is a critical
credential-exposure risk.

**Fix applied:** Demo accounts/data are created **only** when
`app()->environment('local','staging')`; production seeds RBAC only. Passwords are now
random 24-char strings (secrets via `DEMO_*_PASSWORD` env) and printed once in local.
See `PRODUCTION_SEEDER_POLICY.md`.

#### H-4 — `verified` Middleware Absent on Modules (Phase 2)
See C-1. Fixed by wrapping the authenticated group.

#### H-5 — Weak Password Policy (Phase 10)
`Password::defaults()` resolved to Laravel's built-in default (≥8 chars, no
complexity). No mixed-case/number/symbol/uncompromised enforcement.

**Fix applied:** `AppServiceProvider::boot()` defines `Password::defaults()` as
`min(12)->mixedCase()->numbers()->symbols()->uncompromised()`, tunable via
`config/security.php`. See `PASSWORD_POLICY.md`.

### MEDIUM

#### M-1 — Session Data Not Encrypted
`SESSION_ENCRYPT=false`. Session payloads (including some CSRF/flash) are stored
unencrypted in the `sessions` table.

**Fix / recommendation:** Set `SESSION_ENCRYPT=true` in production (documented in
`SESSION_SECURITY_REPORT.md` and `.env.example` guidance).

#### M-2 — Secure-Cookie / SameSite Not Enforced
`SESSION_SECURE_COOKIE` is unset (cookies sent over plain HTTP) and `SESSION_SAME_SITE`
is `lax` (acceptable) but `secure` must be `true` behind TLS.

**Recommendation:** `SESSION_SECURE_COOKIE=true`, keep `http_only=true`.

#### M-3 — Registration & Password-Reset Not Throttled
Public `register` and `forgot-password` endpoints were unthrottled (account/email
bombing, resource exhaustion).

**Fix applied:** Both are inside the `throttle:login` group.

#### M-4 — Super-Admin Null-Tenant Write Gap
A super admin with `active_institution_id = null` who creates tenant-scoped records
would persist them with `institution_id = null`, producing orphan/cross-tenant rows.

**Recommendation:** Block mutating tenant-scoped writes when the resolved tenant is
null for non-super-admins; for super-admins require an explicit active institution
before write (recommendation logged in `TENANT_SECURITY_REPORT.md`).

### LOW

#### L-1 — `remember_token` Not Rotated on Login
A stolen "remember me" cookie could be replayed after logout.

**Fix applied:** Token rotated on successful login and on password reset.

#### L-2 — `APP_DEBUG=true` in `.env`
Leaks stack traces / config in production if copied.

**Recommendation:** `APP_DEBUG=false` and `APP_ENV=production` in deployed env.

#### L-3 — No `last_login_ip` Captured
Only `last_login_at` was stored. Useful for anomaly detection.

**Recommendation:** Extend `users` with `last_login_ip`; capture in `AuthController`.

---

## 4. Controls Verified as Adequate

- **Session fixation:** `regenerate()` on login and `invalidate()`+`regenerateToken()`
  on logout are present and correct.
- **Tenant isolation:** All domain models extend `BaseModel` → `TenantScoped`;
  route-model binding applies the global scope, so `Student::find($otherTenantId)`
  returns `null`.
- **Password hashing:** `bcrypt` with `BCRYPT_ROUNDS=12` and `'hashed'` cast.
- **RBAC foundation:** Spatie roles/permissions seeded with least-privilege; policies
  use `before()` super-admin short-circuit (fail-closed for others).
- **AI assistant:** Read-only, intent→role authorization gate, and per-request audit
  via `AiAuditLog`.

---

## 5. Remediation Priority

1. **Immediate (block release):** C-1, C-2, H-1, H-2, H-3, H-5.
2. **Before GA:** M-1, M-2, M-4, L-1.
3. **Hardening:** M-3, L-2, L-3, plus periodic penetration testing.

---

## 6. Companion Documents

| Document | Phase |
|----------|-------|
| EMAIL_VERIFICATION_IMPLEMENTATION.md | 2 |
| RATE_LIMITING_DOCUMENTATION.md | 3 |
| SESSION_SECURITY_REPORT.md | 4 |
| PRODUCTION_SEEDER_POLICY.md | 5 |
| AUDIT_LOGGING_REVIEW.md | 6 |
| RBAC_SECURITY_REPORT.md | 7 |
| PARENT_ACCESS_REVIEW.md | 8 |
| TENANT_SECURITY_REPORT.md | 9 |
| PASSWORD_POLICY.md | 10 |
| SECURITY.md | 11 |
