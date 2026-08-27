# Master Improvement Plan — AI-SCUMS

**Prepared by:** Architecture / Laravel / Security / QA / UI / DevOps review team (combined audit)
**Date:** 2026-08-27
**Purpose:** Convert AI-SCUMS from a strong student/ thesis project into a **professional, SaaS-grade, open-source Laravel platform** — *without rebuilding or removing working features* (per mandate).

All detailed findings live in `reports/`:
`PROJECT_AUDIT_REPORT · FUNCTIONAL_TEST_REPORT · TENANT_ISOLATION_REPORT · SECURITY_AUDIT_REPORT · AI_ASSISTANT_SECURITY_REVIEW · UI_UX_AUDIT · DESIGN_SYSTEM · DASHBOARD_IMPROVEMENT_PLAN · SIDEBAR_REDESIGN · AI_ASSISTANT_UI_REDESIGN · RESPONSIVE_REPORT · ACCESSIBILITY_REPORT · PERFORMANCE_REPORT · TEST_REPORT`

---

## 0. Audit Scores (summary)

| Dimension | Score | Grade |
|-----------|-------|-------|
| Architecture | 88 | A |
| Security | 82 | B+ |
| Performance | 75 | B |
| UI / UX | 62 | C+ |
| Documentation | 58 | C |
| **Test Coverage** | **~0%** | **F** |

**Headline:** The code is secure, well-architected, and tenant-safe. The transformation is about **polish, proof, and governance**, not rework.

---

## 1. Quick Wins (Low effort, High value) — do first
| # | Action | Report ref | Effort |
|---|--------|-----------|--------|
| Q1 | **Fix the misleading README test claim** (README.md:209). Either remove the claim or add a note "tests pending". | TEST_REPORT | 0.5h |
| Q2 | **Remove duplicate assistant routes** (routes/web.php:84-87). Covered by FormRequest `authorize()` but is dead/confusing code. | SECURITY S3 | 0.5h |
| Q3 | **Add `throttle:assistant`** to `assistant.ask`/`ask-legacy` (e.g. 20/min/user). | SECURITY S1 | 1h |
| Q4 | **Remove `is_super_admin`/`is_active` from `User.$fillable`** (User.php:39-42); assign explicitly in admin contexts. | SECURITY S6 | 0.5h |
| Q5 | **Cache the super-admin institution switcher** (`Institution::all()` in app.blade.php:62) → `pluck` + short cache. | PERF P2 | 1h |
| Q6 | **Add security headers** middleware (CSP, X-Frame-Options, HSTS, Referrer-Policy). | SECURITY S10 | 1h |
| Q7 | **Confirm session cookie hardening** (`http_only`, `same_site=lax`, `secure`) in prod `.env.example`. | SECURITY S7 | 0.5h |

## 2. High-Impact Improvements (architecture/trust)
| # | Action | Report ref | Effort |
|---|--------|-----------|--------|
| H1 | **Build the test suite to ≥80%** — start with TenantIsolation + AiAssistant + Auth + IntentDetector + AuthorizationGate. Factories/seeders already exist. | TEST_REPORT | 3–5d |
| H2 | **Dashboard query consolidation + 60s cache** (≈27 → ~5 queries). | PERF P1 | 0.5d |
| H3 | **AI data-egress governance**: default `mock`/`local`; per-tenant `allow_external_llm` flag; redact direct identifiers before LLM send; document FERPA/GDPR posture. | AI_SEC A1 | 1–2d |
| H4 | **Introduce a build pipeline (Vite + Tailwind)** and vendor/minify/hash assets (replace runtime CDN). Enables the design system. | UI U2 / PERF P4 | 2–3d |
| H5 | **Design system implementation** (tokens from DESIGN_SYSTEM.md) replacing hard-coded colours. | DESIGN_SYSTEM | 2d |

## 3. Security Fixes (prioritised)
| Severity | Item | Ref |
|----------|------|-----|
| High | `assistant.ask` rate limiting | S1 |
| High | Third-party LLM data egress policy + PII redaction | A1 / S2 |
| Medium | Duplicate routes removal | S3 |
| Medium | `User.$fillable` privilege fields | S6 |
| Medium | Sanctum API design (before shipping planned REST API) — tenant-scoped + throttled | S4 |
| Medium | Document super-admin "All institutions" AI behaviour | S5 |
| Low | Session cookie flags, CDN supply-chain, security headers | S7/S9/S10 |

**No Critical or exploitable code vulnerabilities were found.** Multi-tenancy, RBAC, CSRF, XSS, and SQLi controls are sound.

## 4. UI Improvements
| # | Action | Ref |
|---|--------|-----|
| U1 | **Mobile navigation** (off-canvas sidebar + hamburger) — currently the app is unusable <768px. | SIDEBAR_REDESIGN / RESPONSIVE R1 |
| U2 | Adopt DESIGN_SYSTEM tokens (Primary `#2563EB`, BG `#F8FAFC`, Card `#FFFFFF`, radius 16px, soft shadows). | DESIGN_SYSTEM |
| U3 | **Dashboard redesign** with KPI cards + trends + branded charts. | DASHBOARD_IMPROVEMENT_PLAN |
| U4 | **AI Assistant chat UX**: history, suggested prompts, typing indicator, intent/role/source/audit badges. | AI_ASSISTANT_UI_REDESIGN |
| U5 | Responsive table handling (`.table-responsive` / card lists on mobile). | RESPONSIVE R2 |
| U6 | Consistent form/error/label treatment. | UI_UX U4 |

## 5. Performance Improvements
| # | Action | Ref |
|---|--------|-----|
| P1 | Consolidate + cache dashboard aggregates | PERF P1 |
| P2 | Cache institution switcher list | PERF P2 |
| P3 | Audit list views for N+1; add `$with`/explicit `with()` | PERF P3 |
| P4 | Vite build: vendor/minify/hash assets + SRI | PERF P4 |
| P5 | Enable spatie permission cache; OPcache in prod | PERF P5 |

## 6. Documentation Improvements
> **Note:** Most Phase-15 docs **already exist** (`README.md`, `SECURITY.md`, `CONTRIBUTING.md`, `CHANGELOG.md`, `ROADMAP.md`, `CODE_OF_CONDUCT.md`, `ACKNOWLEDGEMENTS.md`, `LICENSE`, and `docs/{ARCHITECTURE,INSTALLATION,DEPLOYMENT,API_DOCUMENTATION,DATABASE_DOCUMENTATION,AI_ASSISTANT_DOCUMENTATION}.md`). They are reasonably good. The work is **reconciliation + completion**, not wholesale rewrite.
| # | Action | Ref |
|---|--------|-----|
| D1 | **Fix README inaccuracies**: false test claim; phantom `docs/audits/` + `docs/screenshots/` + `AIAssistantServiceProvider.php` references. | TEST_REPORT / PROJECT_AUDIT |
| D2 | Add **architecture diagram** (Mermaid) to README/ARCHITECTURE. | README req |
| D3 | Add **screenshots** (real captures) to `docs/screenshots/` and reference them. | UI/README |
| D4 | Document **AI compliance posture** (data egress, PII, audit). | AI_SEC A1 |
| D5 | Document **multi-tenancy model** clearly for contributors. | TENANT_ISOLATION |
| D6 | Keep `CHANGELOG.md` updated as improvements land (SemVer). | — |
| D7 | Verify `SUPPORTED_VERSIONS.md` / `SECURITY.md` disclosure process is accurate. | — |

---

## 7. Suggested Sequencing (roadmap to "SaaS-grade")

**Sprint 0 (1–2 days) — Quick Wins (Q1–Q7).** Immediate credibility: honest README, removed dead code, throttling, mass-assignment hardening, headers.

**Sprint 1 (1 week) — Proof (H1).** Test suite with tenant-isolation + AI + auth at the core. This de-risks everything else.

**Sprint 2 (1–2 weeks) — Foundations (H4 + H5 + U2).** Build pipeline + design system tokens; retire hard-coded colours.

**Sprint 3 (1–2 weeks) — Experience (U1, U3, U4, U5, A11y).** Mobile nav, dashboard redesign, AI chat UX, responsive tables, accessibility pass.

**Sprint 4 (3–5 days) — Governance (H2, H3, P1–P5, D-steps).** Dashboard perf, AI egress policy, caching, docs reconciliation + screenshots/diagram.

## 8. Definition of Done (per area)
- **Security:** No High/Medium open; throttling + egress policy live; README honest.
- **Tests:** ≥80% coverage; CI gate failing below threshold; TenantIsolation + AiAssistant green.
- **UI:** Token-driven; mobile-nav usable at 320px; dashboard + AI chat redesigned.
- **Perf:** Dashboard ≤ ~5 queries; assets hashed/minified; cache layering present.
- **Docs:** Accurate, with diagram + screenshots; AI compliance documented.

## 9. Risk Notes
- The biggest risk is **scope creep / rebuild temptation** — explicitly avoided; all changes are additive or refactors within existing architecture.
- AI third-party egress (H3) is a **compliance** risk if deployed publicly with an external provider — resolve before any multi-tenant public launch.
- Keep `mock`/`local` as the **default** provider to avoid accidental PII egress.

---
*End of Master Improvement Plan. Implementation should begin with Sprint 0 Quick Wins and proceed through the documented sprints, referencing the individual reports in `reports/` for detail.*
