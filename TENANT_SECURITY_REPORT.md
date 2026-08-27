# MULTI-TENANT SECURITY REVIEW

**Phase:** 9 · **Status:** Reviewed · Hardened (recommendations)

---

## 1. Architecture
- Shared database, discriminator column `institution_id` on every tenant row.
- `App\Models\Concerns\TenantScoped` adds a global scope filtering by
  `TenantManager::getCurrentTenantId()`.
- `ResolveTenant` middleware sets the active tenant per request:
  - Super admin → `session('active_institution_id')` (may be `null` = all).
  - Regular user → their own `institution_id` (immutable).

## 2. Isolation Verification
All tenant data models extend `BaseModel` → `TenantScoped` ✅
(`AcademicYear, Attendance, Department, Exam, ExamMark, Faculty, Fee, FeePayment,
FeeType, Notice, ParentModel, Program, Routine, SchoolClass, Section, Semester,
Student, Subject, Teacher`). `Institution`, `User`, `AuditLog`, `AiAuditLog`
correctly do **not** use the scope (they are platform/tenant-root entities).

Route-model binding applies the global scope, so e.g.
`Student::find($otherTenantId)` returns `null` ⇒ 404/403. ✅

## 3. Threats Assessed

### T-1 (High) — Super-Admin Null-Tenant Write
When `active_institution_id` is `null`, `TenantScoped::creating` assigns
`institution_id = null`, producing cross-tenant / orphan rows if a super admin
creates tenant data without selecting an institution.

**Recommendation (not auto-fixed to avoid breaking super-admin read views):**
- Block writes to tenant-scoped models when the resolved tenant is `null` for
  non-super-admins (already implied). For super admins, require an explicit
  active institution before any write; otherwise abort 422.
- Consider a `TenantScopeEnforcer` middleware on `POST/PUT/DELETE` to tenant
  routes that asserts a non-null tenant.

### T-2 (Medium) — `Institution` Switching Trust
`TenantController::switch` validates `exists:institutions,id` and gates on
`isSuperAdmin()`. A super admin can only switch to a **real** institution. ✅
However the session key `active_institution_id` is the sole source of truth; if an
attacker could forge it they'd see another tenant. Mitigation: it is server-side
session state, not client-controlled, and only writable by the super-admin-gated
endpoint. ✅ Acceptable.

### T-3 (Low) — URL Manipulation / IDOR
Because every read/write goes through the global scope + policy, guessing another
tenant's numeric id yields `null`/403. ✅ The `StudentPolicy::view` and
`AuthorizeResource` provide a second layer. No IDOR found in tested controllers.

### T-4 (Medium) — Global Scope Bypass
No `withoutGlobalScope('tenant')` calls exist in the codebase (verified by grep).
✅ Raw `DB::table()` or `->withoutGlobalScope()` usage would bypass isolation and
must be forbidden in code review. Add a static-analysis rule / CI grep to fail on
`withoutGlobalScope` and `DB::table` touching tenant tables.

### T-5 (Low) — `creating` Event & Mass Assignment
`TenantScoped::creating` only sets `institution_id` when empty, so an explicit
(attacker-supplied) `institution_id` in a fillable could attempt cross-tenant
write. `institution_id` is in `$fillable` on `User` and domain models — but it is
**never accepted from the request** in any controller (creation is service-driven
with the tenant taken from the manager). ✅ Verify in PR review that no controller
does `Model::create($request->all())` with `institution_id`.

## 4. Hardening Recommendations
1. Add CI guard: fail build on `withoutGlobalScope` / `DB::table` over tenant
   tables unless wrapped in an approved helper.
2. Implement `TenantScopeEnforcer` to reject tenant writes when tenant is null.
3. Add a uniqueness/ownership test: a seed of two institutions + cross-assertion
   that `User A` (inst 1) cannot fetch `inst 2` rows via any repository method.
4. Document that repositories must **never** call `setCurrentTenantId(null)` for
   regular users.

## 5. Testing Checklist
- [ ] User in Inst A `Student::count()` ≠ Inst B count and never includes B rows.
- [ ] `GET /students/{instB-student-id}` → 404 for Inst A user.
- [ ] Super admin with null active institution cannot POST tenant data.
- [ ] `grep` CI step flags any new `withoutGlobalScope('tenant')`.
