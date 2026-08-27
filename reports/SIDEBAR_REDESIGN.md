# Sidebar Redesign — AI-SCUMS

**Goal:** Replace the hard-coded, `d-none d-md-block` Bootstrap sidebar with a **collapsible, icon-driven, grouped, role-aware** sidebar that works on mobile and desktop, matching the DESIGN_SYSTEM tokens.

---

## 1. Current State (problems)
- Inline in `resources/views/components/layouts/app.blade.php:24-54`.
- `class="... d-none d-md-block"` → **no nav on mobile** (see RESPONSIVE_REPORT U1).
- Hard-coded colours (`#1e293b`, `#cbd5e1`).
- Links are plain `<a>` with `@role`/`@hasanyrole` — functional but not a component, no collapse, no grouping, no active-state beyond `.active` class.

## 2. Target Design

### Structure
```
[Brand / Logo]                      (collapsible)
─────────────────────────
GENERAL
  Dashboard
  AI Assistant
MANAGE (admin/teacher)
  Students   Teachers   Attendance
  Examinations  Routines  Fees
  Notices
SYSTEM (super_admin)
  Institutions
─────────────────────────
[User chip + logout]
```
- **Grouping** by domain with small uppercase labels (12px, `--text-muted`).
- **Icons** via Bootstrap Icons (already loaded) or Lucide.
- **Active state:** brand-colored left bar + `--brand-50` background + `--text` (not grey).
- **Collapsible:** desktop toggle collapses to icon-rail (64px); expands to 260px. Persist preference in `localStorage`.
- **Mobile:** off-canvas drawer triggered by a hamburger in the top navbar; backdrop closes it; body scroll locked.

### Token mapping
- Background: `#0F172A` (slate-900) or, to align with light theme, white with `--border` + soft shadow. Recommend a **light sidebar** with `--surface` and subtle `--border` for a Linear/Notion feel, OR a deep slate rail. Choose the slate-900 rail for contrast; text `#E2E8F0`, active `#2563EB` accent.
- Radius: cards inside use 16px via DESIGN_SYSTEM.

## 3. Implementation Sketch (Blade component)
Create `resources/views/components/sidebar.blade.php` and a `resources/views/components/navbar.blade.php` with hamburger. Move role logic into a small `app/Helpers/NavMenu.php` returning grouped, permission-filtered items so the view stays declarative.

```blade
@foreach($navGroups as $group => $items)
  <div class="nav-group-label">{{ $group }}</div>
  @foreach($items as $item)
    <a href="{{ route($item['route']) }}"
       class="nav-link @if(request()->routeIs($item['route'].'*')) active @endif">
      <i class="{{ $item['icon'] }}"></i>
      <span class="label">{{ $item['label'] }}</span>
    </a>
  @endforeach
@endforeach
```

### Mobile drawer (minimal)
```html
<div class="offcanvas offcanvas-start" id="sidebarDrawer" ...>@include('components.sidebar')</div>
<!-- navbar -->
<button class="d-md-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarDrawer"><i class="bi bi-list"></i></button>
```

## 4. Accessibility (see ACCESSIBILITY_REPORT)
- `<nav aria-label="Primary">`, `aria-current="page"` on active link, focus-visible outline, `Escape` closes drawer, focus trap inside drawer.

## 5. Rollout
1. Extract sidebar to component + `NavMenu` helper.
2. Add collapse state (desktop) + off-canvas (mobile).
3. Apply DESIGN_SYSTEM tokens (remove `#1e293b` literals).
4. Add active/hover/focus states.
5. Verify at 320/375/768/1024/1440 (RESPONSIVE_REPORT).

## 6. Acceptance Criteria
- [ ] Reachable on 320px (hamburger → drawer).
- [ ] Collapsible on desktop with persisted state.
- [ ] Role-specific items only (no `@role` in raw Blade).
- [ ] Active item visually distinct + `aria-current`.
- [ ] Zero hard-coded colours; uses tokens.
