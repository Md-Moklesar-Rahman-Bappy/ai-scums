# 🔍 Enterprise Audit Report — AI-Powered IEMS

**Project:** AI-Powered Integrated Educational Management System (IEMS)
**Author / Maintainer:** Md Moklesar Rahman
**Audit Date:** 2026-08-27
**Auditor Role:** Principal Software Architect, Senior Laravel / Security / QA / DevOps Engineers
**Scope:** Full source review of `app/`, `resources/`, `routes/`, `config/`, `database/`, `tests/`. No code was modified during audit.

---

## 1. Executive Summary

AI-Powered IEMS is a **multi-tenant, role-aware educational ERP** built on **Laravel 11 / PHP 8.2** with a **read-only, RAG-based AI Academic Assistant** as its central research contribution. The codebase demonstrates a mature, layered architecture (Controllers → Services → Repositories → Models), clean SOLID separation, deterministic and auditable AI intent detection, and a genuinely safe "no write path" assistant design.

A focused, adversarial review surfaced **one Critical**, **several High**, and a number of Medium/Low findings. The single most important defect is that **`TenantManager` is not registered as a scoped/singleton binding**, which breaks super-admin institution switching and produces tenant-less writes for super admins. Regular-user isolation currently works only because of an `auth()->user()->institution_id` fallback inside `TenantManager`.

### Risk Posture (by phase)
| Phase | Domain | Highest Severity |
|------|--------|------------------|
| 1 | Architecture / Code Quality | Critical (tenant binding) |
| 2 | Laravel Best Practices | High (anemic repos, logic-in-controllers) |
| 3 | Database | High (fail-open tenant scope) |
| 4 | Security | Critical (seeded default creds) / High (no throttle, mass-assign) |
| 5 | AI Assistant | High (implicit tenant isolation, egress) |
| 6 | UI/UX | Critical (no mobile nav) / High (no field errors) |
| 7 | Bugs | High (broken assistant default, missing controller method) |
| 8 | Performance | Medium (N+1, sync LLM) |
| 9 | Testing | High (no test suite exists) |

### Readiness Verdict
**Architecture: Strong. Security baseline: Good. Operational readiness: Needs hardening.**
The system is suitable for demonstration and further development; before any production deployment it must address the Critical/High items below, particularly tenant isolation for super admins, default credentials, auth throttling, input field validation UX, and the missing test suite.

---

## 2. Methodology

- Static analysis of every PHP class, Blade view, migration, route and config file.
- `php -l` syntax verification across the codebase (0 errors).
- Adversarial security review (auth, RBAC, tenant isolation, CSRF, XSS, SQLi, secrets, AI endpoints).
- Architecture review against Laravel best practices, SOLID, DRY, Clean Architecture, Repository & Service patterns.
- Database review (FK, indexes, constraints, normalization, N+1).
- UI/UX review (Bootstrap 5.3, responsiveness, a11y) and bug detection (dead code, undefined vars, route conflicts).

Detailed findings are documented per-phase in:
`best-practices-report.md`, `database-review.md`, `security-report.md`, `ai-review.md`, `ui-ux-review.md`, `bug-report.md`, `performance-report.md`, `test-coverage-report.md`.

---

## 3. Severity Definitions

- **Critical** — Trivially exploitable or causes total isolation/availability failure.
- **High** — Serious weakness; privilege, tenant, or data-integrity impact.
- **Medium** — Conditional/latent; requires specific conditions or future code.
- **Low** — Hardening / hygiene / minor inconsistency.

---

## 4. Key Findings (consolidated)

### 4.1 Critical
1. **C-1 — `TenantManager` not bound as scoped/singleton** (`bootstrap/providers.php`, `app/Services/Tenant/TenantManager.php`). Super-admin switching is non-functional and super-admin writes produce `institution_id = NULL` (NOT NULL violation). Regular users are saved only by an `auth()->user()->institution_id` fallback. *Fix: `$app->scoped(TenantManager::class)` in a service provider.*
2. **C-2 — Seeded default super-admin/admin credentials** (`database/seeders/DatabaseSeeder.php:25-55`) use the literal `password` and are active. Anyone reaching the login page owns the platform. *Fix: gate seeding to local/test, randomize passwords, force reset.*

### 4.2 High
- **H-1** No rate limiting / lockout on auth endpoints (`routes/web.php`, `AuthController`). Brute-force exposure.
- **H-2** `User.$fillable` exposes `is_super_admin`, `institution_id`, `is_active` (`app/Models/User.php:38-41`) — privilege-escalation-capable if any future `User::create($validated)` is added.
- **H-3** Tenant global scope is **fail-open** when tenant resolves to `null` (`app/Models/Concerns/TenantScoped.php:28-34`).
- **H-4** AI admin tools contain **no explicit `institution_id` filter** (`AdminOutstandingFeesTool`, `AdminAdmissionStatsTool`, `AdminEnrollmentReportTool`) — isolation works only by accident of the global scope.
- **H-5** Any authenticated user can choose an **external LLM provider** (`AssistantAskRequest.php:29`), pushing tenant PII to third parties (compliance/egress).
- **H-6** No mobile navigation — sidebar is `d-none d-md-block` with no hamburger/offcanvas (`resources/views/layouts/app.blade.php:24`).
- **H-7** Assistant is unusable out-of-the-box: `mock` provider missing from the `in:` validation rule while being the configured default (`AssistantAskRequest.php:29` vs `config/ai.php:17`).
- **H-8** `InstitutionController` has no `show()` but `Route::resource` registers it → `BadMethodCallException` (`routes/web.php:48`).
- **H-9** No automated test suite exists (Phase 9).

### 4.3 Medium (representative)
- Anemic/inconsistently-used repositories; logic-in-controller (Dashboard, Attendance).
- Cross-tenant `exists:` validation rules (`StudentRequest`, `ExamMarkRequest`, `FeeRequest`, etc.).
- N+1 in AI tools and list repositories; synchronous blocking LLM call.
- `ai_feedback` table lacks `institution_id`; pivot tables lack tenant column.
- Prompt-injection via stored data; Gemini provider omits its API key; no real provider fallback.
- PII stored verbatim in `AiAuditLog`; session cookie not `Secure`.

Full enumeration in the phase reports.

---

## 5. Strengths (to preserve)

- Clean, conventional layered architecture with DTOs, Form Requests, Policies.
- Deterministic, explainable AI intent detection; strictly read-only assistant (no write path, no LLM executor) — jailbreak cannot mutate data.
- Solid RBAC (spatie + policies + `authorizeResource` + `assistant.use` gate).
- No SQL-injection or XSS surface (all Eloquent-bound, consistent `{{ }}` escaping).
- `institution_id` indexed on every tenant table; soft deletes everywhere; sensible FK cascades.
- AI provider abstraction (OpenAI/Claude/Gemini/Local/Mock) with offline `MockProvider`.

---

## 6. Prioritized Remediation Roadmap (see `remediation-plan.md`)

| Order | Item | Severity |
|------|------|----------|
| P0 | Bind `TenantManager` as scoped; force tenant in `creating` scope | Critical |
| P0 | Secure/remove seeded default credentials | Critical |
| P1 | Add auth throttling + account lockout | High |
| P1 | Tighten `User.$fillable`; force tenant assignment | High |
| P1 | Explicit `institution_id` in every AI tool | High |
| P1 | Remove end-user external provider selection | High |
| P1 | Add mobile nav; field-level validation UX | High |
| P1 | Fix assistant `mock` rule + `InstitutionController::show` | High |
| P2 | Add test suite (auth, tenant, role, assistant) | High |
| P2 | Cross-tenant `exists:` scoping; N+1 eager loading; indexes | Medium |

---

## 7. Conclusion

The IEMS codebase is **architecturally sound and well-suited to its thesis goal**, with a genuinely safe AI assistant design. The findings are concentrated in **tenant-isolation robustness for super admins**, **default-credential seeding**, **auth hardening**, **UI responsiveness**, and the **absence of automated tests**. None are architecturally fatal; all are addressable with the phased plan in `remediation-plan.md`. Once the P0/P1 items are resolved and a test suite is in place, the system can be considered production-candidate.

*Author: Md Moklesar Rahman — md.moklasarrahmanbappy@gmail.com — +8801965031371*
