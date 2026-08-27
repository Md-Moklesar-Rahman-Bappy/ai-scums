<?php

namespace App\Providers;

use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The TenantManager holds the active institution (tenant) for the
        // current request in memory. It MUST be resolved as a scoped binding
        // so that the instance set by the ResolveTenant middleware (e.g. on a
        // super-admin tenant switch) is the SAME instance consumed by the
        // TenantScoped global scope and services. Without this binding every
        // app(TenantManager::class) call returns a fresh instance and the
        // switched tenant is silently lost.
        $this->app->scoped(TenantManager::class, fn () => new TenantManager);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enterprise password policy: minimum 12 characters, mixed case,
        // numbers, symbols and an uncompromised (breach) check. Surfaced via
        // Password::defaults() and consumed by RegisterRequest and the reset
        // password flow. See config/security.php for tunables.
        $rule = PasswordRule::min((int) config('security.password_min_length', 12));

        if (config('security.password_mixed_case', true)) {
            $rule->mixedCase();
        }
        if (config('security.password_numbers', true)) {
            $rule->numbers();
        }
        if (config('security.password_symbols', true)) {
            $rule->symbols();
        }
        if (config('security.password_uncompromised', true)) {
            $rule->uncompromised();
        }

        PasswordRule::defaults($rule);
    }
}
