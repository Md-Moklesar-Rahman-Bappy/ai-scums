# 🏗️ Architecture — AI SCUMS

This document details the system architecture for the AI-Powered Integrated Educational Management System (IEMS).

## 1. High-Level Design

AI SCUMS is a **multi-tenant, role-aware educational ERP** built on Laravel 11 (PHP 8.2, MySQL). A single deployment serves schools, colleges, and universities. The defining component is a **read-only, RAG-based AI Academic Assistant**.

```text
                ┌─────────────────────────────┐
   Browser ───► │  Web (Bootstrap 5.3 views)  │
                └──────────────┬──────────────┘
                               │ HTTP (CSRF-protected)
                ┌──────────────▼──────────────┐
                │  Routes (routes/web.php)    │
                │  ├─ ResolveTenant middleware │
                │  └─ auth + role/permission   │
                └──────────────┬──────────────┘
       ┌───────────────────────▼───────────────────────┐
       │  Controllers (thin) → Services → Repositories  │
       │  Policies · Form Requests · Middleware · DTOs  │
       └───────────────┬───────────────────┬───────────┘
                        │                   │
              ┌─────────▼─────────┐  ┌──────▼──────────────┐
              │  Eloquent Models  │  │  AI Assistant        │
              │  (TenantScoped)   │  │  (6-step pipeline)  │
              └─────────┬─────────┘  └──────┬──────────────┘
                        │                   │
                  ┌─────▼─────┐       ┌─────▼──────────────┐
                  │  MySQL    │       │  LLM Providers      │
                  │ (tenanted)│       │ OpenAI/Claude/      │
                  └───────────┘       │ Gemini/Local/Mock   │
                                      └─────────────────────┘
```

## 2. Layered Structure (SOLID / Clean Architecture)

| Layer | Location | Responsibility |
|-------|----------|---------------|
| **Controllers** | `app/Http/Controllers` | HTTP I/O, delegation, `authorizeResource`. Thin. |
| **Requests** | `app/Http/Requests/{Domain}` | Validation (`FormRequest`). |
| **Policies** | `app/Policies` | Authorization per model; `before()` super-admin bypass. |
| **Middleware** | `app/Http/Middleware/ResolveTenant.php` | Resolve active tenant. |
| **Services** | `app/Services/{Domain}` | Business logic, transactions. |
| **Repositories** | `app/Repositories` | Eloquent access (`BaseRepository` + per-model). |
| **Models** | `app/Models` | `BaseModel` (abstract) + `TenantScoped` global scope, soft deletes. |
| **DTOs** | `app/DTOs/AI/AssistantResponse.php` | Immutable transport objects. |
| **Providers** | `app/Providers` | Service registration (`AIAssistantServiceProvider`). |

**Dependency direction:** Controller → Service → Repository → Model. Cross-cutting concerns (auth, tenant, validation) enforced at the boundaries.

## 3. Multi-Tenancy

- **Strategy:** shared database, `institution_id` column on every domain table.
- **Isolation:** `TenantScoped` trait (on `BaseModel`) applies a global `where institution_id = ?` scope and stamps `institution_id` on create.
- **Resolution:** `ResolveTenant` middleware sets the tenant from the session (super-admin switching) or the authenticated user.
- **Tenant root:** `Institution` is **not** tenant-scoped (it is the tenant itself).
- **⚠️ Known gap:** `TenantManager` is not bound as a scoped/singleton; super-admin switching + null-tenant writes are broken. See `docs/audits/database-review.md` (DB-1/DB-2) and `remediation-plan.md` (P0).

## 4. RBAC

- **Library:** `spatie/laravel-permission`.
- **Roles:** `super_admin`, `institution_admin`, `accountant`, `teacher`, `student`, `parent`.
- **Enforcement:** policies + `authorizeResource` in controllers + `before()` shortcut + `assistant.use` gate for the assistant.
- **Super-admin identity:** `User::is_super_admin` boolean + `super_admin` spatie role (recommend consolidating — see security-report F-5).

## 5. AI Assistant Subsystem

Pipeline (`AssistantService::ask`):

1. **Intent Detection** — `IntentDetector` (deterministic keyword/regex).
2. **Authorization** — `AuthorizationGate` (role → intent map; read-only enforced).
3. **Data Retrieval** — `ToolRegistry` resolves one of 10 read-only `AIDataTool`s.
4. **Response Generation** — `AIProviderManager` selects a provider implementing `AIProviderInterface` (`OpenAIProvider`, `ClaudeProvider`, `GeminiProvider`, `LocalProvider`, `MockProvider`).
5. **Audit Logging** — `AiAuditLog` records intent/tool/query/response/tokens.

See [`AI_ASSISTANT_DOCUMENTATION.md`](AI_ASSISTANT_DOCUMENTATION.md) for full detail.

## 6. Design Strengths

- Thin controllers, service/repository separation, DTOs, Form Requests, Policies.
- Read-only AI assistant with **no write path** and **no LLM executor** → jailbreak cannot mutate data.
- Deterministic, explainable intent detection (auditable for a thesis).
- Provider abstraction enables runtime LLM switching without code change.

## 7. Design Weaknesses (to address)

- `TenantManager` binding (P0); fail-open tenant scope (DB-1).
- AI tools rely implicitly on the global scope rather than explicit `institution_id` (AI review A2/A3).
- Anemic/inconsistently-used repository layer (best-practices BP-2/3/4).
- No events/queues; synchronous LLM call (performance P-3).

Full findings: [`docs/audits/`](audits/).
