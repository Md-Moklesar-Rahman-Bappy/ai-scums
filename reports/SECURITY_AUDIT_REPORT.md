# Security Audit Report — AI-SCUMS

**Date:** 2026-08-27
**Scope:** Authentication, Session, Rate Limiting, Password Policy, Email Verification, CSRF, XSS, SQLi, Mass Assignment, API Security, Tenant Security, AI Security
**Verdict:** Strong baseline (B+). No Critical/High exploitable issues found in code; two High-severity *governance/abuse* gaps identified.

---

## Severity Summary

| Severity | Count | Items |
|----------|-------|-------|
| Critical | 0 | — |
| High | 2 | S1 (AI rate limiting / cost abuse), S2 (AI third-party data egress) |
| Medium | 5 | S3 (duplicate assistant routes), S4 (no API token layer), S5 (super-admin cross-tenant AI context), S6 (`User.$fillable` `is_super_admin`/`is_active`), S7 (session cookie hardening not verified) |
| Low | 3 | S8 (logout CSRF on GET-less POST ok), S9 (inline CDN assets = supply-chain surface), S10 (missing security headers) |

---

## 1. Authentication — PASS
- Email verification enforced on all app routes via `verified` middleware (routes/web.php:71).
- `User` implements `MustVerifyEmail` (app/Models/User.php:30).
- Session regenerated on login (AuthController.php:89) and fully invalidated on logout (AuthController.php:135-136).

## 2. Session Handling — PASS (minor note S7)
- `Auth::attempt` + `session()->regenerate()` on login; `invalidate()` + `regenerateToken()` on logout.
- Remember-me token rotated every login (AuthController.php:96-99) — good hardening.
- **S7 (Low):** `config/session.php` should be confirmed to set `http_only=true`, `same_site='lax'`, `secure=cookie` in production. Verify `.env.example` documents `SESSION_SECURE_COOKIE`.

## 3. Rate Limiting — PASS (with S1)
- Login throttled by named limiter `login` (routes/web.php:24-28) AND a custom dual-bucket lockout in `AuthController::login` (AuthController.php:53-69). Strong.
- Verification resend throttled (`verification` limiter).
- **S1 (High — abuse/cost):** `assistant.ask` / `assistant.ask-legacy` have **no rate limit**. A user could loop requests and (a) incur LLM cost, (b) exhaust context. Add `throttle:` middleware or a per-user AiAuditLog counter. Recommend `throttle:assistant` limiter (e.g. 20/min).

## 4. Password Policy — PASS
- Fully wired in AppServiceProvider.php:36-51: min 12, mixedCase, numbers, symbols, `uncompromised()` (HIBP breach check).
- Applied in `RegisterRequest` and password reset (AuthController.php:208).

## 5. Email Verification — PASS
- Signed verification route (`signed` middleware, web.php:60), resend throttled, audit-logged.

## 6. CSRF — PASS
- `VerifyCsrfToken` is part of the default `web` group; all forms use `@csrf`; Axios configured with `X-CSRF-TOKEN` (app.blade.php:80-81).
- `tenant.switch` and `assistant.ask` are POST with token.

## 7. XSS — PASS (no sink found)
- Grep for `{!!` returned **0 matches** in app code. Blade auto-escaping (`{{ }}`) is used throughout including user-derived data (e.g. dashboard, chat bubbles use `textContent`, not HTML injection).
- AI responses are rendered via `addBubble` using `textContent` (assistant/index.blade.php:36) — safe from HTML injection.

## 8. SQL Injection — PASS
- Grep for `whereRaw`/`DB::raw`/`selectRaw` found only `selectRaw('...')` with **hard-coded column names** (AdminEnrollmentReportTool.php:39,46; AdminAdmissionStatsTool.php:40) — no user input concatenated. All other queries use Eloquent parameter binding / `exists:` validation.
- `TenantScoped` builds `where` with `$builder->getModel()->getTable().'.institution_id'` — table name is model-derived, not user input. Safe.

## 9. Mass Assignment — PASS (note S6)
- Models use explicit `$fillable`. `Student`/`Fee`/etc. are written via `repository->create($validated)` where `$validated` comes from FormRequests whose `rules()` **whitelist** fields — `institution_id` is never in the request rules, so a tenant cannot be injected (TenantScoped sets it on create).
- **S6 (Medium):** `User::$fillable` (User.php:39-42) includes `is_super_admin` and `is_active`. Today no code path lets an end user set them (registration is hard-coded in RegistrationService.php:45-52 and never passes `is_super_admin`). However, this is fragile: any future `User::create($request->validated())` would grant privilege escalation. **Recommend** removing `is_super_admin` and `is_active` from `$fillable` (or guarding them behind an explicit, policy-checked mutator) and relying on `forceFill`/explicit assignment in admin contexts.

## 10. API Security — NOTE (S4)
- No token/API auth layer exists (web-only app). The service layer is "API-ready" but there is currently no Sanctum surface, so no API attack surface. **S4 (Medium):** before shipping the planned REST API (README roadmap), ensure Sanctum tokens are tenant-scoped and rate-limited; do not expose `assistant.ask` without auth + throttling.

## 11. Tenant Security — PASS (see TENANT_ISOLATION_REPORT.md)
- Shared-DB, `institution_id` column + `TenantScoped` global scope on `BaseModel`.
- Super-admin tenant switch is gated (`TenantController::switch` aborts 403 for non-super-admins, validates `exists:institutions,id`).
- **S5 (Medium):** When a super-admin selects "All institutions" (null tenant), AI tools correctly short-circuit (BaseDataTool::tenantId null → safe empty). Verified in AdminOutstandingFeesTool.php:34-36. No cross-tenant leak observed. Document this behaviour explicitly.

## 12. AI Security — SEE AI_ASSISTANT_SECURITY_REVIEW.md (S2 High)
- **S2 (High — privacy/compliance):** With `AI_PROVIDER=openai|claude|gemini`, the assistant sends retrieved tenant data (student names, attendance, fee amounts) to a third-party LLM (`buildMessages` → `json_encode($context['data'])` in AssistantService.php:114-116). For super-admins the provider is user-selectable; for others the *configured default* still applies. This is a **data-egress / compliance** risk (FERPA/GDPR). Mitigations: keep `mock`/`local` as default, add a per-tenant "allow external LLM" flag, redact direct identifiers before egress, and document the posture.
- Read-only enforcement is solid (tools are SELECT-only; AuthorizationGate rejects mutation intents; no tool mutates). See dedicated report.

---

## Remediation Plan (priority order)

| ID | Severity | Action | Effort |
|----|----------|--------|--------|
| S1 | High | Add `throttle:assistant` to `assistant.ask` / `ask-legacy` | Low |
| S2 | High | Default to `mock`/`local`; add tenant LLM-egress policy + PII redaction + docs | Med |
| S3 | Medium | Remove duplicate assistant routes (web.php:84-87) | Low |
| S4 | Medium | Design Sanctum API with tenant scoping + throttling before release | High |
| S5 | Medium | Document super-admin "All institutions" AI behaviour | Low |
| S6 | Medium | Remove `is_super_admin`/`is_active` from `User.$fillable` | Low |
| S7 | Low | Verify/harden session cookie flags in prod `.env` | Low |
| S9 | Low | Vendor assets via build step instead of runtime CDN (supply-chain) | Med |
| S10 | Low | Add security headers (CSP, X-Frame-Options, HSTS) via middleware | Low |

**Conclusion:** No Critical/High *code* vulnerabilities. The two High items are **governance/abuse** controls (AI throttling, AI data egress) that should be addressed before any production or multi-tenant-public launch.
