# Release Notes

## 1.0.0 — 2026-08-27

**Initial stable release** of AI SCUMS, an AI-Powered Integrated Educational
Management System.

### Highlights
- **Multi-tenancy:** shared-database, tenant-column isolation via `TenantScoped` global scope + `ResolveTenant` middleware. Supports schools, colleges, and universities from one codebase.
- **RBAC:** 6 roles (super_admin, institution_admin, accountant, teacher, student, parent) powered by spatie/laravel-permission, with per-module Policies and `authorizeResource`.
- **Modules:** Institution, Student, Teacher, Attendance, Examination, Fee, Notice, Routine, Dashboard.
- **AI Academic Assistant:** a strictly read-only, RAG-style assistant implementing a 6-step pipeline (Intent → Authorize → Retrieve → Generate → Audit) with provider abstraction (OpenAI, Claude, Gemini, Local, Mock) and 10 read-only tools, plus mandatory `AiAuditLog` for every interaction.
- **UI:** Bootstrap 5.3 with Chart.js, DataTables, FullCalendar, Select2, SweetAlert2.
- **Offline demo:** `MockProvider` + `DemoDataSeeder` make the assistant work with no API key.

### Notes
- Targets Laravel 11 / PHP 8.2.
- This release is intended for demonstration and further development. Before production, address the Critical/High items in `docs/audits/` (notably default seeded credentials, auth throttling, `TenantManager` binding, AI-tool tenant filters, and the missing test suite).

### Upgrade / Migration
- Fresh install: see `README.md` / `docs/INSTALLATION.md`.
- No prior versions exist (initial release).
