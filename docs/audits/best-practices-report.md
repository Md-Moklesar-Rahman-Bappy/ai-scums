# ✅ Laravel Best-Practice Validation Report — AI-Powered IEMS

**Phase 2** · Static review of Controllers, Models, Migrations, Policies, Requests, Services, Middleware.
**Verdict:** Strong adherence to Laravel conventions with a few structural inconsistencies.

---

## 1. Checklist

| Practice | Status | Notes |
|----------|--------|-------|
| Thin Controller Pattern | ✅ Mostly | `DashboardController`, `AttendanceController` contain query logic (see BP-5/6). |
| Proper Validation | ⚠️ Partial | Centralized in Form Requests, but `exists:` rules not tenant-scoped (BP-11). |
| Authorization | ✅ | `authorizeResource` + policies + `assistant.use` gate. |
| Dependency Injection | ✅ | Constructor `private readonly` DI throughout. |
| Route Separation | ✅ | Web routes only; no API routes yet (planned). |
| Event Usage | ⚠️ None | No domain events; recommend for audit/notifications (BP-27). |
| Queue Usage | ⚠️ None | LLM call is synchronous (BP-15). |
| Service Classes | ✅ | Present per module; some anemic/dead deps (BP-2/3). |
| Exception Handling | ⚠️ Partial | Only AI provider failure logged; no custom exceptions (BP-26). |
| Logging | ⚠️ Partial | AI audit logged; no authz-denial/tenant-violation logging. |

---

## 2. Strengths

- **Layered structure** `Controllers → Services → Repositories → Models` with sub-namespacing (`Services/AI`, `Services/Tenant`, `Http/Requests/{Domain}`, `Policies`, `Contracts`, `DTOs`).
- **`declare(strict_types=1)`** present in the overwhelming majority of files.
- **Reusable domain logic in models** — `Attendance::percentageFor`, `ExamMark::deriveGrade`, `Fee::recalcStatus` (no duplication in controllers).
- **`RegistrationService` wraps onboarding in `DB::transaction`** (`RegistrationService.php:29`).
- **Immutability & contracts** — `AssistantResponse` DTO, `AIProviderInterface`, `AIDataToolInterface`.

---

## 3. Findings

### BP-1 — `TenantManager` not a scoped/singleton binding — **Critical**
`app/Services/Tenant/TenantManager.php` is resolved via `app(TenantManager::class)` in `TenantScoped.php`, `AttendanceService.php`, and `ResolveTenant.php`, but **never bound** in `bootstrap/providers.php`. Each call returns a fresh instance, so the tenant set by `ResolveTenant` is discarded. Regular users work only via the `auth()->user()->institution_id` fallback; super-admin switching is broken and super-admin writes violate NOT NULL.
**Fix:** In `AppServiceProvider::boot()` / a dedicated provider: `$this->app->scoped(TenantManager::class);`

### BP-2 — `AttendanceService` injects unused repository — **Medium**
`app/Services/AttendanceService.php:20` injects `AttendanceRepository` but `mark()` writes directly via `Attendance::updateOrCreate` (`:30-43`). Dead dependency; bypasses the abstraction.
**Fix:** Use `$this->repository` consistently, or drop the dependency.

### BP-3 — Repositories anemic & used inconsistently — **Medium**
Every repo only overrides `modelClass()`; queries live in `BaseRepository`. Services bypass them (`ExamService::enterMarks`, `FeeService`, `AttendanceService`). `RepositoryInterface` is never bound to a concrete.
**Fix:** Either move queries into repos & bind the interface, or collapse the layer and use Eloquent directly. As-is it adds indirection without benefit.

### BP-4 — `RepositoryInterface` is never bound — **Medium**
No `$app->bind(RepositoryInterface::class, ...)`. Services inject concrete classes, so the interface yields no swap/test benefit (dead abstraction).

### BP-5 — `DashboardController` contains analytics queries & no gate — **Medium**
`app/Http/Controllers/DashboardController.php:26-92` runs `Student::count()`, attendance loops, `ExamMark` grade counts, `Fee` status counts directly; no `authorize()` / permission check.
**Fix:** Extract an analytics/report service; add a `dashboard.view` permission gate.

### BP-6 — `AttendanceController` runs queries in the controller — **Medium**
`app/Http/Controllers/AttendanceController.php:32` (`Attendance::whereDate(...)->with('student')`) and `:45-47` (`Student::query()->when(...)`) duplicate/bypass `AttendanceService`.
**Fix:** Move into `AttendanceService` (`listForDate()`, `studentsForSection()`).

### BP-7 — Auth login logic in controller — **Low**
`AuthController::login` (`AuthController.php:52,54`) sets `last_login_at` and contains deactivation logic; belongs in a user/service layer.

### BP-8 — `TenantController::switch` uses `abort(403)` not a Gate/Policy — **Low**
`app/Http/Controllers/TenantController.php:28-29`; route not behind `role:super-admin`. Works, but inconsistent with the policy-based approach.

### BP-9 — `GET /assistant` has no permission check — **Low**
Only the `POST` is gated (`AssistantAskRequest::authorize()`). Any authenticated user can open the UI.

### BP-10 — Dead policy imports in controllers — **Low**
`ExamController.php:11`, `FeeController.php:10`, `InstitutionController.php:9`, `StudentController.php:9` import policy classes never referenced (auto-discovery resolves by model). Remove.

### BP-11 — `exists:` rules not tenant-scoped — **Medium**
`ExamMarkRequest.php:27`, `FeeRequest.php:26`, `StudentRequest.php:30-35`, `AttendanceMarkRequest.php:32`, `RoutineRequest.php:27-29` validate `exists:table,id` against the whole table → a `student_id` from another institution passes validation; service then stamps the *current* tenant but links a foreign record (integrity/isolation leak).
**Fix:** `Rule::exists('students','id')->where('institution_id', tenantId)` or validate ownership in the service.

### BP-12 — `InstitutionRequest` computes unused `$id` — **Low**
`app/Http/Requests/Institution/InstitutionRequest.php:27` (dead code; also no uniqueness check on `name`/`slug`).

### BP-13 / 14 — N+1 in AI tools — **Medium**
`TeacherLowAttendanceTool.php:49-52` (per-student `Attendance` query); `StudentCgpaTool.php:48-49` (lazy `$mark->exam->subject`).

### BP-15 — Synchronous blocking LLM call — **Medium**
`AssistantService.php:76` calls `$providerInstance->chat()` synchronously. Real providers block the worker. **Fix:** dispatch a queued job and poll/stream.

### BP-16 — No rate limiting on `/assistant/ask` — **Medium**
`routes/web.php:44-45`. Authenticated users could spam a (paid) LLM. **Fix:** `throttle:...`.

### BP-17 — `ToolRegistry::forIntent()` rebuilds all tools each call — **Low**
`ToolRegistry.php:33-55`. Cache the map or lazy-resolve.

### BP-18 — `AssistantAskRequest` rejects `mock` though manager exposes it — **Low**
`AIProviderManager.php:33` vs `AssistantAskRequest.php:29` (UI/validation mismatch).

### BP-19/20 — DRY duplication — **Low**
Outstanding-fee computation duplicated (`FeeService::dueReport` vs `AdminOutstandingFeesTool`); grade scale defined twice (`ExamMark::deriveGrade` vs `StudentCgpaTool::gradeToPoints`). Centralize.

### BP-21/22/23/24 — Coding-standard nits — **Low**
`User` model lacks `declare(strict_types=1)` and `isSuperAdmin(): bool` return type; `RoutineService::all()` untyped & unused; `AIProviderInterface::complete()` unused by pipeline; base `Controller.php`/`AppServiceProvider` lack `strict_types`.

### BP-25 — Inactive super admin can still log in — **Low**
`AuthController.php:54` condition `! $user->is_active && ! $user->isSuperAdmin()` is false for super admins → deactivated super admins authenticate. Verify intent.

### BP-26 — No custom exceptions / targeted logging — **Low**
`bootstrap/app.php` `withExceptions` empty. No logging of authz denials or tenant violations.

### BP-27 — No Events/Jobs/Listeners — **Low/Med** (recommendation)
Student admitted, fee paid, attendance marked could dispatch events for notifications/audit; LLM calls should be queued.

---

## 4. Recommendations (priority order)
1. **P0** Bind `TenantManager` scoped (BP-1).
2. **P1** Rationalize repository layer (BP-2/3/4); move controller query logic into services (BP-5/6).
3. **P1** Scope `exists:` rules tenant-aware (BP-11).
4. **P2** Queue the LLM call + throttle assistant (BP-15/16); fix N+1 (BP-13/14); DRY (BP-19/20).
5. **P3** Hygiene (BP-7–12, BP-17–27).
