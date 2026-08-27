# AI Assistant Security Review — AI-SCUMS

**Date:** 2026-08-27
**Components:** IntentDetector, AuthorizationGate, ToolRegistry, Providers (OpenAI/Claude/Gemini/Local/Mock), AssistantService, AiAuditLog
**Verdict:** Read-only enforcement is **effective**. Two governance gaps: (1) third-party LLM data egress, (2) no rate limiting. No capability for the AI to modify records, access unauthorized data, or leak tenant data beyond its own scope.

---

## 1. Pipeline (as built)
```
User Query
  → IntentDetector.detect()         (keyword/regex, deterministic)
  → AuthorizationGate.check()       (role → allowed intents)
  → ToolRegistry.forIntent()        (read-only AIDataTool)
  → AIProviderManager.driver()      (LLM)
  → AiAuditLog.create()             (audit)
```
Source: app/Services/AI/AssistantService.php:39-97.

## 2. Read-only enforcement — VERIFIED
- Every tool extends `BaseDataTool`/`AIDataToolInterface` and performs only `SELECT` (e.g. StudentAttendanceTool.php:39 `Attendance::where(...)->get()`; AdminOutstandingFeesTool.php:38 `Fee::where(...)->get()`).
- No tool calls `create/update/delete/save`. Mutation intents are not defined in the `Intent` set, and `AuthorizationGate` maps only read intents (AuthorizationGate.php:24-53).
- System prompt explicitly forbids suggesting changes (AssistantService.php:108-111).
- **Conclusion:** The assistant **cannot modify records**.

## 3. Authorization Gate — VERIFIED
- `AuthorizationGate::check()` allows `GENERAL` for all; otherwise requires the user's role to be in the intent's allowed-role list (AuthorizationGate.php:60-78).
- Route-level: `assistant.ask` uses `AssistantAskRequest` whose `authorize()` re-checks `assistant.use` permission (AssistantAskRequest.php:17-20). This *also* covers the duplicate route block in web.php:84-87, so the route duplication is dead code, not a bypass.
- **Conclusion:** Students/parents cannot invoke teacher/admin intents; role boundaries are enforced twice (gate + request).

## 4. Tenant Isolation within AI — VERIFIED
- Tools resolve tenant via `BaseDataTool::tenantId()` → `TenantManager::getCurrentTenantId()`. Queries filter by `institution_id` explicitly (AdminOutstandingFeesTool.php:38) or rely on the model global scope (StudentAttendanceTool uses `Attendance` which is tenant-scoped).
- `null` tenant (super-admin "all") → tools return safe empty summary (BaseDataTool.php:29-32).
- **Conclusion:** A user's assistant context is limited to their own institution.

## 5. Provider Manager — VERIFIED (no injection)
- `AIProviderManager::driver()` whitelists provider names; unknown names throw `InvalidArgumentException` (AIProviderManager.php:43-45). User-supplied `provider` cannot instantiate arbitrary classes.
- Provider override from request is `prohibited` for non-super-admins (AssistantAskRequest.php:32-34). Super-admins may choose a provider, but the endpoint/host come from config, not the request → no SSRF via provider selection.

## 6. Audit Logs — VERIFIED
- Every interaction is written to `AiAuditLog` with `user_id`, `institution_id`, `intent`, `tool`, `query`, `response`, `tokens_used` (AssistantService.php:138-146). Table is indexed on `(institution_id, created_at)`.
- `ai_feedback` links ratings to audit logs. Good accountability.

## 7. Findings

| ID | Severity | Finding | Recommendation |
|----|----------|---------|----------------|
| A1 | High | **Data egress to third-party LLM.** When `AI_PROVIDER` is `openai`/`claude`/`gemini`, tenant PII (student names, attendance %, fee amounts) is transmitted off-platform. Super-admins can also pick the provider at request time. | Keep `mock`/`local` as the documented default. Add a per-tenant flag `allow_external_llm`. Redact direct identifiers (names→IDs) before building messages. Document FERPA/GDPR posture. |
| A2 | High | **No rate limit on `assistant.ask`.** Enables cost abuse and context exhaustion. | Add `throttle:assistant` (e.g. 20 req/min/user) to both `ask` and `ask-legacy`. |
| A3 | Medium | **Deterministic intent detection is brittle.** Keyword matching can misfire (e.g. "my marks" → CGPA) and is English-only; no fallback to safe `GENERAL` when confidence is low (confidence is computed but unused). | Use confidence threshold to default to `GENERAL`; consider multilingual keywords; log mis-classifications. |
| A4 | Medium | **Tool↔intent coupling is implicit.** The registry maps intent→tool, but a future tool that writes would not be blocked by the gate (gate only checks roles, not mutation). | Add an interface-level contract/test asserting every `AIDataToolInterface` implementation is read-only (no write methods called). |
| A5 | Low | **`GENERAL` intent permits free-text chat** with the LLM using the system prompt only — fine, but responses are not constrained to tenant data and could be prompted to reveal the system prompt. | Add a guard against prompt-injection in `query` (basic sanitization / max length already 1000). Acceptable as-is. |

## 8. Could the AI…?
| Capability | Possible? | Evidence |
|------------|-----------|----------|
| Modify records | **No** | Tools are SELECT-only; no mutation intents. |
| Access unauthorized (cross-role) data | **No** | AuthorizationGate role map + request `authorize()`. |
| Leak other tenant's data | **No** (within a session) | TenantScoped + explicit `institution_id` filters. |
| Bypass permissions | **No** | Enforced at gate and FormRequest. |
| Exfiltrate to external LLM (governance) | **Yes, by config** | A1 above. |

## 9. Conclusion
The AI assistant is **safe by construction** for the core threat model (no write, no cross-tenant, no permission bypass). The remaining risks are **governance**: where the data goes (external LLM egress) and **availability/cost** (no throttling). Address A1 and A2 before any public/multi-tenant launch.
