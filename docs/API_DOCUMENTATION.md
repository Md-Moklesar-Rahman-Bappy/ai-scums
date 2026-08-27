# 🔌 API Documentation — AI SCUMS

> **Status:** A REST API for mobile / React clients is **planned** (Roadmap 1.2). The current release (1.0.0) is **web-only**. This document specifies the *intended* API surface and the architectural readiness of the codebase.

## 1. Design Intent

- **Authentication:** Laravel Sanctum (token-based) for SPA/mobile.
- **Authorization:** reuse existing spatie roles/permissions + Policies.
- **Tenant isolation:** every API request resolves the tenant from the authenticated user (or an `X-Institution-Id` header validated against the user's access); the `TenantScoped` global scope applies automatically.
- **AI endpoint:** `/api/assistant/ask` mirrors the web `AssistantController::ask`, returning `AssistantResponse` JSON.

## 2. Proposed Endpoints (draft)

All under `routes/api.php` (Sanctum-protected), prefix `/api`.

### Auth
| Method | URI | Description |
|--------|-----|-------------|
| POST | `/login` | Obtain token (email, password). |
| POST | `/logout` | Revoke token. |
| GET  | `/user` | Current user + role + institution. |

### Students
| Method | URI | Description |
|--------|-----|-------------|
| GET  | `/students` | Paginated list (policy: institution_admin/teacher). |
| POST | `/students` | Admit (policy: institution_admin). |
| GET  | `/students/{student}` | Show (self/policy). |
| PUT  | `/students/{student}` | Update. |
| POST | `/students/{student}/promote` | Promote. |
| DEL  | `/students/{student}` | Remove. |

### Attendance / Exams / Fees / Notices / Routines
Mirror the web resource controllers (same service layer), returning JSON and using the same Form Requests & Policies.

### AI Assistant
| Method | URI | Body | Response |
|--------|-----|------|----------|
| POST | `/assistant/ask` | `{ "query": "...", "provider": "mock" }` | `AssistantResponse` JSON (`answer`, `intent`, `authorized`, `tool`, `context`, `tokens`, `provider`). |

The `AssistantResponse` DTO already serializes to array (`toArray()`), making API exposure trivial.

## 3. Readiness

- Services (`*Service`) are UI-agnostic and reusable for API controllers.
- `AssistantResponse` is a clean DTO ready for JSON.
- Policies & Form Requests are shared between web and API.
- **Gaps to close before API launch:**
  - Add Sanctum; configure `config/auth.php` guard `api`.
  - Throttle API auth + `/assistant/ask`.
  - Explicit tenant resolution per API request.
  - Rate-limit & monitor AI usage (cost).
  - API tests (see `test-coverage-report.md`).

## 4. Example (intended)

```http
POST /api/assistant/ask
Authorization: Bearer <token>
Content-Type: application/json

{ "query": "What is my attendance?", "provider": "mock" }
```

```json
{
  "answer": "Here is what I found ...",
  "intent": "student_attendance",
  "authorized": true,
  "tool": "student_attendance",
  "context": { "percentage": 92, "breakdown": { "present": 18, "absent": 1 } },
  "tokens": 24,
  "provider": "Mock (Offline)"
}
```
