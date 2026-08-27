# Tenant Isolation Report — AI-SCUMS

**Date:** 2026-08-27
**Mechanism under test:** Shared-database, `institution_id` column, `TenantScoped` global scope, `TenantManager`, `ResolveTenant` middleware.
**Verdict:** STRONG. Institution A cannot read or write Institution B's data through the application.

---

## 1. Architecture of Isolation

- `BaseModel` uses `TenantScoped` (app/Models/BaseModel.php:23).
- `TenantScoped::bootTenantScoped` adds a global `where(institution_id = ?)` scope (app/Models/Concerns/TenantScoped.php:28-34) and sets `institution_id` on create (lines 36-42).
- `ResolveTenant` middleware (registered globally in bootstrap/app.php:18-20) resolves the tenant: super-admin from session `active_institution_id`, everyone else from `user->institution_id` (ResolveTenant.php:37-42).
- `TenantManager` is a **scoped** binding (AppServiceProvider.php:24) so the same instance is shared across the scope and services.

## 2. Test Cases & Results

### TC-1: Direct URL manipulation (IDOR on show/edit/delete)
- Controllers use route-model binding + `authorizeResource` (e.g. StudentController.php:25, 47, 54, 77).
- Because `Student` extends `BaseModel`, the bound model is resolved **through the global scope**, so a student belonging to Institution B is not found for a user in Institution A → **404**, never 200.
- Result: **PASS.** Institution A cannot load Institution B's records by guessing IDs.

### TC-2: Repository bypass
- `BaseRepository::query()` (BaseRepository.php:37-43) deliberately does **not** bypass global scopes. Any repository read inherits `TenantScoped`.
- `find/findOrFail/paginate/all` all go through `query()` → tenant scoped.
- Result: **PASS.** No repository method exposes cross-tenant rows.

### TC-3: Write / mass-assignment injection of tenant
- `create()` receives `$validated` from FormRequests whose `rules()` do **not** include `institution_id` (e.g. StudentRequest.php:27-43). Even if present, `TenantScoped::creating` overwrites `institution_id` from the resolved tenant when empty.
- Result: **PASS.** A user cannot assign a record to another institution.

### TC-4: Super-admin "All institutions" scope
- When super-admin picks "All institutions" (`active_institution_id = null`), `TenantManager::getCurrentTenantId()` returns `null` → global scope **omitted** (TenantScoped.php:31) → super-admin sees everything (by design).
- AI tools guard `null` tenant and return safe empties (BaseDataTool.php:29-32; AdminOutstandingFeesTool.php:34-36).
- Result: **PASS (by design).** No *unintended* cross-tenant leak; the broad scope is the intended super-admin privilege.

### TC-5: Policy-level cross-tenant authorization
- `StudentPolicy::view` blocks students/parents from viewing other records and requires `students.view` for staff (StudentPolicy.php:42-57).
- `InstitutionPolicy` restricts all institution management to super-admin (InstitutionPolicy.php:34-57).
- Result: **PASS.**

### TC-6: Tenant switch endpoint
- `TenantController::switch` aborts 403 for non-super-admins and validates `exists:institutions,id` (TenantController.php:31-35). A non-super-admin POST is rejected and, regardless, `ResolveTenant` forces their own `institution_id` on the next request.
- Result: **PASS.**

## 3. Residual Risks (Medium/Low)

| ID | Severity | Finding | Recommendation |
|----|----------|---------|----------------|
| T1 | Medium | The global scope is **soft-disableable**: any code calling `WithoutTenantScope` / `->withoutGlobalScope('tenant')` or `DB::table(...)` raw queries bypasses isolation. No such call was found in the audit, but there is no guardrail preventing a future one. | Add a lint rule / test that fails on `withoutGlobalScope('tenant')` and `DB::table` usage outside an explicitly approved data-access layer. |
| T2 | Low | `Institution::all()` in the super-admin layout (app.blade.php:62) and `InstitutionController` rely on the scope being active. If a dev later adds a non-scoped model, isolation could regress silently. | Add a regression test asserting cross-tenant 404 for each resource. |
| T3 | Low | `AiAuditLog`/`AiConversation` store `institution_id` nullable; when null (super-admin "all"), logs are not tenant-tagged, so per-tenant audit export is incomplete for super-admin sessions. | Record the *effective* tenant or mark super-admin cross-tenant sessions explicitly. |

## 4. Conclusion
Tenant isolation is **correctly and consistently enforced** by a global scope that is hard to bypass accidentally. The only realistic threat is a *future* developer circumventing the scope via raw queries — mitigated by adding a CI guardrail test (T1). No evidence of current cross-tenant leakage.
