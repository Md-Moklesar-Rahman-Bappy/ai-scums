# Changelog

All notable changes to AI SCUMS are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Enterprise audit documentation set under `docs/audits/` (architecture, best-practices, database, security, AI, UI/UX, bugs, performance, test-coverage).
- Offline `MockProvider` for key-free assistant demos/tests.
- Demo data seeder (`DemoDataSeeder`) producing a realistic dataset.
- Model factories for all major modules.

### Fixed (planned — see `docs/audits/remediation-plan.md`)
- P0: Bind `TenantManager` as scoped; secure seeded default credentials.
- P1: Auth throttling, `User.$fillable` hardening, explicit AI-tool tenant filters, assistant `mock` validation, `InstitutionController::show`.

## [1.0.0] - 2026-08-27

### Added
- Multi-tenant Laravel 11 platform (school / college / university).
- Auth (login, register, forgot/reset) with spatie/laravel-permission RBAC (6 roles).
- Modules: Institution, Student, Teacher, Attendance, Examination, Fee, Notice, Routine, Dashboard.
- AI Academic Assistant: 6-step pipeline (Intent → Authorize → Retrieve → Generate → Audit) with provider abstraction (OpenAI, Claude, Gemini, Local, Mock) and 10 read-only tools.
- Tenant isolation via `TenantScoped` global scope + `ResolveTenant` middleware.
- Bootstrap 5.3 UI with Chart.js, DataTables, FullCalendar, Select2, SweetAlert2.

[Unreleased]: https://github.com/Md-Moklesar-Rahman-Bappy/ai-scums/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Md-Moklesar-Rahman-Bappy/ai-scums/releases/tag/v1.0.0
