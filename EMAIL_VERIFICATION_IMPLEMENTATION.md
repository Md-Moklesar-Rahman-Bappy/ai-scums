# EMAIL VERIFICATION IMPLEMENTATION

**Phase:** 2 · **Status:** Implemented & Verified

---

## 1. Objective
Ensure every user verifies ownership of their email before reaching any ERP module,
per OWASP ASVS V2.1 and SaaS account-integrity requirements.

## 2. Changes

### 2.1 User Model — `app/Models/User.php`
- `User` now `implements Illuminate\Contracts\Auth\MustVerifyEmail`.
- Overrides `sendEmailVerificationNotification()` to dispatch the branded, queueable
  `App\Notifications\VerifyEmailNotification`.

### 2.2 Custom Notification — `app/Notifications/VerifyEmailNotification.php`
- Extends `Illuminate\Auth\Notifications\VerifyEmail`.
- Renders a branded mail message, references `config('auth.verification.expire')`
  for the expiry notice, and implements `ShouldQueue` for async delivery.

### 2.3 Routes — `routes/web.php`
New group (auth + `throttle:verification`):
```
GET  /email/verify                            verification.notice
GET  /email/verify/{id}/{hash}               verification.verify   (signed)
POST /email/verification-notification        verification.send
```
The entire authenticated module group is now wrapped in `auth` **and** `verified`.
Unverified users hitting any protected route are redirected to `verification.notice`.

### 2.4 Controller — `app/Http/Controllers/Auth/AuthController.php`
- `showVerificationNotice()` → `auth/verify.blade.php`.
- `verifyEmail()` → validates via signed middleware, calls `markEmailAsVerified()`,
  fires `Verified`, and writes `auth.email.verified` to `AuditLog`.
- `resendVerification()` → re-sends and logs `auth.email.verification_resent`.
- `register()` now sends the verification notification immediately after login.

### 2.5 View — `resources/views/auth/verify.blade.php`
Branded notice page with a "Resend Verification Email" form and logout option.

## 3. Behaviour
1. Self-registration → user logged in → redirected to `/dashboard` →
   `verified` middleware intercepts → `/email/verify` notice.
2. Verification email (signed link, 60-min expiry) → click → email verified →
   dashboard unlocked.
3. Resend limited by `throttle:verification` (default 6/min).

## 4. Configuration
| Key | Default | Purpose |
|-----|---------|---------|
| `auth.verification.expire` (config/auth.php) | 60 | Link lifetime (minutes) |
| `VERIFICATION_THROTTLE_MAX` | 6 | Resend attempts / minute |

## 5. Testing Checklist
- [ ] Unverified user cannot open `/students`, `/dashboard`, `/assistant`.
- [ ] Signed link verifies exactly the authenticated user.
- [ ] Expired/​tampered link → 403 (invalid signature).
- [ ] Resend respects throttle.
- [ ] Verified event appears in `audit_logs`.
