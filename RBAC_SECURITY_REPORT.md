# RBAC SECURITY REPORT

**Phase:** 7 · **Status:** Reviewed + Hardened

---

## 1. Authorization Strategy (as built)
- Controllers use **Policies** via `$this->authorizeResource()` /
  `$this->authorize()`.
- Spatie **permissions** gate policies (`$user->can('students.view')`).
- AI assistant uses an **Authorization Gate** (intent → role map).
- Super admin short-circuits policies via `before()`.

## 2. Route-by-Route Coverage
| Route group | Mechanism | Verdict |
|-------------|-----------|---------|
| `institutions.*` | `InstitutionPolicy` (super-admin only) | ✅ |
| `students.*` | `StudentPolicy` | ⚠️ Fixed (see §4) |
| `teachers.*` | `TeacherPolicy` | ✅ |
| `attendances.*` | `AttendancePolicy` (inline `authorize`) | ✅ |
| `exams.*` | `ExamPolicy` | ✅ |
| `fees.*` | `FeePolicy` | ✅ |
| `notices.*` | `NoticePolicy` | ✅ |
| `routines.*` | `RoutinePolicy` | ✅ |
| `assistant.*` | **none** (route-level) | ⚠️ Fixed (§3) |
| `dashboard` | `auth` only | ✅ (read-only counts) |

No route relies **solely on UI logic** for authorization after this audit — every
mutating/reading controller either has a policy or (now) a `permission` middleware.

## 3. Fix — Assistant Route Gate
`/assistant`, `/assistant/ask`, `/assistant/ask-legacy` previously had **no**
authorization check beyond `auth`. Added `->middleware('permission:assistant.use')`
so only roles granted `assistant.use` can reach the assistant (matches the AI
`AuthorizationGate` and the seeded permission set).

## 4. Fix — StudentPolicy Leak (see PARENT_ACCESS_REVIEW.md)
`viewAny()` and `view()` trusted `students.view` for **all** roles, including
`parent`/`student`. Reworked so those roles can only see their own/linked record,
while staff roles retain listing/management. This is the most severe RBAC defect
found.

## 5. Least-Privilege Assessment — `RolesAndPermissionsSeeder`
| Role | Notes |
|------|-------|
| `super_admin` | All permissions (expected, platform owner) |
| `institution_admin` | Broad but tenant-scoped — acceptable |
| `teacher` | `students.view` only (no write) — good |
| `accountant` | Fees + read — good |
| `student` | `assistant.use`, `routines.view`, `notices.view` — minimal ✅ |
| `parent` | `assistant.use`, `students.view`, `notices.view` — `students.view` is
  required for *own* record; the policy no longer lets it escalate to listing ✅ |

## 6. Defence-in-Depth Recommendations
- Add `permission:` middleware to resource routes as a secondary control (policies
  remain the source of truth).
- Introduce a `StudentPolicy::viewAny` unit test asserting parents get `false`.
- Consider a `/me` scoped endpoint for student/parent self-service instead of
  reusing the admin `students` routes.
