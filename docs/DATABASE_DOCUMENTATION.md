# 🗄️ Database Documentation — AI SCUMS

Reference for the MySQL schema. Generated from `database/migrations/`.

## 1. Tenant Model

- Every domain table carries `institution_id` (NOT NULL) + `created_at`, `updated_at`, `deleted_at` (soft deletes).
- `institution_id` is created via `foreignId()->constrained()` → indexed.
- `Institution` itself is the tenant root and is **not** tenant-scoped.

## 2. Tables

### `institutions`
`id, name, type(school|college|university), slug(uniq), email, phone, address, logo, website, settings(json), is_active, timestamps, softDeletes`.

### Academic structure
- `academic_years` — `institution_id, name, start_date, end_date, is_current`.
- `classes` — `institution_id, academic_year_id, name`.
- `sections` — `institution_id, class_id, name`.
- `faculties` — `institution_id, name, ...`.
- `departments` — `institution_id, faculty_id(null), name, code`.
- `programs` — `institution_id, department_id(null), name, code, degree`.
- `semesters` — `institution_id, program_id, name, number`.
- `subjects` — `institution_id, name, code, type(subject|course), class_id, section_id, department_id, program_id, semester_id, faculty_id, credit_hours, description`.

### People
- `students` — `institution_id, user_id(null), admission_no(uniq), roll_no, academic_year_id, class_id, section_id, department_id, program_id, semester_id, gender, date_of_birth, blood_group, guardian_name, guardian_phone, address, admission_date, status(active|inactive|graduated|transferred)`.
- `teachers` — `institution_id, user_id(null), employee_id(uniq), department_id(null), designation, qualification, joining_date, status`.
- `parents` — `institution_id, name, relation, phone, email, address, occupation`.
- `student_parent` (pivot) — `student_id, parent_id`.
- `teacher_subject` (pivot) — `teacher_id, subject_id`.

### Operations
- `attendances` — `institution_id, student_id, subject_id(null), section_id, date, status(present|absent|late|half_day), marked_by, remarks`. Unique `(student_id, subject_id, date)`.
- `exams` — `institution_id, academic_year_id, subject_id, section_id, name, exam_type, exam_date, total_marks, pass_marks`.
- `exam_marks` — `institution_id, exam_id, student_id, marks_obtained, total_marks, grade, remarks, entered_by`. Unique `(exam_id, student_id)`.
- `fee_types` — `institution_id, name, description, default_amount`.
- `fees` — `institution_id, student_id, fee_type_id, amount, paid_amount, due_date, paid_date, status(pending|partial|paid|overdue)`.
- `fee_payments` — `institution_id, fee_id, student_id, amount, collected_by, ...`.

### Communication & AI
- `notices` — `institution_id, title, body, type(announcement|event|notification), audience(all|students|teachers|parents|admins), published_at, expires_at, created_by`.
- `routines` — `institution_id, type(class|exam), subject_id, teacher_id, section_id, day_of_week, start_time, end_time, room, effective_from, effective_to`.
- `ai_conversations` — `institution_id(null), user_id, ...`.
- `ai_audit_logs` — `institution_id(null), user_id, intent, tool, query, response, tokens_used, created_at`. Index `(institution_id, created_at)`.
- `ai_feedback` — `user_id, ...` (**no `institution_id` — known gap, DB-3**).
- `audit_logs` — generic activity log; index `(model_type, model_id)`.

### Auth & RBAC
- `users` — `institution_id(null for super admin), name, email(uniq), password, phone, avatar, is_super_admin, is_active, last_login_at, timestamps, softDeletes`.
- `password_reset_tokens`, `sessions`, `jobs`, `cache`, `failed_jobs` (Laravel defaults).
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` (spatie; **global, not tenant-scoped — DB-6**).

## 3. Indexes

- All `institution_id` and FKs are indexed by Laravel's `foreignId()`.
- `AiAuditLog` has `(institution_id, created_at)`; `audit_logs` has `(model_type, model_id)`.
- **Missing recommended indexes** (add per `database-review.md` §3): `attendances(date)`, `fees(status)`, `notices(audience,published_at)`, `routines(section_id,day_of_week,type)`, `exams(exam_date)`, `students(status)`, `subjects(type,code)`, `ai_audit_logs(intent,tool)`.

## 4. Relationships (key)

- `Student` → `schoolClass(class_id)`, `section`, `program`, `semester`, `parents()` (pivot), `attendances()`, `examMarks()`, `fees()`.
- `Teacher` → `department`, `subjects()` (pivot).
- `Attendance` → `student`, `subject`, `section`.
- `Exam` → `subject`, `marks()`.
- `Fee` → `student`, `feeType`, `payments()`.
- `Notice` → `creator()` (User).
- `Institution` → `users()`, `students()`.

## 5. Data Integrity Notes

- FK `onDelete`: cascade for ownership chains; `nullOnDelete` for optional user links.
- Derived fields (`exam_marks.grade`, `fees.status`) should be maintained via model events (currently computed in services — DB-13/14).
- `academic_years.is_current` not unique per institution (DB-12).
- `subjects`/`students`/`exams`/`routines` mix school- and university-side FKs; a `type` discriminator is recommended (DB-10/11).

## 6. Tenant Safety (important)

- The global `TenantScoped` scope filters every `BaseModel` by `institution_id`.
- **Fail-open risk:** when no tenant resolves (null), the scope is NOT applied (DB-1). Must be fixed (fail-closed) before production.
- `ai_feedback` and pivot tables currently lack `institution_id` (DB-3/DB-5).
