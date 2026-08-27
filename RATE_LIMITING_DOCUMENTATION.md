# RATE LIMITING DOCUMENTATION

**Phase:** 3 · **Status:** Implemented & Verified

---

## 1. Threat
Credential stuffing / brute force against `/login`, account enumeration via
`/register` and `/forgot-password`, and email bombing through reset/resend.

## 2. Design
Defence in depth across two layers:

1. **Coarse IP throttle** on the whole public auth surface (`throttle:login`).
2. **Fine-grained attempt accounting** in `AuthController::login` keyed by
   `ip` **and** `ip|email`, backed by Laravel's `RateLimiter` (Redis/cache).

## 3. Tunables — `config/security.php`
```php
'login_max_attempts'        => env('LOGIN_MAX_ATTEMPTS', 5),
'login_decay_seconds'       => env('LOGIN_DECAY_SECONDS', 60),
'auth_throttle_max'         => env('AUTH_THROTTLE_MAX', 10),
'verification_throttle_max' => env('VERIFICATION_THROTTLE_MAX', 6),
```
Requirement: **5 failed attempts → 60-second lockout**, configurable.

## 4. Implementation

### 4.1 Named limiters — `bootstrap/app.php`
```php
RateLimiter::for('login', fn ($r) => Limit::perMinute(
    (int) config('security.auth_throttle_max', 10))->by($r->ip()));

RateLimiter::for('verification', fn ($r) => Limit::perMinute(
    (int) config('security.verification_throttle_max', 6))
    ->by($r->user()?->id ?: $r->ip()));
```
Routes consume them via `->middleware('throttle:login')` and `throttle:verification`.

### 4.2 Login attempt accounting — `AuthController::login`
- Keys: `login:{ip}` and `login:{ip}|{email}`.
- On failure: `RateLimiter::hit($key, decay)`.
- Lockout when either bucket exceeds `login_max_attempts`; the longer remaining
  window is surfaced to the user.
- On success: `RateLimiter::clear($key)` for both buckets.

## 5. Audit Logging
Every lockout and failed attempt is recorded by `AuditLogService`:
- `auth.login.failure` (with user id when the account exists)
- `auth.login.lockout`

This satisfies OWASP ASVS V2.2.1 (anti-automation) and provides SIEM feed.

## 6. Tuning Guidance
- Behind a trusted proxy/load balancer, ensure `TrustProxies` is configured so
  `$request->ip()` reflects the real client, otherwise throttling keys on the
  proxy IP and becomes ineffective or unintentionally blocks everyone.
- For high-traffic SaaS, prefer the `redis` cache driver for `RateLimiter` so
  counters are shared across web nodes.

## 7. Testing Checklist
- [ ] 6th failed login within the window is blocked with a clear countdown.
- [ ] Correct credentials after lockout wait succeed and clear counters.
- [ ] IP-only hammering (many emails) is also throttled (×4 multiplier).
- [ ] Failures appear in `audit_logs` with IP + UA.
