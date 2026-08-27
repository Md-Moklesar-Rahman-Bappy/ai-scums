# Roadmap

AI SCUMS is actively evolving. The following roadmap reflects planned research
and product direction. Priorities may shift based on feedback.

## Near-term (1.1)
- **Tenant robustness:** scoped `TenantManager`, fail-closed scope, explicit AI-tool tenant filters (from audit P0/P1).
- **Auth hardening:** rate limiting, account lockout, `User.$fillable` lockdown.
- **AI safety:** prompt-injection delimiters, no end-user external provider selection, audit-log retention.
- **UI:** mobile navigation (offcanvas), field-level validation feedback.

## Mid-term (1.2 – 1.3)
- **REST API (Sanctum)** for mobile and React frontends (current service layer is API-ready).
- **Performance:** eager loading, caching of dashboard/calendar, queued LLM generation.
- **Database hardening:** `institution_id` on `ai_feedback`/pivots, hierarchy discriminator, single current academic year, model events for derived fields.
- **Accessibility:** WCAG pass on navigation, forms, charts.

## Long-term (2.0 and beyond)
- **Predictive Analytics:** at-risk student detection, dropout/performance forecasting using historical attendance/marks.
- **Recommendation Engine:** intervention suggestions, personalized learning paths.
- **Voice Assistant** & **Multilingual Support** (Bengla/English).
- **Natural-language report generation** for administrators/parents.
- **Plugin/extension architecture** for institution-specific modules.

## Research Themes
The AI Academic Assistant remains the core research contribution. Future work
focuses on grounding/hallucination mitigation, on-prem LLM deployment, and
explainable, auditable decision support.

> Items are indicative and subject to change. See `CHANGELOG.md` for what shipped.
