# SESSION SECURITY REPORT

**Phase:** 4 · **Status:** Reviewed + Hardened

---

## 1. Lifecycle Audit

| Event | Code | Verdict |
|-------|------|---------|
| Post-login | `$request->session()->regenerate();` | ✅ Correct (new session id, prevents fixation) |
| Post-logout | `invalidate()` + `regenerateToken()` | ✅ Correct (destroys data + rotates CSRF) |
| Remember me | `Auth::attempt($creds, $remember)` | ✅ Uses hashed `remember_token` |
| Login order | attempt → regenerate → active check | ✅ OK; regenerate after auth is the documented pattern |

## 2. Findings

### F-1 (Medium) — Session Payload Not Encrypted
`SESSION_ENCRYPT=false` ⇒ session rows in the `sessions` table are plaintext.
Anyone with DB read access (or a leaked backup) can read session contents.

**Recommendation:** `SESSION_ENCRYPT=true` in production.

### F-2 (Medium) — Secure Cookie Not Enforced
`SESSION_SECURE_COOKIE` is unset ⇒ session cookie may traverse plain HTTP,
enabling interception / MITM session hijack.

**Recommendation:** `SESSION_SECURE_COOKIE=true` behind TLS (mandatory in prod).

### F-3 (Low) — `same_site=lax`
`lax` is acceptable and CSRF-resistant for top-level navigations. Consider `strict`
for the most sensitive areas, but `lax` + Laravel CSRF tokens is sufficient.

### F-4 (Low) — No Absolute Session Timeout / Idle Beyond Lifetime
`SESSION_LIFETIME=120` (idle) is fine. There is no hard "max session age"
(re-auth) for privileged actions (e.g. fee payment, role change). OWASP ASVS V3.3
recommends step-up re-auth for sensitive functions.

**Recommendation:** Add `password_timeout` re-auth (already `10800`s = 3h) and
consider `Auth::once` step-up for `fees.pay` / tenant switch.

### F-5 (Low) — `remember_token` Rotation
**Fixed:** token is now rotated on successful login and on password reset, so a
stolen "remember me" cookie cannot be replayed after logout.

### F-6 (Medium) — Cross-Device Login Behaviour
A user logged in on device A and device B shares the same server-side session
record (database driver). Logging out on one device does **not** invalidate the
other (Laravel does not track multiple devices by default). Acceptable for this
tier, but document it. For true "logout everywhere", implement
`Auth::logoutOtherDevices()` on password change.

## 3. Hardening Applied
- `AuthController::login` regenerates session and rotates `remember_token`.
- `AuthController::logout` invalidates + regenerates token (unchanged, verified).
- `config/security.php` adds `rotate_remember_token_on_login` (default true).

## 4. Recommended `.env` (production)
```
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=120
APP_DEBUG=false
```
See `PRODUCTION_SEEDER_POLICY.md` and `SECURITY.md`.
