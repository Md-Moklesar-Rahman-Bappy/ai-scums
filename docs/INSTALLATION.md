# 🚀 Installation Guide — AI SCUMS

Step-by-step setup for local development and evaluation.

## 1. Requirements

| Component | Version |
|-----------|---------|
| PHP | ≥ 8.2 |
| Composer | ≥ 2.x |
| MySQL | 5.7+ / 8.0+ (XAMPP bundles MySQL) |
| Node.js | ≥ 18 (optional; assets are CDN-loaded) |
| Git | any |

## 2. Clone & Install

```bash
git clone https://github.com/Md-Moklesar-Rahman-Bappy/ai-scums.git
cd ai-scums
composer install
```

## 3. Environment

```bash
cp .env.example .env        # or use the provided .env
php artisan key:generate
```

Key `.env` values:

```env
APP_NAME="AI-Powered IEMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_scums
DB_USERNAME=root
DB_PASSWORD=

# AI Assistant — default is the offline Mock provider (no key needed)
AI_PROVIDER=mock
# AI_OPENAI_KEY=sk-...
# AI_CLAUDE_KEY=
# AI_GEMINI_KEY=
# AI_LOCAL_ENDPOINT=http://localhost:11434/v1/chat/completions
```

## 4. Database

Ensure MySQL is running (e.g. XAMPP). Create the database if needed:

```sql
CREATE DATABASE ai_scums CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations and seed:

```bash
php artisan migrate:fresh --seed
```

This creates:
- All schema (soft deletes, tenant columns, FKs, indexes).
- RBAC roles/permissions (`RolesAndPermissionsSeeder`).
- A platform super admin and a demo institution (`DatabaseSeeder`).
- A rich demo dataset (`DemoDataSeeder`): academic years, classes, sections, subjects, students, teachers, attendance history, exams + marks, fees, notices, routines.

## 5. Run

```bash
php artisan serve
# open http://localhost:8000
```

## 6. Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@iems.test` | `password` |
| Institution Admin (Demo School) | `admin@demo.test` | `password` |

> ⚠️ **Change these before any non-local deployment.** See `SECURITY.md`.

## 7. Verify

- Dashboard loads with charts.
- Students/Teachers/Attendance/Exams/Fees/Notices/Routines CRUD works.
- AI Assistant (`/assistant`) answers role-aware queries (Mock provider by default).

## 8. Troubleshooting

- **Migrations fail on MySQL:** ensure `utf8mb4` and sufficient key length.
- **Assistant returns "unable to generate":** with `AI_PROVIDER=mock` it should work; for cloud providers ensure the key is set and reachable.
- **Tenant issues for super admin:** see known limitation in `ARCHITECTURE.md` (being fixed in P0).
