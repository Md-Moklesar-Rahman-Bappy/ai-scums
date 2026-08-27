# Dashboard Improvement Plan — AI-SCUMS

**Goal:** Redesign the dashboard into a professional, insight-rich SaaS landing page using the DESIGN_SYSTEM tokens and Chart.js, without altering backend logic.

---

## 1. Current State
`resources/views/dashboard.blade.php` + `DashboardController`:
- 3 KPI cards (students/teachers/notices) — plain numbers, no icon/trend.
- 3 charts: attendance (line), result distribution (bar), fee status (doughnut) using default Bootstrap colours.
- ~27 queries/load (see PERFORMANCE_REPORT P1).

## 2. Target Layout (12-col grid)

```
┌───────────────────────────────────────────────────────────┐
│ Greeting + date + "Ask AI" CTA                              │
├──────────┬──────────┬──────────┬──────────┬───────────────┤
│ Students │ Teachers │ Attendance│ Fee Collected│ Outstanding│  KPI cards
├──────────┴──────────┴──────────┴──────────┴───────────────┤
│ Attendance Analytics (line/area)      │ Fee Collection (bar by month) │
├──────────────────────────────────────┼──────────────────────────────┤
│ Student Statistics (donut: active/    │ Upcoming Events (notices)     │
│   graduated/transferred)             │ + AI Assistant Card            │
└──────────────────────────────────────┴──────────────────────────────┘
```

## 3. Components

### KPI Cards (5)
Each: icon chip (brand-50 bg, brand-600 icon), label (`--text-muted`), big figure (`tabular-nums`), and a trend delta (▲/▼ vs last month, success/danger color). Data: extend `DashboardController` to compute deltas (last 30d vs previous 30d) — still tenant-scoped.

### Attendance Analytics
- Area chart, brand `#2563EB` fill gradient, last 7 (or 30) days present vs absent.
- Add filter (7/14/30 days) via a small segmented control.

### Revenue / Fee Collection
- Grouped bar: expected vs collected per month (last 6 months). Uses `Fee` amounts.
- Card footer: total collected, collection rate %.

### Student Statistics
- Donut: active / graduated / transferred / inactive using `Student::status`.

### Upcoming Events
- List of `Notice` where `type=event` and `published_at>=now()`, with date chips. Empty state handled.

### AI Assistant Card
- Compact promo card: robot icon, one-line value prop ("Ask about attendance, exams, fees"), a "Open Assistant" button → `route('assistant.index')`, and a one-click example prompt that deep-links with `?q=...`.

## 4. Chart.js Theming (tokens)
```js
Chart.defaults.color = '#64748B';
Chart.defaults.font.family = 'Inter, system-ui';
Chart.defaults.borderColor = '#E2E8F0';
// datasets use palette: #2563EB,#16A34A,#D97706,#DC2626,#0EA5E9,#8B5CF6
```
Wrap charts in `.card` with 16px radius and `--shadow-sm`.

## 5. Backend changes (minimal, additive)
- `DashboardController`: add `attendanceTrend` range param; add `feeCollectionByMonth()`, `studentStatusBreakdown()`, `upcomingEvents()`, and KPI deltas. Keep all queries **tenant-scoped** and **cached 60s** (PERFORMANCE P1).
- Consider a single `DashboardService` to keep the controller thin.

## 6. Responsiveness
- KPI cards: `col-6 col-md-3 col-xl` auto-fit; charts stack `<lg`. Verified at 320–1440 (RESPONSIVE_REPORT).

## 7. Acceptance Criteria
- [ ] 5 KPI cards with icons + trends.
- [ ] Attendance, Fee collection, Student stats charts themed with brand palette.
- [ ] Upcoming events + AI card present.
- [ ] Dashboard queries consolidated/cached (≤ ~5 queries).
- [ ] Token-driven styling, no hard-coded Bootstrap blues.
- [ ] Looks correct at 320/768/1440.
