# 🛠️ Phased Remediation & Improvement Plan — AI-Powered IEMS

**Purpose:** Convert the audit findings into an actionable, prioritized plan. **No code has been modified during the audit.** This plan is the agreed sequence for the implementation phase that follows.

---

## P0 — Critical (must fix before any deployment)
| ID | Finding | Files | Action |
|----|---------|-------|--------|
| C-1 | `TenantManager` not scoped/singleton → broken super-admin switching & null-tenant writes | `bootstrap/providers.php`, `app/Services/Tenant/TenantManager.php` | Bind `$app->scoped(TenantManager::class)`; force tenant stamping in `creating` scope. |
| C-2 | Seeded default super-admin/admin credentials (`password`) | `database/seeders/DatabaseSeeder.php` | Gate seeder to local/test; randomize passwords; force reset; CI check for `password` literal. |

## P1 — High (pre-production hardening)
| ID | Finding | Action |
|----|---------|--------|
| H-1 | No auth rate limiting / lockout | Add `throttle` to auth routes; failed-attempt lockout. |
| H-2 | `User.$fillable` exposes `is_super_admin`/`institution_id`/`is_active` | Remove from `$fillable`; set via Gate-guarded code; regression test. |
| H-3 | Tenant scope fail-open on null | Fail-closed: throw when tenant required but unresolved. |
| H-4 | AI admin tools lack explicit `institution_id` | Add `where('institution_id', $user->institution_id)` in every tool. |
| H-5 | User-selectable external LLM provider → PII egress | Remove per-request override; admin-configured allowlist; default `mock`. |
| H-6 | No mobile navigation | Add Bootstrap 5.3 offcanvas mirroring sidebar with `@role`/`@can`. |
| H-7 | Assistant unusable OOTB (`mock` rejected by validation) | Add `mock` to `in:` rule; default-select active provider. |
| H-8 | `InstitutionController::show()` missing but routed | Add `show()`+view or `->except(['show'])`. |
| H-9 | No automated test suite | Stand up isolated test DB + Tenant/Auth/AI tests (see test-coverage-report). |

## P2 — Medium (quality & scale)
- Rationalize repository layer (anemic/dead deps) + move controller query logic into services. BP-2/3/5/6.
- Scope `exists:` validation rules tenant-aware. BP-11.
- Eager-load relationships in list repositories; fix AI-tool N+1. DB-15, P-1/P-2.
- Queue the LLM call + throttle `/assistant/ask`. BP-15/16, P-3/P-4.
- Add `institution_id` to `ai_feedback`, AI/audit tables (via scope), and pivots. DB-3/4/5.
- `subjects`/`students`/`exams`/`routines` discriminator for hierarchy FKs. DB-10/11.
- Enforce single `academic_years.is_current`; model events for `grade`/`fee.status`. DB-12/13/14.
- Harden session (`SESSION_SECURE_COOKIE`, `SESSION_ENCRYPT`); prompt-injection delimiters; Gemini API key; real provider fallback; AiAuditLog retention/pseudonymization. F-13, A-10/A-13/A-15/A-17.
- Add missing non-FK indexes. DB-16 + table.

## P3 — Low (hygiene & polish)
- Remove dead imports (MockProvider, policy imports), unused `InstitutionRequest.$id`, `RoutineService::all()`, `AIProviderInterface::complete()`.
- `User` model `declare(strict_types=1)` + `isSuperAdmin(): bool`.
- Field-level validation feedback in all forms (UX-2); attendance section filter (UX-3); calendar titles & chart a11y (UX-4/6); label `for`/`id` (UX-7).
- Decide asset strategy (adopt Vite or remove dead vite/tailwind/postcss assets) (UX-9).
- Custom exceptions + targeted logging for authz denials / tenant violations. BP-26.
- Introduce domain Events/Jobs (student admitted, fee paid, attendance marked) for notifications/audit. BP-27.

---

## Suggested Sequencing (sprints)
- **Sprint 1 (P0):** Tenant binding + seeded-credential fix. Unblocks correct multi-tenant behavior & safe demo.
- **Sprint 2 (P1):** Auth hardening, mass-assignment tightening, AI tool tenant filters, provider egress control, UI mobile nav + validation, assistant `mock` rule, `InstitutionController::show`, and the initial test suite (Tenant/Auth/AI).
- **Sprint 3 (P2):** Performance (eager load, queue LLM, indexes, cache), DB hardening (tenant cols, discriminator, single current year, model events), AI robustness (injection delimiters, Gemini key, fallback, audit retention).
- **Sprint 4 (P3):** Hygiene, a11y, assets, events, docs finalization.

---

## Verification Gates (post-fix)
- `php artisan test` green (tenant isolation, auth, RBAC, AI pipeline).
- `php artisan route:list` no missing controller methods.
- Grep: no `is_super_admin`/`institution_id` in any `$fillable` except via guarded setters.
- Manual: super admin switches institution and sees only that tenant; assistant returns only permitted, tenant-scoped data.
- Security: no default creds in production seed; auth endpoints throttled.

*This plan resolves every Critical and High finding and the majority of Medium items. Low items are tracked for ongoing quality.*
