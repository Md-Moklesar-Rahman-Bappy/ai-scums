# 🐞 Bug Detection Report — AI-Powered IEMS

**Phase 7** · Automated inspection for PHP/Laravel errors, undefined variables, dead code, unused imports, duplicate logic, route conflicts, model issues, API issues.
**Method:** Static grep + `php -l` (0 syntax errors across 80+ PHP files) + cross-file reference checks.

---

## 1. High

### B-1 — Assistant `Mock` provider unusable from UI (broken out-of-the-box) — **High**
`AssistantAskRequest.php:29` validates `provider` as `in:openai,claude,gemini,local` — **`mock` missing**. But `AIProviderManager.php:57` `available()` returns `mock` too, so `assistant/index.blade.php:9` renders a "Mock" dropdown. Selecting it → 422. Worse, `config/ai.php:17` defaults `AI_PROVIDER=mock` while the dropdown's first option is `openai` (no key) → OpenAI call fails → caught → "unable to generate a response." So default config yields a non-working assistant unless "Mock" is picked, which validation rejects.
**Fix:** Add `mock` to the `in:` rule; default-select the active provider (`@selected($p === config('ai.provider'))`).

### B-2 — `InstitutionController::show()` missing but route registered — **High**
`routes/web.php:48` `Route::resource('institutions', InstitutionController::class)` registers `GET /institutions/{institution}` → `show()`, but no `show()` exists and no `resources/views/institutions/show.blade.php`. Visiting → `BadMethodCallException`.
**Fix:** Add `show()` + view, or `->except(['show'])`.

---

## 2. Medium
- **B-3 — Super-admin "All institutions" writes orphaned records (`institution_id=NULL`).** `TenantManager.php:34-48` returns null when unresolved; `TenantScoped.php:36-42` sets tenant only `if ($tenantId !== null && empty(...))`. A super admin in "all" mode creating a record gets `institution_id=NULL` → invisible to scoped queries. **Fix:** Prevent writes in "all" mode or require a target institution.
- **B-4 — `RoutineService::calendarEvents()` string-concatenates nullable attributes** (`RoutineService.php:43`) → titles like `" (Room )"`. Guard nulls (see UX-4).
- **B-5 — `attendances` unique index includes nullable `subject_id`** (`2024_01_01_000004:32` `unique(['student_id','subject_id','date'])`). With `subject_id` NULL, MySQL treats NULLs as distinct → duplicate daily rows possible if inserted directly. App code uses `updateOrCreate` (matches `subject_id IS NULL`) so low practical risk; constraint doesn't truly enforce for subject-less attendance. **Fix:** default/generated `subject_id` or partial unique index.

---

## 3. Low
- **B-6 — Unused import in `MockProvider`** (`MockProvider.php:7` `use App\Services\AI\AssistantService;`) — never referenced. Remove.
- **B-7 — Dead models/schema:** `AiConversation.php` & `AiFeedback.php` defined + migrated but never written by any controller/service/repo. Dead unless planned.
- **B-8 — Unused variable to view:** `AssistantController.php:36` passes `'user'` to `assistant.index`, but the view only uses `$providers`. Harmless.
- **B-9 — `AIProviderManager::driver` fallback mismatch:** `AIProviderManager.php:41` `$name = $name ?: config('ai.provider', 'openai')` hardcodes `'openai'` while `config/ai.php` defaults `'mock'`. Config always set → harmless but latent footgun.
- **B-10 — No `@can` on `DashboardController::index`** — any authenticated user loads dashboard (read-only aggregates; arguably acceptable but not explicitly gated). Low.
- **B-11 — Unused policy imports** in `ExamController/FeeController/InstitutionController/StudentController` (auto-discovery resolves by model). Remove.
- **B-12 — `InstitutionRequest` computes unused `$id`** (`InstitutionRequest.php:27`). Remove.
- **B-13 — `RoutineService::all()` unused** (only `calendarEvents()` called). Low.
- **B-14 — `AIProviderInterface::complete()` unused by pipeline** (only `chat()` invoked). Low.
- **B-15 — `User` model missing `declare(strict_types=1)` and `isSuperAdmin(): bool` return type.** Low consistency nit.
- **B-16 — Deactivated super admin can still log in** (`AuthController.php:54`). Verify intent.

---

## 4. Items Verified CORRECT (no bug)
- **Variable passing:** every Blade receives exactly the variables it uses (`dashboard` → `$stats/$attendanceTrend/$resultDistribution/$feeStatus`; `students/show` loads relations; `attendances/index` → `$records/$date/$summary`; `fees/index` → `$fees/$due`; `exams/show` → `$exam/$summary`; `assistant/index` → `$providers`; `routines/index`, `notices/index`). **No undefined-variable usage.**
- **Blade structure:** all pages use `<x-layouts.app>`/`<x-layouts.guest>` (matching `$slot`); no broken `@include`; no `@yield`/`@section` mismatch.
- **Tenant wiring:** `ResolveTenant` correctly appended to `web` group; `TenantScoped` global scope; `Institution` correctly NOT scoped.
- **RBAC alignment:** `RolesAndPermissionsSeeder` defines exactly the permissions the views' `@can` and policies check; `authorizeResource` aligns with policy methods.
- **Models/relationships/migrations:** consistent (Student→schoolClass/section/program/semester; Teacher→subjects pivot; Attendance→subject/section/student; Exam→marks; Fee→payments; Notice→creator; `ExamMark::deriveGrade`; `Attendance::percentageFor`).
- **AI pipeline:** all five providers implement `AIProviderInterface`; registered in `bootstrap/providers.php`. **No references to non-existent `IntentClassifier`/`QueryRouter`/`ResponseFormatter`** (grep: none) — the old README's claims were inaccurate but the code is correct.
- **Forms/security:** CSRF on all forms; correct verbs; `Auth::attempt($request->validated())` safe (LoginRequest validates only email+password); reset views receive `$token`/`$email`.
- **No route conflicts:** no duplicate URIs/verbs; `ask` & `ask-legacy` coexist; resource routes standard.

---

## 5. Priority
1. **P1** B-1 (mock rule) + B-2 (InstitutionController::show) — both break core UX/routes.
2. **P1** B-3 (super-admin null-tenant writes).
3. **P2** B-4/B-5 (routine titles, attendance unique).
4. **P3** B-6–B-16 hygiene cleanups.
