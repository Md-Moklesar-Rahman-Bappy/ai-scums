# Performance Report — AI-SCUMS

**Date:** 2026-08-27
**Verdict:** Good fundamentals (pagination, Eloquent, soft deletes), with clear **N+1 / query-count** hotspots on the dashboard and a couple of un-cached global queries. No caching layer exists.

---

## 1. Query Efficiency

### Hotspot P1 — Dashboard (HIGH impact, easy win)
`DashboardController::index` (DashboardController.php:26-92) issues:
- 3 base counts (`Student::count()`, `Teacher::count()`, `Notice::where(...)->count()`).
- `attendanceTrend()`: 7 days × 2 statuses = **14** `COUNT` queries (lines 55-56).
- `resultDistribution()`: 6 grades × `COUNT` = **6** queries (line 71).
- `feeStatus()`: 4 statuses × `COUNT` = **4** queries (line 87).

**Total ≈ 27 queries per dashboard load.** All tenant-scoped (good) but each is a round-trip. Recommend consolidation via a single aggregated query or `DB::table(...)->selectRaw('status, count(*)')->groupBy('status')` and a date-range scan for attendance.

### Hotspot P2 — Super-admin layout query (MEDIUM)
`Institution::all()` executes on **every** super-admin page (app.blade.php:62) to populate the tenant switcher, and is unpaginated/un-cached. For many institutions this grows. Cache for the session or use a lightweight `pluck`.

### Hotspot P3 — N+1 in detail views (LOW/MEDIUM)
Controllers generally eager-load (e.g. `StudentController::show` loads `attendances`, `examMarks.exam.subject`, `fees`, `schoolClass`, `section` — StudentController.php:49). Good. However:
- `AdminEnrollmentReportTool` / `AdminAdmissionStatsTool` use `selectRaw` groupings that are fine, but several admin list views may lazy-load relationships. Add `$with` defaults on models or explicit `with()` in services.

## 2. Caching
- **No caching layer** configured for queries or config beyond Laravel defaults. `CACHE_STORE=array` in tests.
- Opportunities: cache dashboard aggregates (60s TTL), cache the institution switcher list (session), cache RBAC permission sets (spatie caches automatically when configured).

## 3. Assets / JS Bundles
- All JS/CSS served from **public CDNs** (app.blade.php). Pros: zero build. Cons:
  - No minification/versioning/hashing → no long-term browser caching, no SRI.
  - Runtime dependency on external availability (supply-chain + latency).
  - No tree-shaking; full Bootstrap + jQuery + Select2 + Axios + Chart.js downloaded on every page.
- **Recommend:** Vite build pipeline + `npm run build` producing hashed, minified, versioned assets (see DESIGN_SYSTEM.md / MASTER_IMPROVEMENT_PLAN.md).

## 4. Blade Rendering
- Views are straightforward; `@include('partials.alerts')` and `@stack/@push` used correctly.
- `Institution::all()` in layout is the main render-time DB hit.

## 5. Images
- No image-processing concerns found. `avatar` field exists on User but no upload/resize logic reviewed; ensure any upload uses `Storage` with validation + sizing.

## 6. Remediation Priority

| ID | Severity | Action | Effort |
|----|----------|--------|--------|
| P1 | High | Consolidate dashboard queries (groupBy/aggregation) + 60s cache | Low |
| P2 | Medium | Cache institution switcher list; `pluck` instead of `all()` | Low |
| P3 | Medium | Audit list views for N+1; add `$with`/explicit `with()` | Med |
| P4 | Medium | Introduce Vite build; vendor + minify + hash assets; add SRI | Med |
| P5 | Low | Enable spatie permission cache; OPcache in prod | Low |

## Conclusion
No crippling performance defects; the app will scale reasonably per-tenant. The highest-value, lowest-effort win is **dashboard query consolidation + caching**, followed by moving off runtime CDN assets.
