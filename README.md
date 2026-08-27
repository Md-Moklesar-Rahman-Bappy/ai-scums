# 🎓 AI SCUMS
### AI-Powered School, College & University Management System (IEMS)

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-purple?style=for-the-badge&logo=bootstrap" alt="Bootstrap 5.3">
  <img src="https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/AI-Assistant-green?style=for-the-badge&logo=openai" alt="AI Assistant">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="MIT License">
</p>

<p align="center">
  <b>A multi-tenant, role-aware Educational Management Platform with a strictly read-only, RAG-based AI Academic Assistant.</b>
</p>

---

## 📖 Project Overview

**AI SCUMS** (AI-powered School, College & University Management System) is a production-grade educational ERP built on **Laravel 11** (PHP 8.2) with **MySQL**. A single codebase serves three institution types — schools, colleges and universities — using a shared-database, tenant-column multi-tenancy strategy.

The distinguishing research contribution is the **AI Academic Assistant**: a role-aware, strictly read-only assistant that answers academic and administrative questions by detecting intent, authorizing the query against the user's role, retrieving data through a registry of read-only tools, generating a response with a pluggable LLM provider, and logging every interaction for auditability.

> **Implementation note:** The project targets **Laravel 11 / PHP 8.2** (the available toolchain). The architecture is forward-compatible and documented accordingly.

---

## ✨ Features

| Module | Capabilities |
|--------|--------------|
| **Auth & RBAC** | Login/register/forgot/reset, 6 roles (super_admin, institution_admin, accountant, teacher, student, parent), spatie/laravel-permission. |
| **Institution** | Tenant root entity; super admin can create institutions and switch active tenant. |
| **Student** | Admission, profiles, promotion, guardian linkage, soft deletes. |
| **Teacher** | Profiles, department linkage, subject allocation. |
| **Attendance** | Daily marking (present/absent/late/half_day), per-student analytics. |
| **Examination** | Exams per subject/section, marks entry, auto grade derivation, result summary. |
| **Fee** | Fee types, assignment, payment recording, status recomputation, due report. |
| **Notice** | Announcements & calendar events (FullCalendar). |
| **Routine** | Weekly class/exam schedule (FullCalendar). |
| **Dashboard** | Chart.js overview (students, teachers, fees, attendance). |
| **AI Assistant** | 6-step pipeline (Query → Intent → Authorize → Retrieve → Generate → Audit). |

---

## 📸 Screenshots

> Screenshots are maintained in the [`docs/screenshots/`](docs/screenshots/) directory (to be added). Representative views:
>
> - **Dashboard** — aggregate KPIs with Chart.js (students, teachers, fee status, attendance trend).
> - **Students** — searchable DataTables list, admission form, profile/show with attendances, marks, fees.
> - **Attendance** — mark-by-section view + analytics with attendance percentage.
> - **Examinations** — exam creation, marks entry grid, result summary.
> - **Fees** — assignment, payment recording, due report.
> - **Routines / Notices** — FullCalendar weekly schedule and event calendar.
> - **AI Assistant** — chat UI with provider selection and role-aware answers.

---

## 🏗️ Architecture

Clean, SOLID structure: **Controllers → Services → Repositories → Models**, with **Policies**, **Form Requests**, **Middleware**, and **DTOs**.

```text
app/
 ├── Models/                 # BaseModel (abstract) + tenant-scoped models
 │    ├── Concerns/TenantScoped.php
 │    └── BaseModel.php
 ├── Services/
 │    ├── Tenant/TenantManager.php
 │    ├── AI/                # Assistant pipeline
 │    │    ├── AssistantService.php
 │    │    ├── AIProviderManager.php
 │    │    ├── Intent.php / IntentDetector.php
 │    │    ├── AuthorizationGate.php
 │    │    ├── ToolRegistry.php
 │    │    ├── Providers/    # OpenAI, Claude, Gemini, Local, Mock
 │    │    └── Tools/        # 10 read-only AIDataTool implementations
 │    ├── *Service.php        # Per-module business logic
 │    └── *Repository (app/Repositories)
 ├── Http/
 │    ├── Controllers/        # Thin controllers
 │    ├── Requests/           # Form request validation
 │    └── Middleware/ResolveTenant.php
 ├── Policies/               # Per-module authorization
 ├── DTOs/AI/AssistantResponse.php
 └── Providers/AIAssistantServiceProvider.php

resources/views/             # Blade + Bootstrap 5.3
database/                    # Migrations, seeders, factories
config/ai.php                # AI provider configuration
routes/web.php               # Web routes
```

Full architecture detail: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## 🛠️ Technology Stack

- **Backend:** Laravel 11, PHP 8.2, Eloquent ORM
- **Frontend:** Bootstrap 5.3, Chart.js, DataTables, FullCalendar, Select2, SweetAlert2, Axios
- **Database:** MySQL
- **Auth/RBAC:** spatie/laravel-permission
- **AI:** Provider abstraction over OpenAI / Claude / Gemini / Local (Ollama) / Mock

---

## 🚀 Installation Guide

### Prerequisites
PHP ≥ 8.2, Composer, MySQL (e.g. XAMPP), Node.js (optional for build).

### 1. Install dependencies
```bash
composer install
```

### 2. Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database setup
Configure `.env`:
```env
DB_CONNECTION=mysql
DB_DATABASE=ai_scums
DB_USERNAME=root
DB_PASSWORD=
```

### 4. AI Assistant setup
The app ships with the **offline `MockProvider`** as the default — the assistant works with **no API key**. To use a real LLM:
```env
AI_PROVIDER=openai
AI_OPENAI_KEY=sk-...
# AI_CLAUDE_KEY=..., AI_GEMINI_KEY=..., AI_LOCAL_ENDPOINT=...
```

### 5. Migrate & seed
```bash
php artisan migrate:fresh --seed
```
Creates schema, RBAC, a super admin, a demo school, and rich demo data.

### 6. Serve
```bash
php artisan serve
```

**Demo credentials**

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@iems.test` | `password` |
| Institution Admin (Demo School) | `admin@demo.test` | `password` |

> ⚠️ Change seeded credentials before any production deployment (see `SECURITY.md`).

Full guide: [`docs/INSTALLATION.md`](docs/INSTALLATION.md) · Deployment: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## 🤖 AI Assistant

The assistant is **strictly read-only** — tools never mutate marks, attendance, admissions or payments. Pipeline:

```text
1. User Query
2. Intent Detection ──► IntentDetector (keyword/regex, explainable)
3. Authorization Gate ──► AuthorizationGate (role → intent map, read-only)
4. Data Retrieval ──► ToolRegistry → read-only AIDataTool (tenant-scoped)
5. Response Generation ──► AIProviderManager → LLM (OpenAI/Claude/Gemini/Local/Mock)
6. Audit Logging ──► AiAuditLog
```

| Role(s) | Example | Tool |
|---------|---------|------|
| Student | "What is my attendance?" | `StudentAttendanceTool` |
| Student | "When is my next exam?" | `StudentNextExamTool` |
| Student | "What is my CGPA / marks?" | `StudentCgpaTool` |
| Student | "Show my class schedule." | `StudentScheduleTool` |
| Teacher | "Show students with low attendance." | `TeacherLowAttendanceTool` |
| Teacher | "Course performance analysis." | `TeacherCoursePerformanceTool` |
| Teacher | "What evaluations are pending?" | `TeacherPendingEvaluationsTool` |
| Admin/Accountant | "Show outstanding fees." | `AdminOutstandingFeesTool` |
| Admin | "Admission statistics." | `AdminAdmissionStatsTool` |
| Admin | "Enrollment report." | `AdminEnrollmentReportTool` |

Full AI docs: [`docs/AI_ASSISTANT_DOCUMENTATION.md`](docs/AI_ASSISTANT_DOCUMENTATION.md).

---

## 🔌 API Documentation

A REST API (Sanctum) for mobile/React clients is **planned** (see Roadmap). Current release is web-only; the service layer is API-ready.
Draft spec: [`docs/API_DOCUMENTATION.md`](docs/API_DOCUMENTATION.md).

---

## 🧪 Testing

```bash
php artisan test
```
Feature/unit tests cover auth, tenant isolation, RBAC, and the AI pipeline. See [`docs/audits/test-coverage-report.md`](docs/audits/test-coverage-report.md).

---

## 🔐 Security

- Multi-tenancy via `TenantScoped` global scope on every domain model.
- RBAC (spatie) + policies + `authorizeResource` + `assistant.use` gate.
- Assistant is read-only and audit-logged; deterministic intent detection.
- Responsible disclosure: `SECURITY.md`. Audit findings: [`docs/audits/`](docs/audits/).

---

## 🗺️ Roadmap

- Predictive student analytics & at-risk detection
- Recommendation engine for interventions
- REST API layer (Sanctum) for mobile / React frontends
- Voice & multilingual assistant
- Natural-language report generation

Full roadmap: [`ROADMAP.md`](ROADMAP.md).

---

## 🤝 Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for code standards, branch strategy, PR/commit conventions, and issue templates.

---

## 📄 License

MIT — see [`LICENSE`](LICENSE).

---

## 📞 Contact

**Author:** Md Moklesar Rahman
**Email:** md.moklasarrahmanbappy@gmail.com
**Phone:** +8801965031371

---

**AI SCUMS — Shaping the Future of Educational Management with Artificial Intelligence.**
