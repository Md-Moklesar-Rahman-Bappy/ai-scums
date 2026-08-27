# 🗄️ Database Review — AI-Powered IEMS

**Phase 3** · Migrations, Models, Relationships, Indexes, Constraints, Normalization, N+1.
**Verdict:** Sound tenant-column strategy; tenant scope is fail-open and a few tables lack tenant columns.

---

## 1. Strengths
- **`institution_id` indexed** on every tenant table (Laravel `foreignId()->constrained()` adds the index), including `users`, `ai_conversations`, `ai_audit_logs`, `audit_logs`.
- **`institution_id` NOT NULL** on all `BaseModel` tables → DB enforces tenancy at write time.
- **Soft deletes** consistent across domain models + `institutions`.
- **Sensible FK cascades** — `cascadeOnDelete()` for ownership chains; `nullOnDelete()` for optional links (`user_id`, `marked_by`, `created_by`).
- **Good unique constraints**: `institutions.slug`, `users.email`, `students.admission_no`, `teachers.employee_id`, composite uniques on `attendances(student_id,subject_id,date)` and `exam_marks(exam_id,student_id)`.
- **`AiAuditLog`** already has composite index `(institution_id, created_at)`; `audit_logs` has `(model_type, model_id)`.
- Form Requests never expose `institution_id` → mitigates HTTP-layer tenant spoofing.

---

## 2. Findings

### DB-1 — Tenant global scope is **fail-open** — **High**
`app/Models/Concerns/TenantScoped.php:28-34` applies the filter only `if ($tenantId !== null)`. `TenantManager::getCurrentTenantId()` returns `null` when unresolved — for super admins without an active institution, and for **any non-web context** (queues, console, tests) where `ResolveTenant` never runs and `auth()->user()` is null. In those contexts every tenant-scoped query silently returns cross-tenant data.
**Fix:** Fail closed — throw when a tenant is required but unresolved, or propagate tenant context into jobs/commands; require an explicit "all-tenants intended" opt-in.

### DB-2 — `creating` observer only stamps tenant when resolved — **High**
`app/Models/Concerns/TenantScoped.php:36-42` sets `institution_id` only `if ($tenantId !== null && empty(...))`. On nullable AI/audit tables a null tenant yields `institution_id = NULL` → row never returned by scoped queries (silent data loss). On NOT NULL tables it throws.
**Fix:** Derive tenant from `auth()->user()->institution_id` as fallback, or throw if unresolved for a tenant-bound write; never persist `institution_id = null`.

### DB-3 — `ai_feedback` has **no `institution_id`** — **Medium**
Migration `2024_01_01_000005`; `app/Models/AiFeedback.php`. AI feedback cannot be DB-scoped; `AiFeedback::all()` is fully cross-tenant.
**Fix:** Add `institution_id` (nullable FK → `institutions`, `nullOnDelete`) + index; set from the linked audit log.

### DB-4 — `ai_conversations`/`ai_audit_logs`/`audit_logs` not tenant-scoped & nullable — **Medium**
These models don't extend `BaseModel`/`TenantScoped`. Isolation depends entirely on disciplined app code. For super admins (`institution_id = null`) rows are unreachable under any scope.
**Fix:** Provide `scopeForInstitution()` and enforce in repositories, or apply a soft scope allowing super-admin cross-tenant listing. Document the contract.

### DB-5 — Pivot tables `student_parent` & `teacher_subject` lack `institution_id` — **Medium**
Migrations `2024_01_01_000003:69-84`. A raw query is cross-tenant; nothing prevents linking a student from institution A to a parent from B (orphaned/invisible).
**Fix:** Add `institution_id` FK + index to both pivots; validate same-institution on attach.

### DB-6 — Spatie permission tables not tenant-scoped — **Medium**
`2026_08_27_041017_create_permission_tables.php` reads `config('permission.teams')` (default off) → roles/permissions shared platform-wide; `model_has_roles` has no institution column.
**Fix:** Enable Spatie teams if per-tenant roles required, or document roles as global by design and rely on tenant scope + `AuthorizationGate`.

### DB-7 — `users.institution_id` uses `nullOnDelete` — **Low/Med**
`2024_01_01_000001:38`. Hard-deleting an institution nulls `users.institution_id` → tenant-less accounts (compounds DB-1). **Fix:** `restrictOnDelete()` or `cascadeOnDelete()`.

### DB-8 — `institution_id` in `$fillable` is a spoof vector — **Medium (latent)**
Every tenant model lists `institution_id` in `$fillable` (e.g. `Student.php:27`). The observer sets it `if empty`, so any `Model::create([... 'institution_id' => $input])` could write to another tenant. Mitigated today because Requests don't include the field, but fragile.
**Fix:** Remove `institution_id` from `$fillable` (let `TenantScoped` own it) or force-set it unconditionally.

### DB-9 — `AttendanceService::mark` derives tenant from `TenantManager` (can be null) — **Medium**
`app/Services/AttendanceService.php:37`. If null, NOT NULL throws. **Fix:** derive from the resolved student's institution.

### DB-10 — `subjects` mixes 7 nullable polymorphic FKs — **Medium**
`2024_01_01_000002:96-113`. A row can hold school-side (`class_id`,`section_id`) and university-side (`department_id`,`program_id`,`semester_id`,`faculty_id`) links simultaneously → ambiguous placement; `faculty_id` duplicates `department→faculty`.
**Fix:** `type` discriminator requiring one consistent FK set; drop redundant `faculty_id`.

### DB-11 — `students`/`exams`/`routines` mix hierarchies — **Medium**
Same ambiguity as DB-10. **Fix:** discriminator + mutually-exclusive validation.

### DB-12 — `academic_years.is_current` not unique per institution — **Medium**
`2024_01_01_000002:31` no constraint → multiple "current" years possible. **Fix:** enforce single current year (app-level on save, or MySQL 8 functional unique index).

### DB-13 — `exam_marks.grade` not auto-maintained — **Low/Med**
`deriveGrade()` static (`ExamMark.php:54-70`) never called on save. **Fix:** compute in a `saving` event or generated column.

### DB-14 — `Fee::status` only recomputed via service — **Low/Med**
`recalcStatus()` not triggered on direct writes. **Fix:** call in a `saving` event.

### DB-15 — Repositories don't eager-load → N+1 in list views — **Medium**
`BaseRepository::paginate()/all()` (`BaseRepository.php:50-63`) issue `->get()`/`->paginate()` with no `with()`. Index Blades rendering related columns (students→class/section, fees→student/type, exams→subject) cause N+1. `show()` actions eager-load correctly.
**Fix:** add `with([...])` in repositories/controllers (see index suggestions below).

### DB-16 — `AttendanceController::analytics` loads all students+attendances — **Low**
`AttendanceController.php:76` `Student::with('attendances')->get()` then per-student compute. Fine for small tenants; aggregate in SQL at scale.

---

## 3. Suggested Indexes (missing non-FK)
| Table | Column(s) | Why | Sev |
|-------|-----------|-----|-----|
| attendances | `date`, `(section_id, date)` | `whereDate` in index/analytics | Med |
| fees | `status`, `(status, due_date)` | due reports filter `whereIn('status')` | Med |
| notices | `(audience, published_at)` / `(type, published_at)` | feed/calendar filters | Med |
| routines | `(section_id, day_of_week, type)`, `(type)` | weekly/calendar filters | Med |
| exams | `exam_date`, `exam_type` | listing/calendar | Low/Med |
| students | `status`, `roll_no` | admin filters/search | Low/Med |
| teachers | `status` | staff filters | Low |
| subjects | `type`, `code` | filtering/lookup | Low |
| ai_audit_logs | `intent`, `tool` | AI analytics | Low |
| academic_years | functional unique `(institution_id,(is_current=1))` | single current year (DB-12) | Med |

**Pivot hardening (DB-5):**
```php
$table->foreignId('institution_id')->constrained()->cascadeOnDelete();
$table->index(['institution_id','student_id']); // student_parent
$table->index(['institution_id','parent_id']);
$table->index(['institution_id','teacher_id']); // teacher_subject
$table->index(['institution_id','subject_id','section_id']);
```
**List eager-loads (DB-15):** Student→`schoolClass,section,academicYear`; Fee→`student,feeType`; Exam→`subject,section,academicYear`; Teacher→`department,subjects`.

---

## 4. Priority
1. **P0** DB-1/DB-2 fail-closed tenant scope + forced tenant stamping.
2. **P1** DB-3/DB-4/DB-5 add `institution_id` to `ai_feedback`, AI/audit tables (via scope), and pivots.
3. **P1** DB-8 harden `institution_id` (remove from `$fillable` or force-set).
4. **P2** DB-10/11 discriminator; DB-12 single current year; DB-13/14 model events.
5. **P2** DB-15 eager loading; add missing indexes (DB-16 + table above).
