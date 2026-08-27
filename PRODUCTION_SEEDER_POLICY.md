# PRODUCTION SEEDER POLICY

**Phase:** 5 · **Status:** Implemented & Verified

---

## 1. Problem
`DatabaseSeeder` unconditionally created:
- `superadmin@iems.test` / `password`
- `admin@demo.test` / `password`

and then ran `DemoDataSeeder`. Shipping this to production is an immediate
credential-exposure and data-pollution risk (OWASP ASVS V2.2 / CWE-798).

## 2. Policy
| Environment | RBAC seed | Demo accounts | Demo data |
|-------------|-----------|---------------|-----------|
| `local` | ✅ | ✅ (random 24-char pw) | ✅ |
| `staging` | ✅ | ✅ (random pw) | ✅ |
| `production` | ✅ | ❌ **NEVER** | ❌ **NEVER** |

In production only `RolesAndPermissionsSeeder` runs. Real accounts are created
through the self-registration / invitation flow (which now enforces email
verification and the strong password policy).

## 3. Implementation — `database/seeders/DatabaseSeeder.php`
```php
if (! app()->environment('local', 'staging')) {
    $this->command->info('Production: skipping demo accounts and data.');
    return;
}
```
- Demo passwords are generated with `Str::random(24)` unless explicitly supplied
  via `DEMO_SUPERADMIN_PASSWORD` / `DEMO_ADMIN_PASSWORD` env vars.
- Credentials are printed **once** to the console in `local` only.
- Emails are overridable via `DEMO_SUPERADMIN_EMAIL` / `DEMO_ADMIN_EMAIL`.

## 4. CI / Deployment Guardrail
Add a deployment assertion (optional) so a production seed can never create demo
users:
```php
// in DatabaseSeeder::run()
throw_unless(
    ! app()->environment('production'),
    new RuntimeException('Demo seeding must not run in production.')
);
```
(Not added to avoid breaking legitimate local runs; the early-return above is
sufficient and is the enforced control.)

## 5. Operator Guidance
- Never set `DEMO_*_PASSWORD=password` in any shared/staging environment.
- Rotate the super-admin password immediately after first production login.
- Prefer SSO / SCIM provisioning for production admins over seeded accounts.
