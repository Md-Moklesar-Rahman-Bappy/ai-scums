# Accessibility Report — AI-SCUMS

**Date:** 2026-08-27
**Verdict:** Baseline acceptable (semantic HTML, escaped output) but **missing ARIA, labels, focus states, and keyboard nav for the sidebar/drawer**. Needs a focused a11y pass.

---

## 1. Strengths
- Output is Blade-escaped (`{{ }}`); no raw HTML injection → no XSS and predictable DOM.
- Native `<button>`, `<form>`, `<input>` elements used (good for screen readers).
- `csrf-token` meta + Axios CSRF is irrelevant to a11y but fine.
- Charts are decorative; provide text alternatives where they convey data.

## 2. Findings

| ID | Severity | Area | Issue | Fix |
|----|----------|------|-------|-----|
| A11y-1 | High | Sidebar/Nav | Sidebar `<nav>` lacks `aria-label`; active link has no `aria-current`; **no keyboard-operable mobile drawer** (hamburger not focus-managed). | Add `aria-label="Primary"`, `aria-current="page"`, focus trap + `Escape` to close drawer (SIDEBAR_REDESIGN). |
| A11y-2 | Medium | Forms | Some inputs may lack associated `<label>` (e.g. assistant query uses `placeholder` only; provider select has a `<label>` — good). | Ensure every input has `<label for>` or `aria-label`. |
| A11y-3 | Medium | Contrast | Current sidebar uses `#cbd5e1` on `#1e293b` (ratio ~7:1 — ok) but page text `#64748B` on `#F8FAFC` for muted text ~4.5:1 (borderline). Buttons use brand `#2563EB` on white (ok). | Adopt DESIGN_SYSTEM tokens; verify `--text-muted` meets ≥4.5:1; use `#475569` if needed. |
| A11y-4 | Medium | Focus states | No visible `:focus-visible` ring defined in inline CSS; default outline may be removed by Bootstrap reset. | Add `:focus-visible { outline: 2px solid var(--brand-600); outline-offset:2px; }` globally. |
| A11y-5 | Low | Images/icons | Bootstrap Icons are decorative `<i>` — fine, but ensure they're `aria-hidden="true"`. | Add `aria-hidden="true"` to purely decorative icons. |
| A11y-6 | Low | Tables | No `<caption>` or `scope` on headers; sortable/paginated tables lack status text. | Add `<caption class="visually-hidden">` and `scope="col"` on `<th>`. |
| A11y-7 | Low | Live regions | AI responses appear without announcing to SR users. | Add `aria-live="polite"` region for chat updates (AI_ASSISTANT_UI_REDESIGN). |
| A11y-8 | Low | Skip link | No "skip to main content" link. | Add skip link at top of layout. |

## 3. Keyboard Navigation
- All primary actions are buttons/links → reachable via Tab. Good.
- Drawer/modal focus management missing (A11y-1). Fix in SIDEBAR_REDESIGN.

## 4. Compliance Target
Aim for **WCAG 2.1 AA**:
- Contrast ≥ 4.5:1 text, 3:1 large text/UI components.
- All functionality operable by keyboard.
- Names/roles computed for all controls.
- Status updates announced (aria-live).

## 5. Acceptance
- [ ] `aria-label`/`aria-current` on nav; drawer focus-trapped.
- [ ] Every form control labelled.
- [ ] Visible focus ring on all interactive elements.
- [ ] Contrast passes AA with token palette.
- [ ] Chat updates in `aria-live` region.
- [ ] Skip link present.

## 6. Tooling
Add automated checks: `laravel-pint` (style), and consider `@axe-core/playwright` or `pa11y` in CI to catch regressions once the redesign lands.
