<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Auth\RegistrationService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * AuthController.
 *
 * Thin controller exposing login, registration, logout, email verification and
 * password reset. Business logic for onboarding is delegated to
 * {@see RegistrationService}. All security-sensitive events are recorded via
 * {@see AuditLogService}.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registration,
        private readonly AuditLogService $audit,
    ) {}

    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate a user.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $email = (string) $request->input('email');
        $ipKey = 'login:'.$request->ip();
        $userKey = 'login:'.$request->ip().'|'.strtolower($email);

        // Brute-force lockout: block once either the IP or the email+IP bucket
        // has exhausted its allowance (see config/security.php).
        if (RateLimiter::tooManyAttempts($userKey, (int) config('security.login_max_attempts', 5))
            || RateLimiter::tooManyAttempts($ipKey, (int) config('security.login_max_attempts', 5) * 4)) {
            $seconds = max(
                RateLimiter::availableIn($userKey),
                RateLimiter::availableIn($ipKey)
            );
            $this->audit->log('auth.login.lockout', ['email' => $email]);

            return back()
                ->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credentials = $request->validated(), $request->boolean('remember'))) {
            RateLimiter::hit($userKey, (int) config('security.login_decay_seconds', 60));
            RateLimiter::hit($ipKey, (int) config('security.login_decay_seconds', 60));

            $user = User::where('email', $email)->first();
            $this->audit->log('auth.login.failure', [
                'email' => $email,
                'model_type' => $user ? User::class : null,
                'model_id' => $user?->id,
            ]);

            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        // Success: clear the failed-attempt counters.
        RateLimiter::clear($userKey);
        RateLimiter::clear($ipKey);

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Rotate the remember-me token on every successful login so a stolen
        // "remember me" cookie cannot be replayed after logout.
        if (config('security.rotate_remember_token_on_login', true)) {
            $user->setRememberToken(Str::random(60));
            $user->save();
        }

        $user->update(['last_login_at' => now()]);

        if (! $user->is_active && ! $user->isSuperAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Account is inactive.']);
        }

        $this->audit->log('auth.login.success', [
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $this->audit->log('auth.logout', [
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new institution and its admin.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->registration->registerInstitution($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        // Send the verification email unless already verified.
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('dashboard')
            ->with('success', 'Institution created. Please verify your email to continue.');
    }

    /**
     * Show the forgot-password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        $this->audit->log('auth.password.reset_request', ['email' => $request->input('email')]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the reset-password form.
     */
    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', ['token' => $request->route('token'), 'email' => $request->email]);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password): void {
                $user->forceFill(['password' => Hash::make($password)])->save();

                $user->setRememberToken(Str::random(60));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->input('email'))->first();
            $this->audit->log('auth.password.reset_success', [
                'model_type' => $user ? User::class : null,
                'model_id' => $user?->id,
            ]);
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the email verification notice.
     */
    public function showVerificationNotice(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('auth.verify', [
            'verified' => $user->hasVerifiedEmail(),
        ]);
    }

    /**
     * Verify the email address from the signed verification link.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $this->audit->log('auth.email.verified', [
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Your email has been verified.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $user->sendEmailVerificationNotification();

        $this->audit->log('auth.email.verification_resent', [
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return back()->with('success', 'A fresh verification link has been sent to your email.');
    }
}
