# 🤖 AI Assistant Documentation — AI SCUMS

The AI Academic Assistant is the central research contribution of AI SCUMS: a **strictly read-only, role-aware, RAG-based** assistant for academic and administrative queries.

## 1. Design Principles

1. **Read-only by construction.** Tools only read. The LLM is purely generative (no function-calling/executor). A jailbroken model cannot mutate marks, attendance, admissions, or payments.
2. **Role-aware authorization.** Each intent is mapped to permitted roles; the assistant refuses intents the user's role cannot access.
3. **Tenant isolation.** Data retrieval is scoped to the user's institution.
4. **Auditable.** Every interaction (query, intent, tool, response, tokens) is logged in `ai_audit_logs`.
5. **Explainable.** Deterministic intent detection (no opaque model step) → reproducible, auditable behavior.

## 2. The 6-Step Pipeline

Implemented in `App\Services\AI\AssistantService::ask()`:

```text
1. User Query
      │
2. Intent Detection  ──► IntentDetector (keyword/regex, deterministic)
      │
3. Authorization     ──► AuthorizationGate (role → intent; read-only enforced)
      │
4. Data Retrieval   ──► ToolRegistry → read-only AIDataTool (tenant-scoped)
      │
5. Response Gen.    ──► AIProviderManager → LLM (OpenAI/Claude/Gemini/Local/Mock)
      │
6. Audit Logging    ──► AiAuditLog
```

### Step 2 — Intent Detection
`IntentDetector` scores each intent's keyword list (substring match, length-weighted) and returns the best match + confidence. Intents: `student_attendance`, `student_next_exam`, `student_cgpa`, `student_schedule`, `teacher_low_attendance`, `teacher_course_performance`, `teacher_pending_evaluations`, `admin_admission_stats`, `admin_enrollment_report`, `admin_outstanding_fees`, `general`.

### Step 3 — Authorization
`AuthorizationGate` checks the user's role(s) against a role→intent policy. `general` is always allowed. Unauthorized → response explains the restriction.

### Step 4 — Data Retrieval
`ToolRegistry::forIntent($intent)` returns the matching `AIDataTool`. Each tool's `execute(User $user)` returns `['summary' => string, 'data' => array]`. Tools never write.

### Step 5 — Response Generation
`buildMessages()` composes a system prompt (role, read-only instruction, intent) and a user turn containing the retrieved data (`DATA: <json>`) + the original query. The selected provider's `chat()` returns the answer. Provider failures are caught and logged; a safe fallback message is returned.

### Step 6 — Audit Logging
`AiAuditLog::create([...])` records `user_id, institution_id, intent, tool, query, response, tokens_used`.

## 3. Providers (abstraction)

`AIProviderInterface` (`getName()`, `complete()`, `chat()`). Implementations under `app/Services/AI/Providers/`:

| Provider | Class | Notes |
|----------|-------|-------|
| OpenAI | `OpenAIProvider` | `withToken` auth; configurable model. |
| Claude | `ClaudeProvider` | `x-api-key` header. |
| Gemini | `GeminiProvider` | **Known gap:** currently omits API key (A13). |
| Local | `LocalProvider` | OpenAI-compatible (Ollama/LM Studio). |
| **Mock** | `MockProvider` | Offline, key-free; reformats retrieved data. Default. |

Selection: `AI_PROVIDER` env (default `mock`); overridable per-request (currently allowed for end users — a known egress risk, A16).

## 4. Tools (read-only)

| Intent | Tool | Data |
|--------|------|------|
| student_attendance | `StudentAttendanceTool` | Own attendance % + breakdown. |
| student_next_exam | `StudentNextExamTool` | Upcoming exams. |
| student_cgpa | `StudentCgpaTool` | Marks → CGPA/grades. |
| student_schedule | `StudentScheduleTool` | Weekly routine. |
| teacher_low_attendance | `TeacherLowAttendanceTool` | Students below 75%. |
| teacher_course_performance | `TeacherCoursePerformanceTool` | Per-subject averages. |
| teacher_pending_evaluations | `TeacherPendingEvaluationsTool` | Exams with missing marks. |
| admin_admission_stats | `AdminAdmissionStatsTool` | Admission counts by status. |
| admin_enrollment_report | `AdminEnrollmentReportTool` | Enrolled per class/program. |
| admin_outstanding_fees | `AdminOutstandingFeesTool` | Outstanding total + by status. |

Student/parent tools resolve the actor's **own** record (`BaseDataTool::resolveStudentFor`). Teacher/admin tools derive scope from relationships.

## 5. Security & Risks (see `ai-review.md`)

- **Tenant isolation is implicit.** Tools rely on the `TenantScoped` global scope, not an explicit `institution_id` filter. Admin tools (`Admin*`) currently ignore `$user` (A3). **Fix:** add explicit `where('institution_id', $user->institution_id)` in every tool (P1).
- **Super-admin "all institutions"** path returns cross-tenant aggregates, unlabeled (A4).
- **Prompt injection** via stored data (notice body, names) concatenated into the prompt (A10). Mitigation: delimit untrusted data; keep instructions in `system` role.
- **PII egress** when users pick a cloud provider (A16). Restrict provider selection.
- **Read-only is convention**, not enforced; add a runtime read-only guard / CI check (A8).

## 6. Usage

Web: `/assistant` (chat UI) → `POST /assistant/ask` with `{query, provider}`.
Programmatic: `app(AssistantService::class)->ask($user, $query, $provider)` returns an `AssistantResponse` DTO.

## 7. Extending

To add an intent:
1. Add a constant in `Intent`.
2. Register keywords in `IntentDetector::$patterns`.
3. Map role→intent in `AuthorizationGate::$policy`.
4. Create a `AIDataTool` (implement `AIDataToolInterface`, extend `BaseDataTool`).
5. Register it in `ToolRegistry`.

No changes to `AssistantService` are required — the pipeline is open for extension.
