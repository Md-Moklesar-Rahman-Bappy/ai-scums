# PASSWORD POLICY

**Phase:** 10 · **Status:** Implemented & Verified

---

## 1. Requirement
- Minimum **12 characters**.
- Must contain: uppercase, lowercase, number, symbol.
- Reject compromised (breached) passwords (optional but enabled).
- Applied to **registration** and **password reset**.

## 2. Implementation

### 2.1 Central Definition — `app/Providers/AppServiceProvider.php`
```php
$rule = PasswordRule::min((int) config('security.password_min_length', 12));
if (config('security.password_mixed_case'))   $rule->mixedCase();
if (config('security.password_numbers'))       $rule->numbers();
if (config('security.password_symbols'))       $rule->symbols();
if (config('security.password_uncompromised')) $rule->uncompromised();
PasswordRule::defaults($rule);
```
This makes `Password::defaults()` the single source of truth consumed everywhere.

### 2.2 Consumers (already using `Password::defaults()`)
- `app/Http/Requests/Auth/RegisterRequest.php` → `password` rule.
- `app/Http/Controllers/Auth/AuthController.php::resetPassword()` →
  `Password::defaults()` on the reset rule.

### 2.3 Tunables — `config/security.php`
```php
'password_min_length'      => env('PASSWORD_MIN_LENGTH', 12),
'password_mixed_case'      => env('PASSWORD_MIXED_CASE', true),
'password_numbers'         => env('PASSWORD_NUMBERS', true),
'password_symbols'         => env('PASSWORD_SYMBOLS', true),
'password_uncompromised'   => env('PASSWORD_UNCOMPROMISED', true),
```

## 3. Notes
- `uncompromised()` performs a k-anonymity check against the HaveIBeenPwned
  range API. If outbound network is restricted in your environment, set
  `PASSWORD_UNCOMPROMISED=false` or proxy the request. The check fails **closed**
  (registration/reset error) if the API is unreachable, which is the safe default.
- Bcrypt rounds remain `BCRYPT_ROUNDS=12` and the `User` model casts `password`
  as `hashed`, so rehash-on-upgrade is automatic.

## 4. Testing Checklist
- [ ] `password` < 12 chars → validation error.
- [ ] Missing any of upper/lower/number/symbol → error.
- [ ] Known-breached password (e.g. `P@ssw0rd123!`) → `uncompromised` error.
- [ ] Valid 12+ complex password → success on register and reset.
