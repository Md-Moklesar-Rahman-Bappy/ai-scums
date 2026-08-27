<?php

declare(strict_types=1);

/*
 * Security configuration.
 *
 * Centralised, environment-overridable security knobs for the IEMS platform.
 * Values here drive login throttling, password policy and session hardening.
 */

return [

    /*
     * Login brute-force protection.
     *
     * After `login_max_attempts` failed attempts from the same key (IP + email)
     * the account/IP is locked for `login_decay_seconds`. Both dimensions are
     * tracked and the longest remaining lockout applies.
     */
    'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 60),

    /*
     * Public auth surface rate limiting (throttle named "login").
     * Format: max requests per minute.
     */
    'auth_throttle_max' => (int) env('AUTH_THROTTLE_MAX', 10),

    /*
     * Email verification resend rate limiting (throttle named "verification").
     */
    'verification_throttle_max' => (int) env('VERIFICATION_THROTTLE_MAX', 6),

    /*
     * Password policy.
     *
     * Minimum length and required character classes. Consumed in
     * AppServiceProvider via Password::defaults() and used by RegisterRequest
     * and the password reset flow.
     */
    'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 12),
    'password_mixed_case' => (bool) env('PASSWORD_MIXED_CASE', true),
    'password_numbers' => (bool) env('PASSWORD_NUMBERS', true),
    'password_symbols' => (bool) env('PASSWORD_SYMBOLS', true),
    'password_uncompromised' => (bool) env('PASSWORD_UNCOMPROMISED', true),

    /*
     * Remember-me hardening.
     *
     * When true the remember token is rotated on every successful login so a
     * stolen "remember me" cookie cannot be reused after logout.
     */
    'rotate_remember_token_on_login' => (bool) env('ROTATE_REMEMBER_TOKEN_ON_LOGIN', true),

];
