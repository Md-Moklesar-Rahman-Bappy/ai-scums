# Test Report — AI-SCUMS

**Date:** 2026-08-27
**Verdict:** **CRITICAL GAP.** Automated test coverage is effectively **0%**. Only the default Laravel `ExampleTest` classes exist. README.md:209 falsely claims "Feature/unit tests cover auth, tenant isolation, RBAC, and the AI pipeline" and links a non-existent `docs/audits/test-coverage-report.md`.

---

## 1. Current State
- `tests/Feature/ExampleTest.php` — single test hitting `/` expecting 200.
- `tests/Unit/ExampleTest.php` — empty.
- `tests/TestCase.php` — standard.
- `phpunit.xml` — configured with `<include>app</include>` (so coverage can be measured) but no tests exercise it.
- `database/factories` — rich set of model factories exist (good foundation for tests).
- `database/seeders` — `DemoDataSeeder`, `RolesAndPermissionsSeeder` exist (good for test setup).

**Coverage estimate:** ~0% of `app/` logic.

## 2. Risk
- No regression safety net for tenant isolation, RBAC, or the AI pipeline — exactly the project's core value and the areas most likely to break under future change.
- The misleading README creates false assurance for adopters/auditors.

## 3. Recommended Suite (target ≥80%)

### 3.1 Unit Tests
- `IntentDetectorTest` — keyword mapping, confidence, `GENERAL` fallback.
- `AuthorizationGateTest` — each role→intent allow/deny matrix; `GENERAL` always allowed.
- `TenantManagerTest` — `getCurrentTenantId` resolution order; scoped binding.
- `PasswordPolicyTest` — `Password::defaults()` enforces min12/mixed/numbers/symbols/uncompromised.
- `FeeServiceTest` — status recomputation logic (paid/partial/overdue).
- `ExamServiceTest` — grade derivation from marks.

### 3.2 Feature Tests
- `AuthenticationTest` — register (creates institution+admin), login success/failure, lockout after N attempts, verify email (signed), password reset, logout session invalidation.
- `TenantIsolationTest` — **CRITICAL**: as Institution A user, request `students/{B-id}` → 404; assert no cross-tenant rows in repository reads. For each resource.
- `RbacTest` — student cannot hit teacher/admin routes; `authorizeResource` denies.
- `CrudTest` (per module) — create/read/update/delete with policy + validation assertions.
- `AiAssistantTest` —
  - student asks attendance → authorized, tool runs, audit log created, response read-only.
  - student asks outstanding-fees (admin intent) → denied by gate.
  - non-super-admin supplying `provider=openai` → `prohibited` (422).
  - response contains no mutation side-effects (assert DB unchanged).
- `DashboardTest` — renders with tenant-scoped counts; assert no cross-tenant leakage.
- `SecurityHeadersTest` — optional, after S10 hardening.

### 3.3 Database/Integration
- `TenantScopedScopeTest` — assert global scope applied; `withoutGlobalScope` not used in app.
- `MigrationTest` — schema integrity (foreign keys, soft deletes).

## 4. Tooling / CI
- Add `laravel/pint` (present) to CI lint.
- Add coverage config: `phpunit.xml` already includes `<source><include>app</include>`. Run with `./vendor/bin/phpunit --coverage-html build/coverage`.
- Add GitHub Actions: `composer test` + Pint + coverage gate (e.g. fail < 80%).
- Ensure tests use an isolated DB (sqlite `:memory:` or a test MySQL) — uncomment phpunit.xml:25-26 and add `RefreshDatabase` trait.

## 5. Effort & Sequencing
- Phase A (quick wins, ~1 day): AuthTest, TenantIsolationTest, AiAssistantTest, IntentDetectorTest, AuthorizationGateTest.
- Phase B: per-module CrudTest + service unit tests.
- Phase C: CI pipeline + coverage gate.
- Then **correct the README** to reflect real coverage (or keep claims only after suite exists).

## Conclusion
The absence of tests is the single biggest gap between the current project and a "professional SaaS-grade open-source" baseline. The factories/seeders already present make this very achievable. Build the suite (Phase A first, especially TenantIsolation + AI), then fix the README claim.
