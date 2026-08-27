# 🔒 Security Audit — AI-Powered IEMS

**Phase 4** · Adversarial review of Authentication, Authorization, Sessions, CSRF, XSS, SQLi, Uploads, Secrets, AI Endpoints, Tenant Isolation, RBAC.
**Stack:** Laravel 11.31, spatie/laravel-permission ^6.25, custom session auth, shared-DB/tenant-column isolation.

---

## 1. Severity Legend
- **Critical** — trivially exploitable, full compromise.
- **High** — exploitable / serious weakness, privilege or tenant impact.
- **Medium** — conditional/latent.
- **Low** — hardening/hygiene.

---

## 2. Critical

### F-1 — Default seeded super-admin & admin credentials — **Critical**
`database/seeders/DatabaseSeeder.php:25-34,46-55` create `superadmin@iems.test`/`password` (cross-tenant super admin) and `admin@demo.test`/`password`, both active.
**Impact:** Anyone reaching the login page and guessing these owns the platform & every tenant.
**Fix:** Never seed usable creds in shippable seeders. Gate to `environment('local')`, randomize passwords, force reset on first login, or provision via env at deploy. Add CI check failing on `password` literal in seeders.

---

## 3. High

### F-2 — No rate limiting / lockout on auth — **High**
`routes/web.php:22-31` (guest group, no `throttle`); `bootstrap/app.php` only appends `ResolveTenant`; `AuthController.php:40,108,130`. Login/register/forgot/reset unthrottled; no lockout. `config/auth.php` only throttles reset-token generation.
**Impact:** Brute force / credential stuffing; reset-token flooding.
**Fix:** `throttle:10,1` on login, `throttle:6,1` on register/forgot/reset; implement failed-attempt lockout.

### F-4 — `User.$fillable` exposes escalation keys — **High (latent)**
`app/Models/User.php:38-41` includes `is_super_admin`, `institution_id`, `is_active`. No endpoint currently does `User::create/update($request->validated())` with these, but the design is a loaded gun: any future `User::update($request->all())` lets an attacker set `is_super_admin=1` or flip `institution_id`/`is_active`.
**Fix:** Remove those keys from `$fillable`; set only via explicit `Gate`-guarded code. Add a regression test.

---

## 4. Medium

### F-6 — "null tenant = all data" escape for non-super users — **Medium (latent)**
`TenantScoped.php:31-34`; `ResolveTenant.php:37-42`. Scope applies only when `tenantId !== null`. A non-super user with null `institution_id` (bad import/seed) gets unrestricted cross-tenant access.
**Fix:** Enforce `institution_id NOT NULL` for non-super users; treat `null` tenant strictly as super-admin-only.

### F-7 — Tenant write-bypass via `institution_id` in `$fillable` — **Medium**
`TenantScoped.php:36-43` sets `institution_id` only `if empty`. If a Request ever validates `institution_id`, the repo honors attacker value → record stored under another tenant. Not reachable today (Requests omit it) but fragile.
**Fix:** In `creating`, **force** `institution_id = currentTenant`; drop `institution_id` from `$fillable`.

### F-9 — Indirect prompt injection via stored data + Gemini drops system role — **Medium**
`AssistantService.php:113-116` concatenates retrieved data into the prompt. Notice `body`, names are attacker-influenceable. `GeminiProvider.php:37-40` strips the `system` role, so the "read-only" instruction isn't even sent to Gemini — the guarantee rests solely on tools being non-mutating.
**Fix:** Delimit/escape data vs instructions; sanitize retrieved text; add output guards.

### F-10 — Student PII egress to third-party LLMs — **Medium**
Any user can select `openai/claude/gemini` (`AssistantAskRequest.php:29`), pushing tenant-scoped PII (names, marks, attendance, fees) to Google/OpenAI/Anthropic. Compliance (FERPA/GDPR) exposure.
**Fix:** Restrict prod to `local`/self-hosted; minimize fields; require DPAs/consent; hide cloud providers from UI in prod.

### F-13 — Session cookie not `Secure`; sessions unencrypted — **Medium**
`config/session.php:50` (`encrypt=false` default), `:172` (`secure=null`). On HTTPS prod, cookie lacks `Secure` flag; payload unencrypted at rest.
**Fix:** `SESSION_SECURE_COOKIE=true`, consider `SESSION_ENCRYPT=true`; enforce HTTPS/HSTS.

### F-8 — Secrets/env hygiene — **Medium**
Ensure prod: `APP_DEBUG=false`, strong `APP_KEY`, `SESSION_SECURE_COOKIE`. Provider keys read from env via config (good); default `AI_PROVIDER=mock` (good).

---

## 5. Low
- **F-3** Email verification disabled (`User.php:5` commented). Enable `MustVerifyEmail` if policy requires.
- **F-5** Super-admin identity split: policies' `before()` check `is_super_admin` boolean; `AuthorizationGate` checks `super_admin` spatie role. Divergent if only one set. Use a single source of truth.
- **F-11** `askLegacy` dead path; `mock` not in provider allow-list.
- **F-12** Latent upload risk: `avatar`/`logo` in `$fillable` with no validation if upload feature added later (define disk, validate MIME/size, store outside webroot).
- **F-9b** Password-reset email enumeration via `sendResetLink`.

---

## 6. What is already STRONG (keep)
1. **No SQL-injection surface** — zero raw `DB::`/`whereRaw`/`withoutGlobalScope`; all Eloquent-bound.
2. **No XSS** — grep `{!!` returns zero; consistent `{{ }}`; assistant uses `textContent`.
3. **Solid tenant isolation baseline** — global scope on all `BaseModel`s, enforced on route-model binding, dashboard & AI tools.
4. **Layered RBAC** — spatie + policies + `authorizeResource` + `assistant.use` gate + `before()` super-admin.
5. **Assistant genuinely read-only & audit-logged**; deterministic intent detection.
6. **Correct password hashing** (`hashed` cast + `Hash::isHashed` prevents double-hash); secure session flags (`http_only`, `same_site`); fixation-safe `regenerate()`/`invalidate()`; reset expiry/throttle.
7. **Secrets not committed**; provider keys env-based; safe `mock` default.

---

## 7. Prioritized Remediation
| # | Sev | Issue | File(s) |
|---|-----|-------|---------|
| F-1 | Critical | Seeded default creds | `DatabaseSeeder.php` |
| F-2 | High | No auth throttle/lockout | `routes/web.php`, `AuthController` |
| F-4 | High | `User.$fillable` escalation keys | `User.php` |
| F-6 | Med | Null-tenant escape | `TenantScoped.php`, `ResolveTenant.php` |
| F-7 | Med | Tenant write-bypass | `TenantScoped.php`, fillables |
| F-9 | Med | Prompt injection / Gemini system-drop | `AssistantService.php`, `GeminiProvider.php` |
| F-10 | Med | PII egress to LLMs | providers, `AIProviderManager` |
| F-13 | Med | Session Secure/Encrypt | `config/session.php` |
| F-3/5/11/12 | Low | Verify email, identity split, dead path, upload | various |
