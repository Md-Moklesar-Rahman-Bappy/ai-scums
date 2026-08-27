# AI Assistant UI Redesign — AI-SCUMS

**Goal:** Transform the assistant from a single flat chat box into a modern, ChatGPT/Claude/Gemini-inspired experience with conversation history, suggested prompts, typing indicator, and transparent badges — using DESIGN_SYSTEM tokens.

---

## 1. Current State (`resources/views/assistant/index.blade.php`)
- One bordered `#fff` scroll box, plain text bubbles (`textContent`).
- 4 static quick-prompt buttons.
- Provider `<select>` for super-admins.
- On response: `answer + '  [' + intent + ']'` appended — no structured badges.

## 2. Target Experience

### Layout
```
┌───────────────────────────────────────────────┐
│ Header: AI Academic Assistant  ·  read-only badge│
├───────────────┬───────────────────────────────┤
│ History (coll.)│  Chat thread                   │
│ - "Attendance" │   ● User bubble (right)        │
│ - "Next exam"  │   ● Bot bubble (left, card)    │
│                │       [intent][role][source]    │
│                │       [audit ✓ logged]         │
│                │   ● … typing indicator …       │
├───────────────┴───────────────────────────────┤
│ Suggested: [Attendance][Next exam][CGPA][Fees]  │
│ [ input............................ ] [Ask ➤]   │
└───────────────────────────────────────────────┘
```

### Features
1. **Conversation History** — persist threads via `ai_conversations` (already exists: user_id, institution_id, messages json). Left rail lists past conversations; click to reload. Store each turn (user + bot + metadata) as JSON.
2. **Suggested Prompts** — role-aware chips generated from `AuthorizationGate::intentsForRole($role)`; clicking sends immediately.
3. **Typing Indicator** — three-dot animated bubble shown after POST until response resolves (replace the disabled-button-only UX).
4. **Intent Badge** — pill showing detected intent (e.g. `intent: student_attendance`) using `--brand-50` bg.
5. **Role Badge** — shows the assistant's resolved role context (`serving: student`).
6. **Source Badge** — the tool used (`tool: student_attendance`); tooltip explains "read from your institution's attendance records".
7. **Audit Badge** — small "✓ logged" indicator confirming the turn was written to `ai_audit_logs` (builds trust/transparency).
8. **Markdown rendering** — render bot answers as markdown (light sanitised renderer) instead of plain text; keep `textContent` only if no renderer.

### Visual tokens
- Bot bubble: `.card` surface, radius 16px, `--shadow-sm`, max-width 75%.
- User bubble: `--brand-600` bg, white text, radius 16px.
- Badges: `--radius-pill`, 12px, semantic tints.
- Header: read-only shield badge (`#16A34A`) — reinforces safety.

## 3. Implementation Notes
- Keep the existing `assistant.ask` AJAX contract; extend `AssistantResponse` DTO to include `role`, `source`/`tool`, `audit_id`. (DTO already returns `intent, tool, context, tokens, provider`.)
- Add `GET /assistant/history` and `POST /assistant/conversation` (store thread) — gated by `assistant.use`.
- Use the same `axios` + CSRF setup already present.
- Respect the design system; no Bootstrap-default blues.

## 4. Acceptance Criteria
- [ ] History rail with past conversations (persisted).
- [ ] Role-aware suggested prompts.
- [ ] Typing indicator during requests.
- [ ] Intent / Role / Source / Audit badges on each bot message.
- [ ] Markdown answer rendering (sanitised).
- [ ] Fully responsive (history collapses to a toggle on mobile).
- [ ] No data leaves tenant scope; audit log still written per turn.
