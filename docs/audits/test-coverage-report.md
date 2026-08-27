# 🧪 Testing & Coverage Report — AI-Powered IEMS

**Phase 9** · Feature / Unit / API / Security / Role / Tenant / AI tests.
**Verdict:** **No automated test suite exists.** This is a High-severity gap for an enterprise / thesis-grade system. Below: current state, what's missing, and recommended tests to generate.

---

## 1. Current State
- `tests/` contains only the default Laravel `ExampleTest` (`tests/Feature/ExampleTest.php`) and `TestCase.php`.
- No tests for auth, RBAC, tenant isolation, modules, or the AI pipeline.
- `phpunit.xml` does **not** override `DB_CONNECTION` (commented sqlite `:memory:`) → tests would run against the configured MySQL `ai_scums` DB. **Risk:** `RefreshDatabase` would wipe the developer's demo data. A separate testing DB (`ai_scums_testing`) or sqlite `:memory:` should be configured.
- No CI workflow (`.github/workflows`).

---

## 2. Coverage Gaps (by required category)
| Category | Status | Notes |
|----------|--------|-------|
| Feature Tests | ❌ Missing | Auth flow, each CRUD module, dashboard. |
| Unit Tests | ❌ Missing | Services (`StudentService::admit`, `FeeService::recalcStatus`, `ExamMark::deriveGrade`, `Attendance::percentageFor`). |
| API Tests | ❌ Missing | No API layer exists yet (planned). |
| Security Tests | ❌ Missing | Mass-assignment (`User.$fillable`), CSRF, auth throttle. |
| Role Tests | ❌ Missing | Each role's policy + `AuthorizationGate` intents. |
| Tenant Tests | ❌ Missing | **Critical** — verify isolation; super-admin switching after scoped binding. |
| AI Tests | ❌ Missing | Pipeline intent/auth/retrieve/audit; MockProvider; per-tool `institution_id` filtering. |

---

## 3. Recommended Test Suite (to generate)
**Tenant & Isolation (highest priority)**
- `TenantIsolationTest`: a student from institution A cannot read/resolve a record from B via route-model binding; global scope applied.
- `SuperAdminSwitchTest`: after binding `TenantManager` as scoped, switching `active_institution_id` changes the resolved tenant and writes stamp correctly.

**Auth & Security**
- `AuthTest`: login/logout; throttling (requires `throttle` middleware); inactive user blocked; seeded creds absent in production seed.
- `MassAssignmentTest`: `User::create($validated)` cannot set `is_super_admin`/`institution_id`/`is_active`.

**RBAC / Role**
- `PolicyTest`: each controller action denied for unauthorized role; `before()` super-admin bypass.
- `AssistantAuthorizationTest`: each role can/cannot invoke mapped intents.

**Modules (Feature)**
- `StudentModuleTest`, `TeacherModuleTest`, `AttendanceModuleTest`, `ExamModuleTest`, `FeeModuleTest`, `NoticeModuleTest`, `RoutineModuleTest`: index/store/update/delete with policy enforcement; validation errors.

**AI Pipeline (Unit/Feature)**
- `AssistantServiceTest`: intent detection + auth + tool execution + MockProvider answer + `AiAuditLog` created; cross-tenant data NOT leaked.
- `ToolTenantScopeTest`: every admin tool returns only `$user->institution_id` rows.
- `IntentDetectorTest`: keyword mapping; confidence (currently unused) behavior.

**Performance/Regression**
- `NPlusOneTest`: assert list endpoints issue bounded queries (Laravel's `assertQueryCount` / `DB::enableQueryLog`).

---

## 4. Setup Recommendations
1. Configure an isolated test DB: set `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` in `phpunit.xml` (requires SQLite extension), **or** create `ai_scums_testing` MySQL DB and point `phpunit.xml` there.
2. Use `RefreshDatabase` + seed `RolesAndPermissionsSeeder` (and `DemoDataSeeder` for AI tests) in `TestCase::setUp()`.
3. Add GitHub Actions CI running `php artisan test` on push/PR.
4. Enforce coverage threshold (e.g., `mockery/php-coverage` / `pestphp/pest` optional).

---

## 5. Priority
- **P1** Establish isolated test DB + base `TestCase` + Tenant & Auth tests.
- **P1** AI pipeline + tool tenant-scope tests.
- **P2** Per-module feature tests + role/policy tests.
- **P2** CI workflow.
- **P3** Performance regression & API tests (post API layer).
