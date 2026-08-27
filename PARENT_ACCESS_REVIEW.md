# PARENT ACCESS REVIEW

**Phase:** 8 · **Status:** Reviewed + Hardened · **Severity: CRITICAL before fix**

---

## 1. Authorised Parent Scope
A parent (`role = parent`, linked via `student_parent` pivot) MUST access **only**:
- Their own linked student(s) — attendance, exam CGPA, next exam, schedule (AI).
- Notices addressed to them / all.
- The AI assistant (with `assistant.use`).

A parent MUST NOT access:
- Other students (any field), other parents, other institutions, or any
  administrative module (fees, exams management, attendance marking, etc.).

## 2. Vulnerabilities Found

### V-1 (CRITICAL) — Parent Could List ALL Students
`StudentPolicy::viewAny()` returned `$user->can('students.view')`. The `parent`
role is seeded with `students.view`, so `/students` returned the full tenant
roster (names, admission numbers, guardians, phones, addresses).

### V-2 (CRITICAL) — Parent Could View ANY Student Record
`StudentPolicy::view()` opened with `if ($user->can('students.view')) return true;`
— therefore a parent could `GET /students/{id}` for **any** tenant student, leaking
academic and personal data of unrelated children.

## 3. Fixes Applied — `app/Policies/StudentPolicy.php`
```php
public function viewAny(User $user): bool
{
    if ($user->hasRole(['student', 'parent'])) {
        return false;                       // never list the whole cohort
    }
    return $user->can('students.view');
}

public function view(User $user, Student $student): bool
{
    if ($user->hasRole(['student', 'parent'])) {
        if ($user->student && $user->student->id === $student->id) {
            return true;
        }
        return $user->parent && $user->parent->students->contains($student);
    }
    return $user->can('students.view');
}
```
Now a parent can reach **only** the `Student` rows present in
`$user->parent->students` (tenant-scoped pivot), and only via `view`.

## 4. Residual Surface Reviewed
| Vector | Result |
|--------|--------|
| `attendances.*` | Parent lacks `attendance.view` ⇒ 403 ✅ |
| `exams.*` / `marks` | Parent lacks `exams.view`/`marks.manage` ⇒ 403 ✅ |
| `fees.*` | Parent lacks `fees.view` ⇒ 403 ✅ |
| `notices.*` | `notices.view` granted, but notices are broadcast (acceptable) ✅ |
| `assistant.*` | Intent gate restricts parent to `STUDENT_ATTENDANCE`,
  `STUDENT_NEXT_EXAM`, `STUDENT_CGPA` + `GENERAL`; `resolveStudentFor()` returns
  **only the parent's first linked child** ✅ |
| `institutions.*` | Super-admin only ⇒ 403 ✅ |
| Cross-institution | `TenantScoped` + route binding ⇒ parent can never bind another
  institution's student ✅ |

## 5. Additional Hardening Recommendation
For self-service, expose a dedicated `/my-child/{id}` endpoint (or reuse `show`
with the policy) rather than the admin `students.index`, and ensure the UI never
links to `/students` for parent/student roles.

## 6. Testing Checklist
- [ ] Parent `GET /students` → 403.
- [ ] Parent `GET /students/{ownChildId}` → 200.
- [ ] Parent `GET /students/{otherStudentId}` (same tenant) → 403.
- [ ] Parent `GET /students/{otherTenantStudentId}` → 404 (scope).
- [ ] Parent `GET /fees`, `/exams`, `/attendances` → 403.
