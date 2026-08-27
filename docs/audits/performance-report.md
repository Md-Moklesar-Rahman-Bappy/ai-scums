# ⚡ Performance Review — AI-Powered IEMS

**Phase 8** · Queries, caching, queues, assets, JS bundles, images, DB load.
**Verdict:** Acceptable for demo scale; N+1 in list views/AI tools and synchronous LLM calls are the main scaling risks.

---

## 1. Findings

### P-1 — N+1 in list repositories — **Medium**
`BaseRepository::paginate()/all()` (`BaseRepository.php:50-63`) call `->get()`/`->paginate()` with no `with()`. Index Blades render related columns:
- `students.index` → `schoolClass.name`, `section.name`, `fees` count
- `fees.index` → `student.name`, `feeType.name`
- `exams.index` → `subject.name`, `section.name`
- `teachers.index` → `department.name`, `subjects`
Each triggers per-row queries. `show()` actions eager-load correctly, so detail views are fine.
**Fix:** add `with([...])` in repositories/controllers (see `database-review.md` §3).

### P-2 — N+1 in AI tools — **Medium**
`TeacherLowAttendanceTool.php:49-52` (per-student `Attendance` query); `StudentCgpaTool.php:48-49` (lazy `$mark->exam->subject`). At scale, a low-attendance scan issues one query per student.
**Fix:** eager-load `attendances`, `examMarks.exam.subject` once; aggregate in PHP or via SQL `GROUP BY`.

### P-3 — Synchronous, blocking LLM call — **Medium**
`AssistantService.php:76` calls `$providerInstance->chat()` synchronously within the HTTP worker. Real providers (`timeout(60/120)`) block the worker and can time out under load; the web request holds until the LLM responds.
**Fix:** dispatch a queued job that calls the provider and stores the result; UI polls or streams (e.g., via `AiConversation`/a jobs table). Make the queue connection configurable.

### P-4 — No rate limiting on `/assistant/ask` — **Medium**
`routes/web.php:44-45`. Authenticated users can spam a (paid) LLM → cost & DoS.
**Fix:** `throttle:...` middleware on assistant routes; per-user quota.

### P-5 — No caching layer — **Low/Med**
Dashboard aggregates (`DashboardController.php:26-92`: multiple `count()`/`whereDate`/`groupBy`) recomputed every request. Routine/notice calendar events re-queried each load.
**Fix:** cache dashboard stats (e.g., `Cache::remember` 5 min, tenant-keyed); cache routine/notice event feeds.

### P-6 — Missing non-FK indexes — **Medium**
See `database-review.md` §3 (attendances `date`, fees `status`, notices `audience/published_at`, routines `section/day/type`, exams `exam_date`, students `status`, etc.). These predicates are filtered often without supporting indexes.

### P-7 — `AttendanceController::analytics` loads all rows — **Low**
`AttendanceController.php:76` `Student::with('attendances')->get()` then per-student compute. Fine for small tenants; aggregate in SQL (`GROUP BY student_id, status`) at scale.

### P-8 — Asset strategy — **Low**
UI uses CDN Bootstrap 5.3 + Icons + jQuery/Select2/DataTables/Chart.js/FullCalendar (`app.blade.php:8-10,75-78`). CDNs reduce build complexity but add external runtime dependency & privacy considerations; no bundling/minification of local JS/CSS (dead Vite assets present — see UX-9). No image optimization needed (no uploads yet).
**Fix:** Decide on a single asset strategy (adopt Vite for local bundling/minification or remove dead `vite.config.js`/tailwind/postcss); consider Self-hosted CDNs for prod.

---

## 2. Strengths
- Query builder used correctly; no `select *` abuse beyond necessary.
- Unique/composite indexes present on tenant + important keys.
- Assistant only retrieves minimal context; `MockProvider` is O(n) over small datasets.

---

## 3. Priority
1. **P1** P-1/P-2 eager loading (N+1) — biggest win.
2. **P1** P-3 queue the LLM call + P-4 throttle.
3. **P2** P-5 caching of dashboard/calendar; P-6 add indexes.
4. **P3** P-7/P-8 asset & analytics optimization.
