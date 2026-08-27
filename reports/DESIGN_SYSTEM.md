# Design System — AI-SCUMS

A modern, SaaS-grade design language for AI-SCUMS, inspired by **ERPNext, Linear, Notion, Stripe Dashboard, and Zoho**. Built to replace the current Bootstrap-default styling with a consistent, token-driven system.

**Required base tokens (per brief):**
- Primary: `#2563EB`
- Background: `#F8FAFC`
- Card: `#FFFFFF`
- Border Radius: `16px`
- Soft shadows + consistent spacing.

---

## 1. Color Tokens

### Brand & Accent
| Token | Value | Usage |
|-------|-------|-------|
| `--brand-600` (Primary) | `#2563EB` | Buttons, active states, links, focus rings |
| `--brand-700` | `#1D4ED8` | Hover/active primary |
| `--brand-50` | `#EFF6FF` | Subtle primary backgrounds, badges |
| `--brand-100` | `#DBEAFE` | Selected row / chip bg |

### Neutrals (Slate scale)
| Token | Value |
|-------|-------|
| `--bg` (app background) | `#F8FAFC` |
| `--surface` (card) | `#FFFFFF` |
| `--surface-2` | `#F1F5F9` (insets, wells) |
| `--border` | `#E2E8F0` |
| `--text` | `#0F172A` (slate-900) |
| `--text-muted` | `#64748B` (slate-500) |
| `--text-faint` | `#94A3B8` |

### Semantic
| Token | Value | Meaning |
|-------|-------|---------|
| `--success` | `#16A34A` | Paid / present / active |
| `--warning` | `#D97706` | Partial / pending |
| `--danger` | `#DC2626` | Overdue / absent / delete |
| `--info` | `#0EA5E9` | Informational |

### Data viz palette (Chart.js)
`#2563EB`, `#16A34A`, `#D97706`, `#DC2626`, `#0EA5E9`, `#8B5CF6`, `#64748B`.

## 2. Spacing Scale (4px base)
`--sp-1:4px · --sp-2:8px · --sp-3:12px · --sp-4:16px · --sp-5:24px · --sp-6:32px · --sp-8:48px · --sp-10:64px`
All layout uses the scale; avoid arbitrary margins.

## 3. Typography
- Font: Inter (or system UI stack) for UI; `tabular-nums` for figures.
- Scale: `display 28/32`, `h1 22/28`, `h2 18/24`, `h3 16/22`, `body 14/20`, `small 12/16`.
- Weight: 600 for headings/labels, 400 body, 500 medium buttons.

## 4. Radius
- `--radius: 16px` (cards, modals, inputs, buttons)
- `--radius-sm: 10px` (chips, badges, small controls)
- `--radius-pill: 999px` (status pills)

## 5. Elevation (soft shadows)
```
--shadow-sm: 0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06);
--shadow-md: 0 4px 12px rgba(15,23,42,.06), 0 2px 4px rgba(15,23,42,.04);
--shadow-lg: 0 12px 32px rgba(15,23,42,.10);
```
Use `--shadow-sm` for cards at rest, `--shadow-md` on hover, `--shadow-lg` for modals/drawers.

## 6. Components

### Card
```
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  padding: var(--sp-5);
}
.card:hover { box-shadow: var(--shadow-md); }
```

### Button
- `.btn-primary`: bg `--brand-600`, white text, radius 16px, padding 10×16, hover `--brand-700`, focus ring `0 0 0 3px rgba(37,99,235,.35)`.
- `.btn-ghost`: transparent, `--text-muted`, hover `--surface-2`.
- `.btn-danger`: `--danger`, white.

### Input
```
.input {
  height: 42px; padding: 0 14px;
  border: 1px solid var(--border); border-radius: var(--radius);
  background: var(--surface); color: var(--text);
}
.input:focus { border-color: var(--brand-600); box-shadow: 0 0 0 3px rgba(37,99,235,.20); outline: none; }
```

### Alert
Rounded 16px, left 4px accent stripe in semantic color, icon + message.

### Table
- Header: `--surface-2`, uppercase 12px `--text-muted`, 600.
- Rows: hover `--brand-50`; `border-bottom: 1px var(--border)`.
- Zebra optional (very light).

### Modal / Drawer
- Surface white, radius 16px, shadow-lg; scrim `rgba(15,23,42,.45)`; drawer slides from right on mobile for forms.

### Status Pill
`radius-pill`, 12px, 600 weight, tinted bg + colored text (success/warning/danger/info).

## 7. Implementation Guidance
- **Preferred:** Tailwind theme extending these tokens + Vite build (see MASTER_IMPROVEMENT_PLAN). Tokens become `theme.extend.colors.brand`, `borderRadius.DEFAULT='16px'`, `boxShadow`.
- **Minimal (no build):** define CSS variables in a `resources/css/app.css` `<style>`-free stylesheet linked locally, replacing the inline block in `app.blade.php`.
- Apply tokens uniformly; retire hard-coded `#1e293b`/`#f5f6fa` literals.

## 8. Do / Don't
- ✅ Use 16px radius everywhere; consistent 4px spacing grid.
- ✅ Brand only for primary actions; semantic colors for status.
- ❌ Don't mix raw Bootstrap blues (`#0d6efd`) with brand `#2563EB`.
- ❌ Don't use box-shadows heavier than `--shadow-lg`.
- ❌ Don't use radius < 10px on cards.

This system is the foundation for DASHBOARD_IMPROVEMENT_PLAN, SIDEBAR_REDESIGN, and AI_ASSISTANT_UI_REDESIGN.
