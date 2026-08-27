# 🌐 Deployment Guide — AI SCUMS

Guidance for deploying AI SCUMS to a production environment. Treat as a baseline; adapt to your hosting (shared VPS, Docker, Forge, etc.).

## 1. Server Requirements

- PHP 8.2+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`.
- MySQL 8.0 (recommended).
- Web server: Nginx or Apache with HTTPS.
- (Optional) Redis for cache/queue/session at scale.

## 2. Build & Deploy Steps

```bash
git clone <repo> /var/www/ai-scums
cd /var/www/ai-scums
composer install --no-dev --optimize-autoloader
cp .env.example .env && nano .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link   # if file uploads are added later
```

Set web root to `public/` and deny access to everything else.

### Nginx (excerpt)
```nginx
server {
    listen 443 ssl http2;
    server_name iems.example.com;
    root /var/www/ai-scums/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { include fastcgi_params; fastcgi_pass unix:/run/php/php8.2-fpm.sock; }
}
```

## 3. Production Environment Hardening

In `.env` / config:
- `APP_ENV=production`, `APP_DEBUG=false`.
- `SESSION_SECURE_COOKIE=true`, consider `SESSION_ENCRYPT=true`.
- Strong unique `APP_KEY`.
- `AI_PROVIDER=mock` or `local` for regulated/self-hosted; restrict cloud providers to admins (see below).
- DB credentials from secrets, not committed.

## 4. AI Provider & Data Egress

- For **FERPA/GDPR-sensitive** deployments, prefer the **offline `MockProvider`** or a **self-hosted `Local`** (Ollama) provider.
- If using cloud providers, **do not allow end users to select the provider** (current `AssistantAskRequest` permits it — a known gap, see `security-report.md` F-10 / `ai-review.md` A16). Restrict provider choice to administrators and minimize PII sent to third parties; ensure DPAs/consent.
- Define **retention & access control** for `ai_audit_logs` (contains queries/responses/PII).

## 5. Queue & Performance (recommended at scale)

- Configure a real queue connection (`redis`/`database`) and run workers: `php artisan queue:work`.
- Move LLM generation to a queued job (currently synchronous — see `performance-report.md` P-3).
- Enable caching for dashboard/calendar aggregates.
- Add the missing indexes from `database-review.md` §3.

## 6. Tenant Isolation (must verify)

Before go-live, confirm:
- `TenantManager` is bound as `scoped` (P0 fix applied) — super-admin switching and writes correct.
- Tenant scope is **fail-closed** (DB-1 fix).
- `institution_id` removed from mass-assignable `$fillable` where not needed (F-7).
- `ai_feedback`, pivots, and AI/audit tables carry `institution_id` (DB-3/4/5).

## 7. Security Pre-Flight

- Remove/secure seeded default credentials (`superadmin@iems.test`, `admin@demo.test`).
- Add rate limiting / account lockout to auth endpoints (F-2).
- Tighten `User.$fillable` (remove `is_super_admin`, `institution_id`, `is_active`) (F-4).
- Enable HTTPS + HSTS.

## 8. Backups

- Schedule `mysqldump` (or managed snapshots) of `ai_scums`.
- Test restore periodically.

See also `SECURITY.md`, `ARCHITECTURE.md`, and `docs/audits/`.
